-- Proposed fin_docs_ipc UNION Query
-- Reconstructed from legacy MS Access logic
-- Combines three in-process document sources:
-- 1. pur.purcases (Procurement Cases, filtered to in-process statuses)
-- 2. fin.salorders (Salary Orders, filtered to "In Process" status)
-- 3. hr.salreqs (Salary Requests, filtered to "In Process" status)

WITH fin_docs_ipc AS (
    -- Procurement Cases (pur.purcases)
    SELECT 
        pcs_id AS docid,
        pcs_type AS doctype,
        pcs_title AS title,
        pcs_effhed_id AS effhed_id,
        pcs_effunt_id AS effunt_id,
        pcs_hed_id AS hed_id,
        pcs_unt_id AS unt_id,
        CASE 
            WHEN pcs_transtype = 1 THEN pcs_intprice 
            ELSE pcs_price 
        END AS amount,
        pcs_sudohed AS sudohed,
        pcs_intprice AS amount1,
        pcs_inttax AS tax1,
        pcs_price AS amount2,
        pcs_releasedtg AS rdate
    FROM pur.purcases
    WHERE 
        pcs_sudohed IS NOT NULL
        AND pcs_sudohed <> ''
        AND LOWER(pcs_status) IN (
            'finance', 
            'with finance', 
            'with dfinance', 
            'audit', 
            'command', 
            'approved_pending_commit'
        )
    
    UNION ALL
    
    -- Salary Orders (fin.salorders)
    SELECT 
        sor_id AS docid,
        sor_type AS doctype,
        sor_empnamecomp || ' (' || to_char(sor_month, 'YYYY-MM') || ' Salary)' AS title,
        sor_effhed_id AS effhed_id,
        sor_effunt_id AS effunt_id,
        sor_hed_id AS hed_id,
        sor_unt_id AS unt_id,
        CASE 
            WHEN sor_transtype = 1 THEN sor_salary 
            ELSE sor_netsalary 
        END AS amount,
        sor_sudohed AS sudohed,
        sor_salary AS amount1,
        sor_withheld AS tax1,
        sor_netsalary AS amount2,
        sor_releasedtg AS rdate
    FROM fin.salorders
    WHERE 
        sor_sudohed IS NOT NULL
        AND sor_sudohed <> ''
        AND sor_status = 'In Process'
    
    UNION ALL
    
    -- Salary Requests (hr.salreqs)
    SELECT 
        srq_id AS docid,
        'Srq' AS doctype,
        srq_empnamecomp || ' (' || to_char(srq_month, 'YYYY-MM') || ' Salary Request)' AS title,
        srq_effhed_id AS effhed_id,
        srq_effunt_id AS effunt_id,
        srq_hed_id AS hed_id,
        srq_unt_id AS unt_id,
        srq_netsalary AS amount,
        srq_sudohed AS sudohed,
        srq_salary AS amount1,
        srq_withheld AS tax1,
        srq_netsalary AS amount2,
        srq_releasedtg AS rdate
    FROM hr.salreqs
    WHERE 
        srq_sudohed IS NOT NULL
        AND srq_sudohed <> ''
        AND srq_status = 'In Process'
)

-- Example usage for CF In-Process calculation:
-- SELECT effhed_id, SUM(amount) AS cf_in_process
-- FROM fin_docs_ipc
-- WHERE effhed_id = 200012
-- GROUP BY effhed_id;

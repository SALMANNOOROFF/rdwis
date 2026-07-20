-- Query: fin_commitments_u_pcs_closed
-- Type: 0

SELECT fin_commitments_u_pcs.*
FROM fin_commitments_u_pcs
WHERE (((fin_commitments_u_pcs.cmt_status)="Paid"))
ORDER BY fin_commitments_u_pcs.cmt_date DESC;


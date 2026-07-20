-- Query: fin_remcomfrom_pcs
-- Type: 0

SELECT fin_commitments_pcs.cmt_docid AS docid, fin_commitments_pcs.cmt_type AS doctype, pur_purcases.pcs_date AS rdate, pur_purcases.pcs_title AS title, pur_purcases.pcs_effhed_id AS effhed_id, pur_purcases.pcs_effunt_id AS effunt_id, pur_purcases.pcs_hed_id AS hed_id, pur_purcases.pcs_unt_id AS unt_id, -1*[cmt_amount] AS [commit], IIf([pcs_transtype]=1,Nz(-1*[trn_amount1],0),Nz(-1*[trn_amount2],0)) AS paid, IIf([pcs_transtype]=1,Nz([trn_amount1],0),Nz([trn_amount2],0))-[cmt_amount] AS rem, pur_purcases.pcs_sudohed AS sudohead, fin_commitments_pcs.cmt_id
FROM (pur_purcases INNER JOIN fin_commitments_pcs ON pur_purcases.pcs_id = fin_commitments_pcs.cmt_docid) LEFT JOIN fin_transactions ON fin_commitments_pcs.cmt_id = fin_transactions.trn_cmt_id
WHERE (((pur_purcases.pcs_effhed_id)=Forms!vars!Parameter1) And ((fin_commitments_pcs.cmt_status)="Awaited"));


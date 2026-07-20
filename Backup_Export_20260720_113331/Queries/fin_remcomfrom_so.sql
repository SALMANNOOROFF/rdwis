-- Query: fin_remcomfrom_so
-- Type: 0

SELECT fin_commitments_so.cmt_docid AS docid, fin_commitments_so.cmt_type AS doctype, fin_salorders.sor_releasedtg AS rdate, "Salary for " & [sor_empnamecomp] AS title, fin_salorders.sor_effhed_id AS effhed_id, fin_salorders.sor_effunt_id AS effunt_id, fin_salorders.sor_hed_id AS hed_id, fin_salorders.sor_unt_id AS unt_id, -1*[cmt_amount] AS [commit], IIf([sor_transtype]=1,Nz(-1*[trn_amount1],0),Nz(-1*[trn_amount2],0)) AS paid, IIf([sor_transtype]=1,Nz(-1*[trn_amount1],0),Nz(-1*[trn_amount2],0))-[cmt_amount] AS rem, fin_salorders.sor_sudohed AS sudohed, fin_commitments_so.cmt_id
FROM (fin_salorders INNER JOIN fin_commitments_so ON fin_salorders.sor_id = fin_commitments_so.cmt_docid) LEFT JOIN fin_transactions ON fin_commitments_so.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_salorders.sor_effhed_id)=Forms!vars!Parameter1) And ((fin_commitments_so.cmt_status)="Awaited"));


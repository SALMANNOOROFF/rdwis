-- Query: aud_chk_hednunitcheck_so
-- Type: 0

SELECT fin_salorders.sor_id, fin_salorders.sor_hed_id, fin_salorders.sor_unt_id, fin_commitments.cmt_hed_id, fin_commitments.cmt_unt_id
FROM (fin_salorders INNER JOIN fin_commitments ON fin_salorders.sor_id = fin_commitments.cmt_docid) INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_hed_id)<>Nz([sor_hed_id],0)) AND ((fin_commitments.cmt_type)="SO")) OR (((fin_commitments.cmt_unt_id)<>Nz([sor_unt_id],0)) AND ((fin_commitments.cmt_type)="SO"));


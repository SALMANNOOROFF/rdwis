-- Query: aud_chk_transtype_so
-- Type: 0

SELECT cen_heads.hed_id, cen_heads.hed_code, fin_salorders.sor_id, cen_heads.hed_transtype, fin_salorders.sor_id, fin_salorders.sor_transtype, fin_transactions.trn_id, fin_transactions.trn_transtype
FROM ((fin_salorders LEFT JOIN fin_commitments ON (fin_salorders.sor_type = fin_commitments.cmt_type) AND (fin_salorders.sor_id = fin_commitments.cmt_docid)) LEFT JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) INNER JOIN cen_heads ON fin_salorders.sor_effhed_id = cen_heads.hed_id
WHERE (((fin_salorders.sor_transtype)<>[trn_transtype]))
ORDER BY cen_heads.hed_id;


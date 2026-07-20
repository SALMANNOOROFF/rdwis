-- Query: aud_revobj_trn
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_date, pur_purcases.pcs_minute, pur_purcases.pcs_title, pur_purcases.pcs_hed_id, pur_purcases.pcs_effhed_id, pur_purcases.pcs_status, pur_purcases.pcs_frm_id, pur_purcases.pcs_remarks, pur_purcases.pcs_midprice, pur_purcases.pcs_midtax, pur_purcases.pcs_price, fin_transactions.trn_id, fin_transactions.trn_date, fin_transactions.trn_amount1, fin_transactions.trn_tax1, fin_transactions.trn_amount2
FROM (pur_purcases INNER JOIN fin_commitments ON (pur_purcases.pcs_type = fin_commitments.cmt_type) AND (pur_purcases.pcs_id = fin_commitments.cmt_docid)) INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id;


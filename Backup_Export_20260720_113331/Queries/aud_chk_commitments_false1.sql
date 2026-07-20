-- Query: aud_chk_commitments_false1
-- Type: 0

SELECT fin_commitments.cmt_id, fin_commitments.cmt_docid, fin_commitments.cmt_type, fin_commitments.cmt_amount AS committed, IIf([trn_transtype]=1,[trn_amount1],[trn_amount2]) AS paid, Round(Nz([paid])/[committed]*100,0) AS paidpct, fin_commitments.cmt_status, fin_commitments.cmt_effhed_id, cen_heads.hed_code
FROM (fin_commitments LEFT JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) LEFT JOIN cen_heads ON fin_commitments.cmt_effhed_id = cen_heads.hed_id;


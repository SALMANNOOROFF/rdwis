-- Query: aud_revcomps_trn
-- Type: 0

SELECT aud_revcomps_plus.rvc_rev_id, fin_transactions.trn_id, fin_transactions.trn_date, fin_transactions.trn_amount1, fin_transactions.trn_tax1
FROM aud_revcomps_plus INNER JOIN fin_transactions ON aud_revcomps_plus.rvc_rowid_num = fin_transactions.trn_id
WHERE (((aud_revcomps_plus.rvc_table)="fin_transactions"));


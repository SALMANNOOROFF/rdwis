-- Query: fin_transactions_plus
-- Type: 0

SELECT fin_transactions.trn_id, fin_transactions.trn_cmt_id, fin_transactions.trn_date, fin_transactions.trn_amount1, fin_transactions.trn_tax1, fin_transactions.trn_amount2
FROM fin_transactions
ORDER BY fin_transactions.trn_date DESC;


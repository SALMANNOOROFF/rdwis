-- Query: fin_transactions_u
-- Type: 0

SELECT fin_transactions.*, fin_commitments.cmt_effhed_id
FROM fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_effhed_id)>=getvar("varLower") And (fin_commitments.cmt_effhed_id)<=getvar("varUpper")));


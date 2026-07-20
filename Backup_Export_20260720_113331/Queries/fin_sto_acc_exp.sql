-- Query: fin_sto_acc_exp
-- Type: 0

SELECT fin_sto_acc_exp1.cmt_effhed_id, Sum(fin_sto_acc_exp1.amount) AS SumOfamount
FROM fin_sto_acc_exp1
GROUP BY fin_sto_acc_exp1.cmt_effhed_id;


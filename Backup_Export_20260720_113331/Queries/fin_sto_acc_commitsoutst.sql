-- Query: fin_sto_acc_commitsoutst
-- Type: 0

SELECT Sum([cmt_amount]-Nz([amount],0)) AS SumOfamount
FROM fin_sto_acc_commitsoutst1;


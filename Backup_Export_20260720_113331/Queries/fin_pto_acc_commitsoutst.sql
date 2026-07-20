-- Query: fin_pto_acc_commitsoutst
-- Type: 0

SELECT Sum([cmt_amount]-Nz([amount],0)) AS SumOfamount
FROM fin_pto_acc_commitsoutst1;


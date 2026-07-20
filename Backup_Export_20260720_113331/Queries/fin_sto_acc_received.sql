-- Query: fin_sto_acc_received
-- Type: 0

SELECT fin_sto_acc_received1.cmt_effhed_id, Sum(fin_sto_acc_received1.amount) AS SumOfamount
FROM fin_sto_acc_received1
GROUP BY fin_sto_acc_received1.cmt_effhed_id;


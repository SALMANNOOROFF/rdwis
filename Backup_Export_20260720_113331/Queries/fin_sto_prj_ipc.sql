-- Query: fin_sto_prj_ipc
-- Type: 0

SELECT fin_sto_prj_ipc1.hed_id, Sum(fin_sto_prj_ipc1.amount) AS SumOfamount
FROM fin_sto_prj_ipc1
GROUP BY fin_sto_prj_ipc1.hed_id;


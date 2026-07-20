-- Query: fin_sto_pcc_ipc
-- Type: 0

SELECT [fin_sto_pcc_ipc1].effhed_id, Sum([fin_sto_pcc_ipc1].amount) AS SumOfamount
FROM fin_sto_pcc_ipc1
GROUP BY [fin_sto_pcc_ipc1].effhed_id;


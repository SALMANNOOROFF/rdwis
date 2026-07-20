-- Query: aud_chk_receipts_double1
-- Type: 0

SELECT pur_purreceipts.prt_pcs_id, Count(pur_purreceipts.prt_id) AS [count]
FROM pur_purreceipts
GROUP BY pur_purreceipts.prt_pcs_id
HAVING (((Count(pur_purreceipts.prt_id))>1));


-- Query: pur_purreceipts_u
-- Type: 0

SELECT pur_purreceipts.*
FROM pur_purreceipts
WHERE (((pur_purreceipts.prt_unt_id)>=getvar("varLower") And (pur_purreceipts.prt_unt_id)<=getvar("varUpper")))
ORDER BY pur_purreceipts.prt_id DESC;


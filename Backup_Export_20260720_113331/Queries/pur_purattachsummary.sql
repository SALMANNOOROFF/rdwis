-- Query: pur_purattachsummary
-- Type: 0

SELECT pur_purattachsummary1.Currency, pur_purattachsummary1.pat_type, pur_purattachsummary1.Status, Count(pur_purattachsummary1.Status) AS CountOfStatus
FROM pur_purattachsummary1
GROUP BY pur_purattachsummary1.Currency, pur_purattachsummary1.pat_type, pur_purattachsummary1.Status;


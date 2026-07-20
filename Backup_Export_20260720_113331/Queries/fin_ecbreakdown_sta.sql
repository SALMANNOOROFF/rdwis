-- Query: fin_ecbreakdown_sta
-- Type: 0

SELECT Count([%$##@_Alias].id) AS doc_count
FROM (SELECT DISTINCT fin_ecbreakdown_temp.id
FROM fin_ecbreakdown_temp)  AS [%$##@_Alias];


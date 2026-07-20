-- Query: fin_salorderminute_months1
-- Type: 0

SELECT DISTINCT Format([sor_month],"mmm yy") AS sal_month
FROM fin_salorders
WHERE (((fin_salorders.sor_status) Like "Draft"))
ORDER BY Format([sor_month],"mmm yy");


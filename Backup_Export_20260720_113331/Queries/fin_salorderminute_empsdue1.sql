-- Query: fin_salorderminute_empsdue1
-- Type: 0

SELECT Max(firstdatethismonth([sor_month])) AS max_month
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Draft"));


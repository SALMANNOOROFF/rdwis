-- Query: fin_lastsalarymonth
-- Type: 0

SELECT Max(fin_salorders.sor_month) AS max_month
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Fulfilled") And ((fin_salorders.sor_hed_id)=Forms!vars!Parameter1));


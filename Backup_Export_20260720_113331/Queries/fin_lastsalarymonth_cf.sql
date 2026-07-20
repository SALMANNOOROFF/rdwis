-- Query: fin_lastsalarymonth_cf
-- Type: 0

SELECT Max(fin_salorders.sor_month) AS max_month
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Fulfilled") AND ((fin_salorders.sor_hed_id) Is Null));


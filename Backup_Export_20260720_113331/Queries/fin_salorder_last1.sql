-- Query: fin_salorder_last1
-- Type: 0

SELECT Max(fin_salorders.sor_releasedtg) AS MaxOfsor_releasedtg
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Fulfilled") And ((fin_salorders.sor_emp_id)=Forms!vars!Parameter1));


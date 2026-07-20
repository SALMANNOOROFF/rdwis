-- Query: xxx5-1
-- Type: 0

SELECT fin_salorders.sor_id, fin_salorders.sor_emp_id, fin_salorders.sor_empnamecomp, fin_salorders.sor_month, fin_salorders.sor_ctrsalary, fin_salorders.sor_salary, fin_salorders.sor_status, [sor_emp_id] & "-" & [sor_month] AS id
FROM fin_salorders
WHERE (((fin_salorders.sor_emp_id) Not Like "*Unknown*") AND ((fin_salorders.sor_status)="Fulfilled"));


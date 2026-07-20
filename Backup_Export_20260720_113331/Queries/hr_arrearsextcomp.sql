-- Query: hr_arrearsextcomp
-- Type: 0

SELECT First(fin_extcompenses.ecp_id) AS marker, Sum(fin_extcompenses.ecp_amount) AS ecp_amount
FROM fin_extcompenses
WHERE (((fin_extcompenses.ecp_emp_id)=[EmpId]) AND ((fin_extcompenses.ecp_type)="Salary") AND ((fin_extcompenses.ecp_month)=[SalMonth]));


-- Query: hr_emps_custom
-- Type: 0

SELECT emp_id AS [Employee ID], name_full AS [Name Full], unt_name AS [Department Name], emp_joindt AS [Joining Date], emp_ntnlty AS Nationality, emp_marital AS [Marital Status]
FROM hr_emps_u_grand
WHERE emp_status like 'Active*' And emp_unt_id = 200000
ORDER BY emp_unt_id, emp_id;


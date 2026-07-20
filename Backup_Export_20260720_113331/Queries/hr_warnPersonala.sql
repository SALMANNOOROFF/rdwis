-- Query: hr_warnPersonala
-- Type: 0

SELECT hr_emps_u_active.*
FROM hr_emps_u_active
WHERE (((hr_emps_u_active.emp_id) Not In (Select [empexta_emp_id] From [hr_empsexta])));


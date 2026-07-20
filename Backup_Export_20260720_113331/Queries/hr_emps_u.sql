-- Query: hr_emps_u
-- Type: 0

SELECT hr_emps.*
FROM hr_emps
WHERE (((hr_emps.emp_unt_id)>=getvar("varLower") And (hr_emps.emp_unt_id)<=getvar("varUpper")))
ORDER BY hr_emps.emp_unt_id;


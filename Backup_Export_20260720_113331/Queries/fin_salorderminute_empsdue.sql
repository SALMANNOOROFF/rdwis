-- Query: fin_salorderminute_empsdue
-- Type: 0

SELECT Count(hr_emps.emp_id) AS count_empsdue
FROM hr_emps
WHERE (((hr_emps.emp_status) Like "Active")) OR (((hr_emps.emp_lastdt)>=(Select max_month From fin_salorderminute_empsdue1)));


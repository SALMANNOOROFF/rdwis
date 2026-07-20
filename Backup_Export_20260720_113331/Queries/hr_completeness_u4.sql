-- Query: hr_completeness_u4
-- Type: 0

SELECT hr_emps.emp_id, Count(hr_vehicles.vcl_emp_id) AS CountOfvcl_emp_id
FROM hr_emps LEFT JOIN hr_vehicles ON hr_emps.emp_id = hr_vehicles.vcl_emp_id
GROUP BY hr_emps.emp_id;


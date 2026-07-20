-- Query: hr_contracts_u_last_active
-- Type: 0

SELECT hr_contracts_last.*, hr_emps_u_active.emp_name, hr_emps_u_active.emp_rank
FROM hr_emps_u_active INNER JOIN hr_contracts_last ON hr_emps_u_active.emp_id = hr_contracts_last.ctr_num;


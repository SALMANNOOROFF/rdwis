-- Query: hr_contracts_last_active
-- Type: 0

SELECT hr_contracts_last.*
FROM hr_contracts_last INNER JOIN hr_emps ON hr_contracts_last.ctr_num = hr_emps.emp_id
WHERE (((hr_emps.emp_status)="Active"));


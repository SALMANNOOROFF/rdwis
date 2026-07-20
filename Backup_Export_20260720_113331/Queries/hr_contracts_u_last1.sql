-- Query: hr_contracts_u_last1
-- Type: 0

SELECT hr_contracts.ctr_num, Max(hr_contracts.ctr_date) AS MaxOfctr_date
FROM hr_contracts INNER JOIN hr_emps_u ON hr_contracts.ctr_num = hr_emps_u.emp_id
GROUP BY hr_contracts.ctr_num;


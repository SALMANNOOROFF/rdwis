-- Query: hr_warnContractNone
-- Type: 0

SELECT hr_emps_u_active.*
FROM hr_emps_u_active
WHERE ((([hr_emps_u_active].[emp_id]) Not In (Select ctr_num From hr_contracts)));


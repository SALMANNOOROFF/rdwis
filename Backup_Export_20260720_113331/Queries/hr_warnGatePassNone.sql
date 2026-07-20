-- Query: hr_warnGatePassNone
-- Type: 0

SELECT hr_emps_u_active.*
FROM hr_emps_u_active
WHERE (((hr_emps_u_active.emp_id) In (Select [hr_empsextc].[empextc_emp_id] From [hr_empsextc] Where isnull([hr_empsextc].[emp_cnum] ))) AND ((hr_emps_u_active.emp_rank) Is Null));


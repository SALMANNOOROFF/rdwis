-- Query: hr_contracts_u_active
-- Type: 0

SELECT hr_contracts.*, IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]) AS ctr_effenddt
FROM hr_emps_u_active INNER JOIN hr_contracts ON hr_emps_u_active.emp_id = hr_contracts.ctr_num;


-- Query: hr_warnContractExpiry
-- Type: 0

SELECT hr_emps.*, IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]) AS end_date, hr_contracts_u_last_active.emp_name AS name, hr_contracts_u_last_active.emp_rank AS rank, *
FROM hr_emps INNER JOIN hr_contracts_u_last_active ON hr_emps.emp_id = hr_contracts_u_last_active.ctr_num
WHERE (((DateDiff("d",Date(),IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt])))<=60))
ORDER BY IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]);


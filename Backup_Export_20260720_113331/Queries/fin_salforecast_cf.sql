-- Query: fin_salforecast_cf
-- Type: 0

SELECT hr_contracts.ctr_id, hr_contracts.ctr_num, hr_contracts.ctr_unt_id, hr_contracts.ctr_salary, hr_emps.emp_name, hr_emps.emp_joindt, fin_empeffheads.eeh_status, IIf([emp_joindt]>[ctr_startdt],[emp_joindt],[ctr_startdt]) AS ctr_effstartdt, IIf(IsNull([ctr_termindt]),[ctr_enddt],([ctr_termindt])) AS ctr_effenddt, [datetoday] AS today
FROM (hr_contracts INNER JOIN hr_emps ON hr_contracts.ctr_num = hr_emps.emp_id) INNER JOIN fin_empeffheads ON hr_contracts.ctr_num = fin_empeffheads.eeh_emp_id
WHERE (((hr_contracts.ctr_unt_id)<200000 Or (hr_contracts.ctr_unt_id)>=800000) AND ((fin_empeffheads.eeh_status)="Open") AND ((IIf(IsNull([ctr_termindt]),[ctr_enddt],([ctr_termindt])))>=FirstDateThisMonth([datetoday])))
ORDER BY IIf([emp_joindt]>[ctr_startdt],[emp_joindt],[ctr_startdt]);


-- Query: fin_salforecast
-- Type: 0

SELECT hr_contracts.ctr_id, hr_contracts.ctr_num, hr_contracts.ctr_unt_id, hr_contracts.ctr_salary, hr_emps.emp_name, hr_emps.emp_joindt, fin_empeffheads.eeh_status, IIf([emp_joindt]>[cpn_startdt],[emp_joindt],[cpn_startdt]) AS cpn_effstartdt, hr_contractplans.cpn_enddt AS cpn_effenddt, hr_contractplans.cpn_hed_id, LastDateThisMonth([cpn_enddt]) AS sal_month
FROM ((hr_contracts INNER JOIN hr_emps ON hr_contracts.ctr_num = hr_emps.emp_id) INNER JOIN fin_empeffheads ON hr_contracts.ctr_num = fin_empeffheads.eeh_emp_id) INNER JOIN hr_contractplans ON hr_contracts.ctr_id = hr_contractplans.cpn_ctr_id
WHERE (((fin_empeffheads.eeh_status)="Open") And ((hr_contractplans.cpn_enddt)>[LastSalaryMonth]) And ((hr_contractplans.cpn_hed_id)=Forms!vars!Parameter1))
ORDER BY IIf([emp_joindt]>[cpn_startdt],[emp_joindt],[cpn_startdt]);


-- Query: hr_emps_u_active_doc
-- Type: 0

SELECT NameComplete([emp_name],[emp_title],[emp_rank]) AS Name, hr_contracts_last_active.ctr_jobtitle, hr_emps_u_active.emp_joindt, hr_emps_u_active.emp_unt_id, hr_contractplans_today.cpn_hed_id, hr_contracts_last_active.ctr_grade, grades.grade_id, IIf(IsNull([ctr_id]),"",Format([ctr_startdt],"dd mmm yy") & "  to  " & Format([ctr_enddt],"dd mmm yy") & IIf(Not IsNull([ctr_termindt]),IIf([ctr_termindt]>[ctr_enddt]," (extended to " & Format([ctr_termindt],"dd mmm yy") & ")"," (concluded on " & Format([ctr_termindt],"dd mmm yy") & ")"))) AS last_active_contract, hr_contracts_last_active.ctr_salary
FROM ((hr_emps_u_active LEFT JOIN hr_contracts_last_active ON hr_emps_u_active.emp_id = hr_contracts_last_active.ctr_num) LEFT JOIN grades ON hr_contracts_last_active.ctr_grade = grades.grade_short) LEFT JOIN hr_contractplans_today ON hr_contracts_last_active.ctr_id = hr_contractplans_today.cpn_ctr_id
ORDER BY grades.grade_id DESC;


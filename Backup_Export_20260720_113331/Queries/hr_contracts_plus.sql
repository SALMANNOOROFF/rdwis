-- Query: hr_contracts_plus
-- Type: 0

SELECT hr_contracts.*, IIf([emp_joindt]>[ctr_startdt] And [emp_joindt]<=[ctr_enddt],[emp_joindt],[ctr_startdt]) AS ctr_effstartdt, IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]) AS ctr_effenddt, hr_emps.emp_joindt, hr_contractplans.*
FROM (hr_emps INNER JOIN hr_contracts ON hr_emps.emp_id = hr_contracts.ctr_num) LEFT JOIN hr_contractplans ON hr_contracts.ctr_id = hr_contractplans.cpn_ctr_id
WHERE (((hr_contractplans.cpn_enddt)>=[emp_joindt])) OR (((hr_contractplans.cpn_enddt) Is Null));


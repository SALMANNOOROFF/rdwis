-- Query: fin_contractsverif_not
-- Type: 0

SELECT fin_contractsverif.cvf_verif, fin_contractsverif.cvf_dtg, hr_contracts.ctr_num, hr_emps.emp_name, hr_emps.emp_title, hr_emps.emp_rank, hr_contracts.ctr_unt_id, hr_contracts.ctr_hed_id, hr_contracts.ctr_startdt, hr_contracts.ctr_enddt, hr_contracts.ctr_salary, hr_contracts.ctr_prob, hr_contracts.ctr_probsal, hr_contracts.ctr_grade, hr_contracts.ctr_id
FROM (fin_contractsverif INNER JOIN hr_contracts ON fin_contractsverif.cvf_ctr_id = hr_contracts.ctr_id) INNER JOIN hr_emps ON hr_contracts.ctr_num = hr_emps.emp_id
WHERE (((fin_contractsverif.cvf_verif)="0"));


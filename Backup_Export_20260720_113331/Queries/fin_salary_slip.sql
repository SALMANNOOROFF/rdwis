-- Query: fin_salary_slip
-- Type: 0

SELECT fin_salorders.*, hr_emps_u_grand.emp_name, hr_emps_u_grand.emp_rank, hr_emps_u_grand.emp_title, hr_emps_u_grand.emp_cnic, hr_emps_u_grand.ctr_jobtitle, hr_emps_u_grand.emp_joindt, hr_emps_u_grand.emp_nokname, hr_emps_u_grand.ctr_unt_id, hr_emps_u_grand.ctr_type, hr_emps_u_grand.bac_accnum, hr_emps_u_grand.bac_bnkname
FROM fin_salorders INNER JOIN hr_emps_u_grand ON fin_salorders.sor_emp_id = hr_emps_u_grand.emp_id
WHERE (((fin_salorders.sor_id)=[Forms]![fin_salorders_u]![sor_id]));


-- Query: hr_contract_doc
-- Type: 0

SELECT hr_emps_u.*, hr_empsexta.*, hr_empsextb.*, hr_empsextc.*, units.*, hr_contracts.*, hr_bnkaccountsforpay.*, hr_vehiclesconcat.*, grades.grade_id, Concatrelated("job_company","hr_jobs","job_emp_id='" & [emp_id] & "'") AS experience, grades.grade, IntervalInMonths([ctr_startdt],[ctr_enddt]) AS contract_period
FROM (((((((hr_emps_u LEFT JOIN hr_contracts ON hr_emps_u.emp_id = hr_contracts.ctr_num) LEFT JOIN units ON hr_emps_u.emp_unt_id = units.unt_id) LEFT JOIN hr_empsexta ON hr_emps_u.emp_id = hr_empsexta.empexta_emp_id) LEFT JOIN hr_empsextb ON hr_emps_u.emp_id = hr_empsextb.empextb_emp_id) LEFT JOIN hr_empsextc ON hr_emps_u.emp_id = hr_empsextc.empextc_emp_id) LEFT JOIN hr_bnkaccountsforpay ON hr_emps_u.emp_id = hr_bnkaccountsforpay.bac_emp_id) LEFT JOIN hr_vehiclesconcat ON hr_emps_u.emp_id = hr_vehiclesconcat.vcl_emp_id) LEFT JOIN grades ON hr_contracts.ctr_grade = grades.grade_short;


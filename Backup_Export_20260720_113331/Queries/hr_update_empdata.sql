-- Query: hr_update_empdata
-- Type: 48

UPDATE hr_emps SET hr_emps.emp_unt_id = [Unit]
WHERE (((hr_emps.emp_id)=[Emp]));


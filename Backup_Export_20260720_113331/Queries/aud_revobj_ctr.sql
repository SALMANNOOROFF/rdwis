-- Query: aud_revobj_ctr
-- Type: 0

SELECT hr_contracts.*, NameComplete([emp_name],[emp_rank],[emp_title]) AS name
FROM hr_emps INNER JOIN hr_contracts ON hr_emps.emp_id = hr_contracts.ctr_num;


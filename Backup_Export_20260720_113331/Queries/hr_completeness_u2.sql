-- Query: hr_completeness_u2
-- Type: 0

SELECT hr_emps.emp_id, Count(hr_jobs.job_emp_id) AS CountOfjob_emp_id
FROM hr_emps LEFT JOIN hr_jobs ON hr_emps.emp_id = hr_jobs.job_emp_id
GROUP BY hr_emps.emp_id;


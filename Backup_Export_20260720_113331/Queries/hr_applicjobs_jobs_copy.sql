-- Query: hr_applicjobs_jobs_copy
-- Type: 64

INSERT INTO hr_jobs ( job_emp_id, job_company, job_city, job_jobtitle, job_repto, job_team, job_from, job_to, job_resp, job_ach )
SELECT [EmployeeId] AS Expr1, hr_applicjobs.apj_company, hr_applicjobs.apj_city, hr_applicjobs.apj_jobtitle, hr_applicjobs.apj_repto, hr_applicjobs.apj_team, hr_applicjobs.apj_from, hr_applicjobs.apj_to, hr_applicjobs.apj_resp, hr_applicjobs.apj_ach
FROM hr_applicjobs
WHERE (((hr_applicjobs.apj_apl_id)=[ApplicId]));


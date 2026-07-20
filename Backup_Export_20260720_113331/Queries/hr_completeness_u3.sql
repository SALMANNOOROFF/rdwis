-- Query: hr_completeness_u3
-- Type: 0

SELECT hr_emps.emp_id, Count(hr_bnkaccounts.bac_emp_id) AS CountOfbac_emp_id
FROM hr_emps LEFT JOIN hr_bnkaccounts ON hr_emps.emp_id = hr_bnkaccounts.bac_emp_id
GROUP BY hr_emps.emp_id;


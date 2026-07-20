-- Query: hr_warnClearance
-- Type: 0

SELECT hr_emps_u.*
FROM hr_emps_u
WHERE (((hr_emps_u.emp_status) Not Like "Active") AND ((hr_emps_u.emp_cleared)="0"));


-- Query: hr_salreqs_u_draft
-- Type: 0

SELECT hr_salreqs.*
FROM hr_salreqs INNER JOIN hr_emps ON hr_salreqs.srq_emp_id = hr_emps.emp_id
WHERE (((hr_salreqs.srq_unt_id)>=getvar("varLower") And (hr_salreqs.srq_unt_id)<=getvar("varUpper")) AND ((hr_salreqs.srq_status)="Draft"))
ORDER BY hr_salreqs.srq_empnamecomp, hr_salreqs.srq_parent;


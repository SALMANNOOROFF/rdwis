-- Query: hr_salreqs_u
-- Type: 0

SELECT hr_salreqs.*
FROM hr_salreqs
WHERE (((hr_salreqs.srq_unt_id)>=getvar("varLower") And (hr_salreqs.srq_unt_id)<=getvar("varUpper")));


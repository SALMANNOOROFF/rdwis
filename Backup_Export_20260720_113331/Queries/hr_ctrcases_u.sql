-- Query: hr_ctrcases_u
-- Type: 0

SELECT hr_ctrcases.*
FROM hr_ctrcases
WHERE (((hr_ctrcases.ctc_unt_id)>=getvar("varLower") And (hr_ctrcases.ctc_unt_id)<=getvar("varUpper")));


-- Query: fin_extcompenses_u
-- Type: 0

SELECT fin_extcompenses.*
FROM fin_extcompenses
WHERE (((fin_extcompenses.ecp_unt_id)>=getvar("varLower") And (fin_extcompenses.ecp_unt_id)<=getvar("varUpper")))
ORDER BY fin_extcompenses.ecp_date DESC;


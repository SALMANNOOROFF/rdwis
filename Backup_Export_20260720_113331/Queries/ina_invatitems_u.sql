-- Query: ina_invatitems_u
-- Type: 0

SELECT ina_invats.*, ina_invatcomps.*
FROM ina_invats INNER JOIN ina_invatcomps ON ina_invats.ias_id = ina_invatcomps.iac_ias_id
WHERE (((ina_invats.ias_unt_id)>=getvar("varLower") And (ina_invats.ias_unt_id)<=getvar("varUpper")))
ORDER BY ina_invats.ias_id DESC;


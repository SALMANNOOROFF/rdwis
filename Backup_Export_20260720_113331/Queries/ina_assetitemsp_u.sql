-- Query: ina_assetitemsp_u
-- Type: 0

SELECT ina_invats.*, ina_invatcompsp.*
FROM ina_invats INNER JOIN ina_invatcompsp ON ina_invats.ias_id = ina_invatcompsp.iac_ias_id
WHERE (((ina_invats.ias_unt_id)>=getvar("varLower") And (ina_invats.ias_unt_id)<=getvar("varUpper")) AND ((ina_invats.ias_type2)=6));


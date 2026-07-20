-- Query: ina_invatitemsp
-- Type: 0

SELECT ina_invats.*, ina_invatcompsp.*
FROM ina_invats INNER JOIN ina_invatcompsp ON ina_invats.ias_id = ina_invatcompsp.iac_ias_id;


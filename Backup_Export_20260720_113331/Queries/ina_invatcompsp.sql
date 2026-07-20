-- Query: ina_invatcompsp
-- Type: 0

SELECT ina_invatcomps.*, ina_invatcomps_1.iac_ias_id AS parent_ias_id
FROM ina_invatcomps LEFT JOIN ina_invatcomps AS ina_invatcomps_1 ON ina_invatcomps.iac_parent_id = ina_invatcomps_1.iac_id;


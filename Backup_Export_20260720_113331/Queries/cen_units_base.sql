-- Query: cen_units_base
-- Type: 0

SELECT *
FROM cen_units
WHERE unt_type = 'Division' And unt_id >= 200000 And unt_id <= 249999;


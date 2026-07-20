-- Query: cen_heads_base
-- Type: 0

SELECT *
FROM cen_heads
WHERE hed_type = 'Project' And IsNull([hed_closedt]) And hed_unt_id >= 200000 And hed_unt_id <= 249999;


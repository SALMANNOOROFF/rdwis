-- Query: cen_heads_u
-- Type: 0

SELECT cen_heads.*
FROM cen_heads
WHERE (((cen_heads.hed_unt_id)>=getvar("varLower") And (cen_heads.hed_unt_id)<=getvar("varUpper")));


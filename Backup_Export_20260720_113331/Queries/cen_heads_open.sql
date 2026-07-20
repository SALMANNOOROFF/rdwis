-- Query: cen_heads_open
-- Type: 0

SELECT cen_heads.*
FROM cen_heads
WHERE (((cen_heads.hed_closedt) Is Null));


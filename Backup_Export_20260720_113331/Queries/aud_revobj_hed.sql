-- Query: aud_revobj_hed
-- Type: 0

SELECT cen_heads.*, prj_projects.prj_code, units.unt_namesh
FROM (cen_heads INNER JOIN prj_projects ON cen_heads.hed_prj_id = prj_projects.prj_id) INNER JOIN units ON cen_heads.hed_unt_id = units.unt_id;


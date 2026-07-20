-- Query: frm_search_projects
-- Type: 0

SELECT frm_firms.frm_id, frm_projects.*
FROM frm_firms INNER JOIN frm_projects ON frm_firms.frm_id = frm_projects.prj_xfrm_id;


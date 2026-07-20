-- Query: prj_selected_u
-- Type: 0

SELECT selected.sel_mpr
FROM selected INNER JOIN prj_projects_u ON selected.sel_mpr = prj_projects_u.prj_id;


-- Query: prj_prghistory_u
-- Type: 0

SELECT prj_prghistory.*
FROM prj_prghistory INNER JOIN prj_projects_u ON prj_prghistory.pgh_xprj_id = prj_projects_u.prj_id;


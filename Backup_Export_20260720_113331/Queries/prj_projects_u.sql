-- Query: prj_projects_u
-- Type: 0

SELECT prj_projects.*
FROM prj_projects
WHERE (((prj_projects.prj_unt_id)>=getvar("varLower") And (prj_projects.prj_unt_id)<=getvar("varUpper")))
ORDER BY prj_projects.prj_status, prj_projects.prj_aprvdt DESC;


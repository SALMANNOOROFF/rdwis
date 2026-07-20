-- Query: prj_projects_u_closed
-- Type: 0

SELECT prj_projects_u.*
FROM prj_projects_u
WHERE ((([prj_projects_u].[prj_status]) Like "Completed" Or ([prj_projects_u].[prj_status]) Like "Cancelled"));


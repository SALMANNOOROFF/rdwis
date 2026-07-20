-- Query: prj_projects_mprsummary
-- Type: 0

SELECT prj_projects_mproverview.semi_status, Count(prj_projects_mproverview.prj_id) AS project_count
FROM prj_projects_mproverview
GROUP BY prj_projects_mproverview.semi_status
ORDER BY prj_projects_mproverview.semi_status;


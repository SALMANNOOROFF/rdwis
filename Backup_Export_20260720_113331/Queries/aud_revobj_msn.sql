-- Query: aud_revobj_msn
-- Type: 0

SELECT prj_milestones.*, prj_projects.prj_code
FROM prj_milestones INNER JOIN prj_projects ON prj_milestones.msn_xprj_id = prj_projects.prj_id;


-- Query: aud_revobj_mct
-- Type: 0

SELECT prj_projects.prj_id, prj_projects.prj_code, prj_projects.prj_title, prj_milestones.msn_id, prj_milestones.msn_desc
FROM prj_projects INNER JOIN prj_milestones ON prj_projects.prj_id = prj_milestones.msn_xprj_id;


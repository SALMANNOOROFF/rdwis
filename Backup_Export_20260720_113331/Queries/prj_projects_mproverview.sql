-- Query: prj_projects_mproverview
-- Type: 0

SELECT prj_projects_u.*, prj_projects_mproverview1.pgh_id, prj_projects_mproverview1.pgh_xprj_id, prj_projects_mproverview1.pgh_status, prj_projects_mproverview1.pgh_level, SemiStatus([pgh_level],[pgh_status]) AS semi_status, IIf(Not IsNull([prj_aprvdt]) And Not IsNull([prj_startdt]),"MPR-I","MPR-II") AS mpr_type
FROM prj_projects_u LEFT JOIN prj_projects_mproverview1 ON prj_projects_u.prj_id = prj_projects_mproverview1.pgh_xprj_id
WHERE (((prj_projects_u.prj_status)<>"Cancelled" And (prj_projects_u.prj_status)<>"Completed") AND ((prj_projects_u.prj_reporting)="1"))
ORDER BY prj_projects_u.prj_unt_id, IIf(Not IsNull([prj_aprvdt]) And Not IsNull([prj_startdt]),"MPR-I",IIf(Not IsNull([prj_aprvdt]),"MPR-II","MPR-III"));


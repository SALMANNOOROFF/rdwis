-- Query: prj_mpr
-- Type: 0

SELECT prj_projects.*, prj_prghistory_draftopen.pgh_id AS current_pgh_id, prj_prghistory_draftopen.pgh_progress AS current_progress, prj_prghistory_fin_last.pgh_id AS last_pgh_id, prj_prghistory_fin_last.pgh_progress AS last_progress, cen_unit_u.unt_leadname, cen_unit_u.unt_leadtitle, cen_unit_u.unt_leadrank, cen_unit_u.unt_leaddesig, cen_unit_u.unt_leaddesigshort, cen_unit_u.unt_id
FROM ((prj_projects INNER JOIN cen_unit_u ON prj_projects.prj_unt_id = cen_unit_u.unt_id) LEFT JOIN prj_prghistory_fin_last ON prj_projects.prj_id = prj_prghistory_fin_last.pgh_xprj_id) INNER JOIN prj_prghistory_draftopen ON prj_projects.prj_id = prj_prghistory_draftopen.pgh_xprj_id;


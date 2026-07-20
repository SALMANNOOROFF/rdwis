-- Query: prj_prghistory_last_open
-- Type: 0

SELECT prj_prghistory_last.pgh_id, prj_prghistory_last.pgh_xprj_id, prj_prghistory_last.pgh_status, prj_prghistory_last.pgh_level
FROM prj_prghistory_last
WHERE (((prj_prghistory_last.pgh_status)="Draft" Or (prj_prghistory_last.pgh_status) Like "Under*"));


-- Query: prj_prghistory_create_copy
-- Type: 64

INSERT INTO prj_prghistory ( pgh_xprj_id, pgh_progress, pgh_author, pgh_level, pgh_trail, pgh_trailinherited, pgh_group, pgh_dtg, pgh_status )
SELECT getField("prj_id") AS Expr1, getFieldSub("pgh_progress","subProgress") AS Expr2, getVar("varRoleDesigShort") AS Expr3, getVar("varRoleLevel") AS Expr4, "-" & getVar("varRoleLevel") & "-" AS Expr5, [TrailInherited] AS Expr6, getFieldSub("pgh_group","subProgress") AS Expr7, [HistoryDtg] AS Expr8, [ProgressStatus] AS Expr9;


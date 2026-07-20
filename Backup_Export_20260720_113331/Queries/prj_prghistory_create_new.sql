-- Query: prj_prghistory_create_new
-- Type: 64

INSERT INTO prj_prghistory ( pgh_xprj_id, pgh_progress, pgh_author, pgh_level, pgh_trail, pgh_group, pgh_dtg, pgh_status )
SELECT getField("prj_id") AS Expr1, Left(getField("prj_rem"),2000) AS Expr2, getVar("varRoleDesigShort") AS Expr3, getVar("varRoleLevel") AS Expr4, "-" & getVar("varRoleLevel") & "-" AS Expr5, GetCurrentMprGroup() AS Expr6, GetNow() AS Expr8, "Draft" AS Expr9;


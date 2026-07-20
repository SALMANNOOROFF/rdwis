-- Query: prj_comments_create
-- Type: 64

INSERT INTO prj_comments ( cmt_xpgh_id, cmt_author, cmt_status )
SELECT getFieldSub("pgh_id","SubProgress") AS Expr1, getVar("varRoleDesigShort") AS Expr3, "Draft" AS Expr2;


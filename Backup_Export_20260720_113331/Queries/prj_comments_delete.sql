-- Query: prj_comments_delete
-- Type: 32

DELETE prj_comments.*, comments.cmt_id AS Expr1, comments.cmt_id AS Expr2
FROM prj_comments
WHERE prj_comments.cmt_id=getFieldSub("cmt_id","SubComment");


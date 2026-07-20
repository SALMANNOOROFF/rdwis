-- Query: prj_comment_last
-- Type: 0

SELECT comments.cmt_xpgh_id AS Expr1, Max(comments.cmt_id) AS cmt_id_last
FROM comments
GROUP BY comments.cmt_xpgh_id;


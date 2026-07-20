-- Query: fin_warn_falsepocommits_awaited
-- Type: 0

SELECT fin_warn_falsepocommits1.cmt_id, fin_warn_falsepocommits1.cmt_docid AS docid, fin_warn_falsepocommits1.cmt_type AS type, First(fin_warn_falsepocommits1.committed) AS committed, Sum(fin_warn_falsepocommits1.paid) AS paid, Sum(fin_warn_falsepocommits1.paidpct) AS paidpct, First(fin_warn_falsepocommits1.cmt_status) AS status
FROM fin_warn_falsepocommits1
GROUP BY fin_warn_falsepocommits1.cmt_id, fin_warn_falsepocommits1.cmt_docid, fin_warn_falsepocommits1.cmt_type
HAVING (((Sum(fin_warn_falsepocommits1.paidpct))>95) AND ((First(fin_warn_falsepocommits1.cmt_status))="Awaited"))
ORDER BY Sum(fin_warn_falsepocommits1.paidpct) DESC;


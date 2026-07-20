-- Query: aud_chk_commitments_falsepaid
-- Type: 0

SELECT aud_chk_commitments_false1.cmt_id, First(aud_chk_commitments_false1.cmt_docid) AS docid, First(aud_chk_commitments_false1.cmt_type) AS type, First(aud_chk_commitments_false1.committed) AS committed, Sum(aud_chk_commitments_false1.paid) AS paid, Sum(aud_chk_commitments_false1.paidpct) AS paidpct, First(aud_chk_commitments_false1.cmt_status) AS status, First(aud_chk_commitments_false1.hed_code) AS head
FROM aud_chk_commitments_false1
GROUP BY aud_chk_commitments_false1.cmt_id
HAVING (((Sum(aud_chk_commitments_false1.paidpct))<99) AND ((First(aud_chk_commitments_false1.cmt_status))="Paid"))
ORDER BY Sum(aud_chk_commitments_false1.paidpct);


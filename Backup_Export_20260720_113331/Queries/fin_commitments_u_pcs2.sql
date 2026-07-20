-- Query: fin_commitments_u_pcs2
-- Type: 0

SELECT fin_commitscat.ctc_cmt_id, fin_commitscat.ctc_amount AS misc_amount
FROM fin_commitscat
WHERE (((fin_commitscat.ctc_category)=3));


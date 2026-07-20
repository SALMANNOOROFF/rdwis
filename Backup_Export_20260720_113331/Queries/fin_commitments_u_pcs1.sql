-- Query: fin_commitments_u_pcs1
-- Type: 0

SELECT fin_commitscat.ctc_cmt_id, fin_commitscat.ctc_amount AS equip_amount
FROM fin_commitscat
WHERE (((fin_commitscat.ctc_category)=1));


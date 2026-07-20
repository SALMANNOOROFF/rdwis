-- Query: aud_chk_committrans_signs
-- Type: 0

SELECT fin_commitments.cmt_type, [cmt_amount]/[trn_amount1] AS Expr1, [trn_tax1]/[trn_amount2] AS Expr2
FROM fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_type) In ("Ps","Pt","Rb","Sa","GO","LO","TO")) AND (([cmt_amount]/[trn_amount1])<0) AND (([trn_tax1]/[trn_amount2])<0)) OR (((fin_commitments.cmt_type) In ("GI","LI","TI")) AND (([cmt_amount]/[trn_amount1])<0) AND (([trn_tax1]/[trn_amount2])<0));


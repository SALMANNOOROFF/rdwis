-- Query: aud_revobj_alc
-- Type: 0

SELECT fin_sharesalloc.sha_id, fin_sharesalloc.sha_hed_id, fin_commitments.cmt_amount AS alloc, -1*[fin_commitments_1].[cmt_amount] AS mtss_share, fin_sharesalloc.sha_cf, fin_sharesalloc.sha_pcc, fin_sharesalloc.sha_prj
FROM (fin_sharesalloc INNER JOIN fin_commitments ON fin_sharesalloc.sha_ficmt_id = fin_commitments.cmt_id) INNER JOIN fin_commitments AS fin_commitments_1 ON fin_sharesalloc.sha_focmt_id = fin_commitments_1.cmt_id;


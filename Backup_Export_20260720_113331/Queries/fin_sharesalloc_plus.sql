-- Query: fin_sharesalloc_plus
-- Type: 0

SELECT fin_sharesalloc.sha_id, fin_sharesalloc.sha_hed_id, fin_sharesalloc.sha_ficmt_id, fin_commitments.cmt_amount AS alloc, fin_sharesalloc.sha_focmt_id, -1*[fin_commitments_1].[cmt_amount] AS mtss_share, fin_sharesalloc.sha_cf, fin_sharesalloc.sha_pcc, fin_sharesalloc.sha_transtype
FROM (fin_sharesalloc INNER JOIN fin_commitments ON fin_sharesalloc.sha_ficmt_id = fin_commitments.cmt_id) INNER JOIN fin_commitments AS fin_commitments_1 ON fin_sharesalloc.sha_focmt_id = fin_commitments_1.cmt_id
WHERE (((fin_sharesalloc.sha_hed_id)=[Forms]![vars]![Parameter1]));


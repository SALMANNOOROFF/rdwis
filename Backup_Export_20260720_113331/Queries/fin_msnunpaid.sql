-- Query: fin_msnunpaid
-- Type: 0

SELECT fin_msnunpaid1.*, [mct_cost]-Nz([received],0) AS balance, Round(([mct_cost]-Nz([received],0))*(([sha_cf]+[sha_pcc])/[cmt_amount]),0) AS amount
FROM fin_msnunpaid1 INNER JOIN (fin_sharesalloc INNER JOIN fin_commitments ON fin_sharesalloc.sha_ficmt_id = fin_commitments.cmt_id) ON fin_msnunpaid1.mct_hed_id = fin_sharesalloc.sha_hed_id
WHERE (((Round(([mct_cost]-Nz([received],0))*(([sha_cf]+[sha_pcc])/[cmt_amount]),0))<>0));


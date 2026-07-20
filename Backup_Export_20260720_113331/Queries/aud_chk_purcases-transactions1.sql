-- Query: aud_chk_purcases-transactions1
-- Type: 0

SELECT pur_purcases.pcs_id, First(cen_heads.hed_code) AS eff_hed, First(pur_purcases.pcs_midprice) AS pcs_midprice, First(pur_purcases.pcs_midtax) AS pcs_midtax, First(pur_purcases.pcs_price) AS pcs_price, First(pur_purcases.pcs_transtype) AS pcs_transtype, Sum(-1*[trn_amount1]) AS sum1, Sum(-1*[trn_amount2]) AS sum2, First(fin_transactions.trn_id) AS trn_id
FROM ((pur_purcases LEFT JOIN cen_heads ON pur_purcases.pcs_effhed_id = cen_heads.hed_id) INNER JOIN fin_commitments ON (pur_purcases.pcs_type = fin_commitments.cmt_type) AND (pur_purcases.pcs_id = fin_commitments.cmt_docid)) INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_status)="Paid"))
GROUP BY pur_purcases.pcs_id;


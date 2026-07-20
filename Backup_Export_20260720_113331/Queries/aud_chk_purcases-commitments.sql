-- Query: aud_chk_purcases-commitments
-- Type: 0

SELECT pur_purcases.pcs_id, cen_heads.hed_code AS eff_hed, pur_purcases.pcs_midprice, pur_purcases.pcs_midtax, pur_purcases.pcs_price, pur_purcases.pcs_transtype, IIf([pcs_transtype]=1,[pcs_midprice],[pcs_price]) AS eff_price, fin_commitments.cmt_amount
FROM (pur_purcases LEFT JOIN cen_heads ON pur_purcases.pcs_effhed_id = cen_heads.hed_id) INNER JOIN fin_commitments ON (pur_purcases.pcs_type = fin_commitments.cmt_type) AND (pur_purcases.pcs_id = fin_commitments.cmt_docid)
WHERE (((fin_commitments.cmt_amount)<>-1*(IIf([pcs_transtype]=1,[pcs_midprice],[pcs_price]))));


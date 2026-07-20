-- Query: pur_compstatementsub_doc
-- Type: 0

SELECT pur_quotes.qte_id, pur_quotes.qte_pcs_id, pur_quotes.qte_frm_id, pur_quotes.qte_price, pur_quotes.qte_techaccept, pur_quotes.qte_recomm, pur_quoteitems.qti_serial, pur_quoteitems.qti_desc, pur_quoteitems.qti_qty, pur_quoteitems.qti_qtyunit, pur_quoteitems.qti_price, pur_quotes.qte_midprice
FROM pur_quotes INNER JOIN pur_quoteitems ON pur_quotes.qte_id = pur_quoteitems.qti_qte_id
WHERE (((pur_quotes.qte_pcs_id)=getVar("Parameter1")));


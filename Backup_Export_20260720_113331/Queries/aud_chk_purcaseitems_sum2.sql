-- Query: aud_chk_purcaseitems_sum2
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_midprice, pur_purcases.pcs_price, pur_purcases.pcs_status, pur_purcases.pcs_status
FROM pur_purcases
WHERE (((pur_purcases.pcs_status)<>"Cancelled" And (pur_purcases.pcs_status)<>"Draft"));


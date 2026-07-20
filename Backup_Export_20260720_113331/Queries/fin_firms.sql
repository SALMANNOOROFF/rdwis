-- Query: fin_firms
-- Type: 0

SELECT units.unt_namesh, IIf(Nz([hed_code],"")="","Center",[hed_code]) AS project, pur_purcases.pcs_date, pur_purcases.pcs_title, frm_firms.frm_id, frm_firms.frm_name AS frm_name, pur_purcases.pcs_intprice
FROM ((pur_purcases LEFT JOIN cen_heads ON pur_purcases.pcs_hed_id = cen_heads.hed_id) INNER JOIN frm_firms ON pur_purcases.pcs_frm_id = frm_firms.frm_id) INNER JOIN units ON pur_purcases.pcs_unt_id = units.unt_id
WHERE (((pur_purcases.pcs_status) In ("Approved","Fulfilled","Partially Fulfilled")));


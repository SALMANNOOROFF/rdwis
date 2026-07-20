-- Query: frm_search_facils
-- Type: 0

SELECT frm_firms.frm_id, frm_facils.*
FROM frm_firms INNER JOIN frm_facils ON frm_firms.frm_id = frm_facils.fcl_xfrm_id;


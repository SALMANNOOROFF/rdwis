-- Query: frm_search_specs
-- Type: 0

SELECT frm_firms.frm_id, frm_specs.*
FROM frm_firms INNER JOIN frm_specs ON frm_firms.frm_id = frm_specs.spc_xfrm_id;


-- Query: frm_search_infofirms
-- Type: 0

SELECT frm_firms.frm_id, frm_info.*
FROM frm_firms INNER JOIN frm_info ON (frm_firms.frm_entity = frm_info.inf_xmsc_entity) AND (frm_firms.frm_id = frm_info.inf_xmsc_id);


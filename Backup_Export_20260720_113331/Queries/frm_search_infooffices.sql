-- Query: frm_search_infooffices
-- Type: 0

SELECT frm_firms.frm_id, frm_info.*
FROM frm_firms INNER JOIN (frm_offices INNER JOIN frm_info ON (frm_offices.off_entity = frm_info.inf_xmsc_entity) AND (frm_offices.off_id = frm_info.inf_xmsc_id)) ON frm_firms.frm_id = frm_offices.off_xfrm_id;


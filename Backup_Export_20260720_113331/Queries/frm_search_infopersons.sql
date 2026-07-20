-- Query: frm_search_infopersons
-- Type: 0

SELECT frm_firms.frm_id, frm_info.*
FROM frm_firms INNER JOIN (frm_persons INNER JOIN frm_info ON (frm_persons.per_entity = frm_info.inf_xmsc_entity) AND (frm_persons.per_id = frm_info.inf_xmsc_id)) ON frm_firms.frm_id = frm_persons.per_xfrm_id;


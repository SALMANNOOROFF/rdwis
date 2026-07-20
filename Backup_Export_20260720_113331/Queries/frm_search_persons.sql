-- Query: frm_search_persons
-- Type: 0

SELECT frm_firms.frm_id, frm_persons.*
FROM frm_firms INNER JOIN frm_persons ON frm_firms.frm_id = frm_persons.per_xfrm_id;


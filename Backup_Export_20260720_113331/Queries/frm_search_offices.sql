-- Query: frm_search_offices
-- Type: 0

SELECT frm_firms.frm_id, frm_offices.*
FROM frm_firms INNER JOIN frm_offices ON frm_firms.frm_id = frm_offices.off_xfrm_id;


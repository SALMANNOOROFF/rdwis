-- Query: aud_revs_plus
-- Type: 0

SELECT aud_revs.*, CLng([rev_objid]) AS rev_obj_id_num
FROM aud_revs;


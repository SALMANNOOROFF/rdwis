-- Query: pur_purreqs_fulfilreverse
-- Type: 48

UPDATE pur_purreqs INNER JOIN pur_purreqitems ON pur_purreqs.prq_id = pur_purreqitems.pri_prq_id SET pur_purreqs.prq_fulfilled = "0"
WHERE (((pur_purreqitems.pri_id)=[ReqItemId]));


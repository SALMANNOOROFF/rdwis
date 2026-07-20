-- Query: pur_purreqitems_appr_tempadder
-- Type: 64

INSERT INTO pur_purreqitems_appr_temp ( pri_prq_id, pri_serial, pri_id, pri_desc, pri_qty, pri_qtyunit, pri_appqty )
SELECT pur_purreqitems.pri_prq_id, pur_purreqitems.pri_serial, pur_purreqitems.pri_id, pur_purreqitems.pri_desc, pur_purreqitems.pri_qty, pur_purreqitems.pri_qtyunit, pur_purreqitems.pri_qty
FROM pur_purreqitems
WHERE (((pur_purreqitems.pri_prq_id)=[PurReqId]));


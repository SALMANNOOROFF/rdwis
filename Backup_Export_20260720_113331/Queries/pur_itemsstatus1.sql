-- Query: pur_itemsstatus1
-- Type: 0

SELECT pur_purreqs_u_open.prq_id, pur_purreqs_u_open.prq_date, pur_purreqitems.pri_id, pur_purreqitems.pri_desc, [pri_qty]-[pri_fulfilment] AS ur_qty, pur_purreqitems.pri_qtyunit, "Under Requisition" AS Status
FROM pur_purreqs_u_open INNER JOIN pur_purreqitems ON pur_purreqs_u_open.prq_id = pur_purreqitems.pri_prq_id;


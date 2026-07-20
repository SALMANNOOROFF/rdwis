-- Query: pur_itemsstatus3
-- Type: 0

SELECT pur_purreceipts.prt_id, pur_purreceipts.prt_date, pur_purreceiptitems.pti_id, pur_purreqitems.pri_id, pur_purreceiptitems.pti_desc, pur_purreceiptitems.pti_qty, pur_purreceiptitems.pti_qtyunit, "Received" AS status
FROM ((pur_purreqs_u_open INNER JOIN pur_purreqitems ON pur_purreqs_u_open.prq_id = pur_purreqitems.pri_prq_id) INNER JOIN pur_purreceiptitems ON pur_purreqitems.pri_id = pur_purreceiptitems.pti_pri_id) INNER JOIN pur_purreceipts ON pur_purreceiptitems.pti_prt_id = pur_purreceipts.prt_id
WHERE (((pur_purreceipts.prt_status)="Finalized"));


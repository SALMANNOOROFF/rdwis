-- Query: pur_itemsstatus2
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_date, pur_purcaseitems.pci_id, pur_purreqitems.pri_id, pur_purcaseitems.pci_desc, [pci_qty]-Nz([pci_fulfilment],0) AS up_qty, pur_purcaseitems.pci_qtyunit, "Under Procurement" AS status
FROM (pur_purreqs_u_open INNER JOIN (pur_purcaseitems INNER JOIN pur_purreqitems ON pur_purcaseitems.pci_pri_id = pur_purreqitems.pri_id) ON pur_purreqs_u_open.prq_id = pur_purreqitems.pri_prq_id) INNER JOIN pur_purcases ON pur_purcaseitems.pci_pcs_id = pur_purcases.pcs_id
WHERE ((([pci_qty]-Nz([pci_fulfilment],0))<>0) AND ((pur_purcases.pcs_status) Not In ("Draft","Cancelled")));


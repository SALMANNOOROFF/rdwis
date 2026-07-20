-- Query: pur_purreceiptitems_plus
-- Type: 0

SELECT pur_purreceiptitems.*, pur_purcaseitems.pci_fulfilment, pur_purcaseitems.pci_category
FROM pur_purreceiptitems INNER JOIN pur_purcaseitems ON pur_purreceiptitems.pti_pci_id = pur_purcaseitems.pci_id;


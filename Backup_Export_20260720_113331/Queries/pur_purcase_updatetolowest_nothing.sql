-- Query: pur_purcase_updatetolowest_nothing
-- Type: 48

UPDATE pur_purcaseitems SET pur_purcaseitems.pci_price = ""
WHERE (((pur_purcaseitems.pci_pcs_id)=[PurCaseId]));


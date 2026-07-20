-- Query: pur_quoteitems_plus
-- Type: 0

SELECT pur_quoteitems.*, pur_purcaseitems.pci_type
FROM pur_purcaseitems INNER JOIN ((pur_quoteitems INNER JOIN pur_quotes ON pur_quoteitems.qti_qte_id = pur_quotes.qte_id) INNER JOIN pur_purcases ON pur_quotes.qte_pcs_id = pur_purcases.pcs_id) ON (pur_purcaseitems.pci_pcs_id = pur_purcases.pcs_id) AND (pur_quoteitems.qti_serial = pur_purcaseitems.pci_serial);


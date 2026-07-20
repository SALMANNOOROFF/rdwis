-- Query: pur_purcaseitems_bycat1
-- Type: 0

SELECT pur_purcaseitems.pci_type, [pci_price]*[pci_qty] AS price, Round([pci_price]*[pci_qty]*TaxPercentage([pci_type]),0) AS tax_prop, pur_purcaseitems.pci_category, pur_purcases.pcs_midtax
FROM pur_purcases INNER JOIN pur_purcaseitems ON pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id
WHERE (((pur_purcaseitems.pci_pcs_id)=[PcsId]));


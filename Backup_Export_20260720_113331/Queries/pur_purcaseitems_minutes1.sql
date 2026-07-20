-- Query: pur_purcaseitems_minutes1
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_title, pur_purcases.pcs_intprice, pur_purcases.pcs_inttax, pur_purcases.pcs_midprice, pur_purcases.pcs_midtax, pur_purcases.pcs_price, pur_purcases.pcs_transtype, pur_purcaseitems.pci_serial, pur_purcaseitems.pci_desc, pur_purcaseitems.pci_qty, pur_purcaseitems.pci_qtyunit, pur_purcaseitems.pci_price, pur_purcaseitems.pci_type, pur_purcaseitems.pci_subtype, pur_purcaseitems.pci_type2, pur_purcaseitems.pci_subhead, pur_purcaseitems.pci_category, pur_purcaseitems.pci_pcs_id
FROM pur_purcases INNER JOIN pur_purcaseitems ON pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id;


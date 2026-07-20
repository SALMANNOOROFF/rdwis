-- Query: xxx1-2
-- Type: 0

SELECT pur_purcases.pcs_hed_id, Count(pur_purcaseitems.pci_desc) AS materials
FROM pur_purcases INNER JOIN pur_purcaseitems ON pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id
WHERE (((pur_purcaseitems.pci_fulfilment) Is Not Null) AND ((pur_purcaseitems.pci_type)<>3))
GROUP BY pur_purcases.pcs_hed_id;


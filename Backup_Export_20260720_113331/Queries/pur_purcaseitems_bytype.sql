-- Query: pur_purcaseitems_bytype
-- Type: 0

SELECT pur_purcaseitems.pci_type AS type, Sum([pci_price]*[pci_qty]) AS price_sum
FROM pur_purcaseitems
WHERE (((pur_purcaseitems.pci_pcs_id)=[PcsId]))
GROUP BY pur_purcaseitems.pci_type;


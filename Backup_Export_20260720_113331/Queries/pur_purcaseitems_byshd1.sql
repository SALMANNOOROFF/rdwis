-- Query: pur_purcaseitems_byshd1
-- Type: 0

SELECT First(pur_purcaseitems.pci_serial) AS pci_serial, pur_purcaseitems.pci_subhead, pur_purcaseitems.pci_type, Sum([pci_price]*[pci_qty])*IIf([pci_type]=3,(1+TaxRate([pci_type])),1) AS price1, Sum([pci_price]*[pci_qty])*(1+TaxRate([pci_type])) AS price2
FROM pur_purcaseitems
WHERE (((pur_purcaseitems.pci_pcs_id)=[PcsId]))
GROUP BY pur_purcaseitems.pci_subhead, pur_purcaseitems.pci_type
ORDER BY First(pur_purcaseitems.pci_serial);


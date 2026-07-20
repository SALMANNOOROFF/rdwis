-- Query: pur_purcaseitems_tempadder
-- Type: 64

INSERT INTO pur_purcaseitems_temp ( pci_pri_id, pci_pcs_id, pci_id, pci_serial, pci_desc, pci_qty, pci_qtyunit, pci_price, balance_qty, selected, unt_id, prj_id )
SELECT pur_purcaseitems.pci_pri_id, pur_purcaseitems.pci_pcs_id, pur_purcaseitems.pci_id, pur_purcaseitems.pci_serial, pur_purcaseitems.pci_desc, pur_purcaseitems.pci_qty, pur_purcaseitems.pci_qtyunit, pur_purcaseitems.pci_price, [pci_qty]-Nz([pci_fulfilment]) AS balance, False AS selected, [UnitID] AS Expr1, [HeadId] AS Expr2
FROM pur_purcaseitems
WHERE (((pur_purcaseitems.pci_pcs_id)=[PurCaseId]) AND (([pci_qty]-Nz([pci_fulfilment]))>0));


-- Query: pur_purreqitems_tempadder
-- Type: 64

INSERT INTO pur_purreqitems_temp ( pri_prq_id, pri_serial, pri_id, pri_desc, pri_qty, pri_qtyunit, pri_appqty, pri_fulfilment, balance_qty, required_qty, pri_price, selected, unt_id, hed_id, effhed_id )
SELECT pur_purreqitems.pri_prq_id, pur_purreqitems.pri_serial, pur_purreqitems.pri_id, pur_purreqitems.pri_desc, pur_purreqitems.pri_qty, pur_purreqitems.pri_qtyunit, pur_purreqitems.pri_appqty, pur_purreqitems.pri_fulfilment, Nz([pri_appqty],[pri_qty])-Nz([pri_fulfilment])-Nz([sum_pci_qty]) AS balance, 0 AS required, pur_purreqitems.pri_price, False AS selected, [UnitID] AS Expr1, [HeadId] AS Expr2, [EffHeadId] AS Expr3
FROM pur_purreqitems LEFT JOIN pur_purreqitems_tempadder1 ON pur_purreqitems.pri_id = pur_purreqitems_tempadder1.pci_pri_id
WHERE (((pur_purreqitems.pri_prq_id)=[PurReqId]) AND ((Nz([pri_appqty],[pri_qty])-Nz([pri_fulfilment])-Nz([sum_pci_qty]))>0));


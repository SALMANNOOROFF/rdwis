-- Query: pur_purreqitems_fulfil
-- Type: 48

UPDATE pur_purreqitems SET pur_purreqitems.pri_fulfilment = Nz([pri_fulfilment],0)+[ChangeQty]
WHERE (((pur_purreqitems.pri_id)=[ItemId]));


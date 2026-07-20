-- Query: pur_purreqitems_plus
-- Type: 0

SELECT pur_purreqitems.*, [pri_price]*[pri_qty] AS price_total, Nz([pri_appqty],[pri_qty])-Nz([pri_fulfilment],0) AS balance_qty
FROM pur_purreqitems;


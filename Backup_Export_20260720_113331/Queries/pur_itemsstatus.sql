-- Query: pur_itemsstatus
-- Type: 128

SELECT pur_itemsstatus1.* FROM pur_itemsstatus1
UNION
SELECT pur_itemsstatus2.* FROM pur_itemsstatus2
UNION SELECT pur_itemsstatus3.* FROM pur_itemsstatus3;


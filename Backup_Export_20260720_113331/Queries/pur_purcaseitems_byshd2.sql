-- Query: pur_purcaseitems_byshd2
-- Type: 0

SELECT Sum(Round([price1],2)) AS sum_price1, Sum(Round([price2],2)) AS sum_price2
FROM pur_purcaseitems_byshd1;


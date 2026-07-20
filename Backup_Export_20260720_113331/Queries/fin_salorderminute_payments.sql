-- Query: fin_salorderminute_payments
-- Type: 0

SELECT Count(fin_salorderminute_payments1.sor_id) AS payments, Sum(fin_salorderminute_payments1.parent) AS parents, Sum(fin_salorderminute_payments1.child) AS children
FROM fin_salorderminute_payments1;


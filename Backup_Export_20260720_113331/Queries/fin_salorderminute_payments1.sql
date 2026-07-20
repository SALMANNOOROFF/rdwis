-- Query: fin_salorderminute_payments1
-- Type: 0

SELECT fin_salorders.sor_id, fin_salorders.sor_parent, IIf([sor_parent]=0,1,0) AS parent, IIf([sor_parent]>0,1,0) AS child, fin_salorders.sor_status
FROM fin_salorders
WHERE (((fin_salorders.sor_status)='Draft'));


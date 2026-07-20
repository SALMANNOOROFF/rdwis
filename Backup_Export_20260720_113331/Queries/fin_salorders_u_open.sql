-- Query: fin_salorders_u_open
-- Type: 0

SELECT fin_salorders.*
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Approved" Or (fin_salorders.sor_status)="Under Revision"));


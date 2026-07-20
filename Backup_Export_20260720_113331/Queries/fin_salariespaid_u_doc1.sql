-- Query: fin_salariespaid_u_doc1
-- Type: 0

SELECT Max(fin_salorders.sor_month) AS MaxOfsor_month
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Fulfilled"));


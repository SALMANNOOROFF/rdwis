-- Query: cen_accounts_closed
-- Type: 0

SELECT cen_accounts.*
FROM cen_accounts
WHERE (((cen_accounts.acc_status)="Closed"))
ORDER BY cen_accounts.acc_enddt DESC;


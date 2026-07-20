-- Query: cen_accounts_open
-- Type: 0

SELECT cen_accounts.*
FROM cen_accounts
WHERE (((cen_accounts.acc_status)<>"Closed"))
ORDER BY cen_accounts.acc_startdt DESC;


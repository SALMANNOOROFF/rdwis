-- Query: hr_bnkaccountsforpay
-- Type: 0

SELECT hr_bnkaccounts.*
FROM hr_bnkaccounts
WHERE (((hr_bnkaccounts.bac_selforpay)="1"));


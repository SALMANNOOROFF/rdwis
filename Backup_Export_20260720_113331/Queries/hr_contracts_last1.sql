-- Query: hr_contracts_last1
-- Type: 0

SELECT hr_contracts.ctr_num, Max(hr_contracts.ctr_date) AS MaxOfctr_date
FROM hr_contracts
GROUP BY hr_contracts.ctr_num;


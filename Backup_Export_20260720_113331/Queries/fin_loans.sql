-- Query: fin_loans
-- Type: 16

TRANSFORM Sum(fin_loans1.amount) AS SumOfamount
SELECT fin_loans1.[For Project], Sum(fin_loans1.amount) AS SumOfamount1
FROM fin_loans1
GROUP BY fin_loans1.[For Project]
PIVOT fin_loans1.[From Project];


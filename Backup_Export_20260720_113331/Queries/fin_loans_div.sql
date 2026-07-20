-- Query: fin_loans_div
-- Type: 16

TRANSFORM First(fin_loans_div4.A) AS FirstOfA
SELECT fin_loans_div4.For, Sum(fin_loans_div4.A) AS [Sum]
FROM fin_loans_div4
GROUP BY fin_loans_div4.For
PIVOT fin_loans_div4.From;


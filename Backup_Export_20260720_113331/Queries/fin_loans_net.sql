-- Query: fin_loans_net
-- Type: 16

TRANSFORM First(fin_loans_net3.A) AS FirstOfA
SELECT fin_loans_net3.For, Sum(fin_loans_net3.A) AS [Sum]
FROM fin_loans_net3
GROUP BY fin_loans_net3.For
PIVOT fin_loans_net3.From;


-- Query: fin_ecbreakdown
-- Type: 0

SELECT fin_ecbreakdown_temp.subtype, Sum(fin_ecbreakdown_temp.amount) AS sum_amount
FROM fin_ecbreakdown_temp
GROUP BY fin_ecbreakdown_temp.subtype
ORDER BY Sum(fin_ecbreakdown_temp.amount) DESC;


-- Query: fin_loans_div2
-- Type: 0

SELECT fin_loans_div1.[Loan String], fin_loans_div1.From, fin_loans_div1.For, Sum(fin_loans_div1.amount) AS SumOfamount
FROM fin_loans_div1
GROUP BY fin_loans_div1.[Loan String], fin_loans_div1.From, fin_loans_div1.For;


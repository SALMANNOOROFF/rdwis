-- Query: fin_loans_div3
-- Type: 0

SELECT fin_loans_div2.[Loan String], fin_loans_div2.From, fin_loans_div2.For, fin_loans_div2.SumOfamount AS A1, IIf([fin_loans_div2_1].[SumOfamount]<=[fin_loans_div2].[SumOfamount],-1*[fin_loans_div2_1].[SumOfamount],[fin_loans_div2_1].[SumOfamount]) AS A2
FROM fin_loans_div2 LEFT JOIN fin_loans_div2 AS fin_loans_div2_1 ON (fin_loans_div2.For = fin_loans_div2_1.From) AND (fin_loans_div2.From = fin_loans_div2_1.For);


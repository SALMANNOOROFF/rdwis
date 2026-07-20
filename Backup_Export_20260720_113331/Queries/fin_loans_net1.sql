-- Query: fin_loans_net1
-- Type: 0

SELECT fin_loans1.loan_string, IIf(IsNull([From Project]),"RGA",[From Project]) AS [From], IIf(IsNull([For Project]),"RGA",[For Project]) AS [For], Sum(fin_loans1.amount) AS SumOfamount, First(fin_loans1.[From Div]) AS FromDiv, First(fin_loans1.[For Div]) AS ForDiv
FROM fin_loans1
GROUP BY fin_loans1.loan_string, IIf(IsNull([From Project]),"RGA",[From Project]), IIf(IsNull([For Project]),"RGA",[For Project])
ORDER BY fin_loans1.loan_string;


-- Query: fin_loans_net3
-- Type: 0

SELECT fin_loans_net2.loan_string, fin_loans_net2.From, fin_loans_net2.For, fin_loans_net2.A1, fin_loans_net2.A2, [A1]+Nz([A2],0) AS A, fin_loans_net2.FromDiv, fin_loans_net2.ForDiv
FROM fin_loans_net2
WHERE (((fin_loans_net2.A1)<>[A2]*(-1)) AND ((fin_loans_net2.A2)<0)) OR (((fin_loans_net2.A2) Is Null));


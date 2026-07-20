-- Query: fin_expenditure
-- Type: 0

SELECT fin_expenditure1.docid, fin_expenditure1.doctype, DateValue([rdate]) AS rsdate, fin_expenditure1.title, fin_expenditure1.effhed_id, fin_expenditure1.effunt_id, fin_expenditure1.hed_id, fin_expenditure1.unt_id, fin_expenditure1.amount1, fin_expenditure1.tax1, fin_expenditure1.amount2, fin_expenditure1.trn_id, fin_expenditure1.trn_transtype, IIf([doctype]="Sa",0,[docid]) AS sum_id, IIf([doctype]="Sa","Monthly Salary for " & Format([docdate],"mmm yy"),[title]) AS title2
FROM fin_expenditure1
ORDER BY DateValue([rdate]);


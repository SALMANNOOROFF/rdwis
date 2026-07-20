-- Query: fin_expenditure1
-- Type: 128

Select * From fin_expenditure_trfx
UNION
Select * From fin_expenditure_pcs
UNION Select * From fin_expenditure_so;


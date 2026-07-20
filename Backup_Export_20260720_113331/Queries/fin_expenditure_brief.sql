-- Query: fin_expenditure_brief
-- Type: 0

SELECT fin_expenditure.effhed_id, fin_expenditure.sum_id, fin_expenditure.title2, First(fin_expenditure.rsdate) AS FirstOfrsdate, First(fin_expenditure.docid) AS FirstOfdocid, First(fin_expenditure.doctype) AS FirstOfdoctype, First(fin_expenditure.hed_id) AS FirstOfhed_id, First(fin_expenditure.effunt_id) AS FirstOfeffunt_id, First(fin_expenditure.unt_id) AS FirstOfunt_id, Sum(fin_expenditure.amount1) AS SumOfamount1, Sum(fin_expenditure.tax1) AS SumOftax1, Sum(fin_expenditure.amount2) AS SumOfamount2, First(fin_expenditure.trn_id) AS FirstOftrn_id, First(fin_expenditure.trn_transtype) AS FirstOftrn_transtype
FROM fin_expenditure
GROUP BY fin_expenditure.effhed_id, fin_expenditure.sum_id, fin_expenditure.title2;


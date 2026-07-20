-- Query: fin_loans_detail2
-- Type: 0

SELECT fin_loans_detail1_temp.loan_group, Min(fin_loans_detail1_temp.trn_date) AS min_date
FROM fin_loans_detail1_temp
GROUP BY fin_loans_detail1_temp.loan_group;


-- Query: fin_sts_prj_exp
-- Type: 0

SELECT fin_sts_prj_exp1.subhead, Sum(fin_sts_prj_exp1.amount) AS SumOfamount
FROM fin_sts_prj_exp1
GROUP BY fin_sts_prj_exp1.subhead;


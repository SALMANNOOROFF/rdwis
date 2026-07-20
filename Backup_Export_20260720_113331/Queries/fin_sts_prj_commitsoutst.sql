-- Query: fin_sts_prj_commitsoutst
-- Type: 0

SELECT fin_sts_prj_commitsoutst1.subhead, Sum([cmt_amountz]-Nz([amount],0)) AS SumOfamount
FROM fin_sts_prj_commitsoutst1
GROUP BY fin_sts_prj_commitsoutst1.subhead;


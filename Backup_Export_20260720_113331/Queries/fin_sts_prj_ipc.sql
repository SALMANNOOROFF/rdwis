-- Query: fin_sts_prj_ipc
-- Type: 0

SELECT fin_sts_prj_ipc1.subhead, Sum([amount]*[ratio]) AS sumOfAmount
FROM fin_sts_prj_ipc1
GROUP BY fin_sts_prj_ipc1.subhead;


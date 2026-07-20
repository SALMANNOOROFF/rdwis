-- Query: fin_firms_overall1
-- Type: 0

SELECT fin_firms.frm_name, Sum(fin_firms.pcs_intprice) AS pcs_intprice
FROM fin_firms
GROUP BY fin_firms.frm_name;


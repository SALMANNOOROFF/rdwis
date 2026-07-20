-- Query: fin_firms_overall2
-- Type: 0

SELECT Sum(Round([pcs_intprice],0)) AS grand_total
FROM fin_firms_overall1;


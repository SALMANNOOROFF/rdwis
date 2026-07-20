-- Query: fin_salorder_last
-- Type: 0

SELECT fin_salorders.sor_id, fin_salorders.sor_releasedtg, fin_salorders.sor_effhed_id, fin_salorders.sor_sudohed
FROM fin_salorder_last1 INNER JOIN fin_salorders ON fin_salorder_last1.MaxOfsor_releasedtg = fin_salorders.sor_releasedtg;


-- Query: fin_timeline_sal1
-- Type: 0

SELECT fin_salforecast_temp.sal_month, Sum(fin_salforecast_temp.salary) AS sal_amount, First(fin_salforecast_temp.id) AS num
FROM fin_salforecast_temp
WHERE (((fin_salforecast_temp.salary)<>0))
GROUP BY fin_salforecast_temp.sal_month
ORDER BY fin_salforecast_temp.sal_month;


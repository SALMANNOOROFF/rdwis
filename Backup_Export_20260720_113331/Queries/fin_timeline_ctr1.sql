-- Query: fin_timeline_ctr1
-- Type: 0

SELECT DISTINCT fin_salforecast_temp.ctr_id, fin_salforecast_temp.cpn_hed_id, fin_salforecast_temp.ctr_salary, First(fin_salforecast_temp.ctr_num) AS ctr_num, First(fin_salforecast_temp.emp_name) AS emp_name, Min(fin_salforecast_temp.cpn_effstartdt) AS start_date, Max(fin_salforecast_temp.cpn_effenddt) AS end_date, Sum(fin_salforecast_temp.salary) AS salary, First(ProjectFromHead([cpn_hed_id])) AS project
FROM fin_salforecast_temp
WHERE (((fin_salforecast_temp.salary)<>0))
GROUP BY fin_salforecast_temp.ctr_id, fin_salforecast_temp.cpn_hed_id, fin_salforecast_temp.ctr_salary;


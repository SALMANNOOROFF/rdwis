-- Query: fin_salorders_u
-- Type: 0

SELECT fin_salorders.*
FROM fin_salorders
WHERE (((fin_salorders.sor_unt_id)>=getvar("varLower") And (fin_salorders.sor_unt_id)<=getvar("varUpper")))
ORDER BY fin_salorders.sor_month DESC , fin_salorders.sor_unt_id, fin_salorders.sor_hed_id, fin_salorders.sor_emp_id;


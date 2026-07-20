-- Query: fin_salorderminute_emps1
-- Type: 0

SELECT DISTINCT fin_salorders.sor_emp_id
FROM fin_salorders
WHERE (((fin_salorders.sor_status)='Draft'));


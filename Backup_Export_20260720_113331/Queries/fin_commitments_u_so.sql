-- Query: fin_commitments_u_so
-- Type: 0

SELECT fin_salorders.sor_id, fin_salorders.sor_emp_id, fin_salorders.sor_empnamecomp, fin_salorders.sor_hed_id, fin_salorders.sor_unt_id, fin_salorders.sor_effhed_id, fin_salorders.sor_effunt_id, fin_salorders.sor_closedtg, fin_salorders.sor_salary, fin_salorders.sor_month, fin_commitments.cmt_status, fin_commitments.cmt_id, fin_salorders.sor_transtype, fin_salorders.sor_noloan, fin_commitments.cmt_amount
FROM fin_salorders INNER JOIN fin_commitments ON (fin_salorders.sor_id = fin_commitments.cmt_docid) AND (fin_salorders.sor_type = fin_commitments.cmt_type)
WHERE (((fin_salorders.sor_unt_id)>=getvar("varLower") And (fin_salorders.sor_unt_id)<=getvar("varUpper")) AND ((fin_salorders.sor_status)<>"Cancelled"))
ORDER BY fin_salorders.sor_month DESC , fin_salorders.sor_unt_id, fin_salorders.sor_hed_id;


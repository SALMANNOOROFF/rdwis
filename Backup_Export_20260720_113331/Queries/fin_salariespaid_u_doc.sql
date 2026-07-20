-- Query: fin_salariespaid_u_doc
-- Type: 0

SELECT fin_salorders.sor_empnamecomp, fin_salorders.sor_month, fin_salorders.sor_salary, fin_salorders.sor_unt_id, fin_salorders.sor_hed_id, fin_salorders.sor_effunt_id, fin_salorders.sor_effhed_id, IIf([sor_unt_id]=GetVar("varUnitId"),1,0) AS my_person, IIf([sor_unt_id]=[sor_effunt_id],1,0) AS paid_same
FROM fin_salorders INNER JOIN fin_salariespaid_u_doc1 ON fin_salorders.sor_month = fin_salariespaid_u_doc1.MaxOfsor_month
WHERE (((fin_salorders.sor_unt_id)>=getvar("varLower") And (fin_salorders.sor_unt_id)<=getvar("varUpper"))) OR (((fin_salorders.sor_effunt_id)>=getvar("varLower") And (fin_salorders.sor_effunt_id)<=getvar("varUpper")))
ORDER BY IIf([sor_unt_id]=GetVar("varUnitId"),1,0) DESC , IIf([sor_unt_id]=[sor_effunt_id],1,0) DESC;


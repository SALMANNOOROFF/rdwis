-- Query: fin_ecbreakdown_so_tempadder
-- Type: 64

INSERT INTO fin_ecbreakdown_temp ( id, subtype, description, amount, status )
SELECT fin_salorders.sor_id, "Salary" AS Salary, fin_salorders.sor_empnamecomp, fin_salorders.sor_salary, fin_salorders.sor_status
FROM fin_salorders
WHERE (((fin_salorders.sor_status)="Approved") And ((fin_salorders.sor_hed_id)=Forms!vars!Parameter1) And ((fin_salorders.sor_sudohed) Is Null Or (fin_salorders.sor_sudohed)="")) Or (((fin_salorders.sor_status)="Fulfilled") And ((fin_salorders.sor_hed_id)=Forms!vars!Parameter1) And ((fin_salorders.sor_sudohed) Is Null Or (fin_salorders.sor_sudohed)=""))
ORDER BY fin_salorders.sor_salary DESC;


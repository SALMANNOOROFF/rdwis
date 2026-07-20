-- Query: pur_purcases_u_open
-- Type: 0

SELECT pur_purcases_u.*
FROM pur_purcases_u
WHERE (((pur_purcases_u.pcs_status) In ("Under Scrutiny","Under Revision","Under Approval","Approved")) AND ((pur_purcases_u.pcs_unt_id)>=getvar("varLower") And (pur_purcases_u.pcs_unt_id)<=getvar("varUpper"))) OR (((pur_purcases_u.pcs_status) In ("Under Scrutiny","Under Revision","Under Approval","Approved")) AND ((pur_purcases_u.pcs_intunt_id)>=getvar("varLower") And (pur_purcases_u.pcs_intunt_id)<=getvar("varUpper")))
ORDER BY pur_purcases_u.pcs_status DESC , pur_purcases_u.pcs_id DESC;


-- Query: pur_purreqs_u_open
-- Type: 0

SELECT pur_purreqs.*
FROM pur_purreqs
WHERE (((pur_purreqs.prq_unt_id)>=getvar("varLower") And (pur_purreqs.prq_unt_id)<=getvar("varUpper")) AND ((pur_purreqs.prq_status) In ("Under Scrutiny","Under Approval","Approved","Revision Required"))) OR (((pur_purreqs.prq_status) In ("Under Scrutiny","Under Approval","Approved","Revision Required")) AND ((pur_purreqs.prq_intunt_id)>=getvar("varLower") And (pur_purreqs.prq_intunt_id)<=getvar("varUpper")));


-- Query: pur_purreqs_u
-- Type: 0

SELECT pur_purreqs.*, pur_purreqs.prq_intunt_id
FROM pur_purreqs
WHERE (((pur_purreqs.prq_unt_id)>=getvar("varLower") And (pur_purreqs.prq_unt_id)<=getvar("varUpper"))) OR (((pur_purreqs.prq_intunt_id)>=getvar("varLower") And (pur_purreqs.prq_intunt_id)<=getvar("varUpper")));


-- Query: pur_purreqs_doc
-- Type: 0

SELECT pur_purreqs.*
FROM pur_purreqs
WHERE (((pur_purreqs.prq_id)=getVar("Parameter1")));


-- Query: aud_revs_u
-- Type: 0

SELECT aud_revs.*
FROM aud_revs
WHERE (((aud_revs.rev_intunt_id)>=getvar("varLower") And (aud_revs.rev_intunt_id)<=getvar("varUpper")))
ORDER BY aud_revs.rev_id DESC;


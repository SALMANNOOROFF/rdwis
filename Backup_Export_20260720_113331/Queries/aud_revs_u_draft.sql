-- Query: aud_revs_u_draft
-- Type: 0

SELECT aud_revs_u.*
FROM aud_revs_u
WHERE (((aud_revs_u.rev_status)="Draft"))
ORDER BY aud_revs_u.rev_id DESC;


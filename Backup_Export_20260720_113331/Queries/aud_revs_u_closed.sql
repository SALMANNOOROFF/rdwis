-- Query: aud_revs_u_closed
-- Type: 0

SELECT aud_revs_u.*
FROM aud_revs_u
WHERE (((aud_revs_u.rev_status)="Fulfilled" Or (aud_revs_u.rev_status)="Cancelled"));


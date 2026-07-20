-- Query: fin_commitments_u_so_open
-- Type: 0

SELECT fin_commitments_u_so.*
FROM fin_commitments_u_so
WHERE (((fin_commitments_u_so.cmt_status)="Awaited"));


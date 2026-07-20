-- Query: fin_empeffheads_u_open
-- Type: 0

SELECT fin_empeffheads_u.*, fin_empeffheads_u.eeh_status
FROM fin_empeffheads_u
WHERE (((fin_empeffheads_u.eeh_status)="Open"));


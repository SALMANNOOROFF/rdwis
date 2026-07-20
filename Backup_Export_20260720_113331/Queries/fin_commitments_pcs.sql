-- Query: fin_commitments_pcs
-- Type: 0

SELECT fin_commitments.*
FROM fin_commitments
WHERE (((fin_commitments.cmt_type)="Ps" Or (fin_commitments.cmt_type)="Pt" Or (fin_commitments.cmt_type)="Rb"));


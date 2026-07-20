-- Query: fin_commitments_trf
-- Type: 0

SELECT fin_commitments.*
FROM fin_commitments
WHERE (((fin_commitments.cmt_type)="FI" Or (fin_commitments.cmt_type)="FO" Or (fin_commitments.cmt_type)="LI" Or (fin_commitments.cmt_type)="LO" Or (fin_commitments.cmt_type)="TI" Or (fin_commitments.cmt_type)="TO"));


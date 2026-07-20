-- Query: fin_loanadjustments_self
-- Type: 0

SELECT fin_loanadjustments.*
FROM fin_loanadjustments
WHERE (((fin_loanadjustments.lad_remarks) Like "Amount added to CSRF*" And (fin_loanadjustments.lad_remarks) Not Like "Amount added to CSRF of *"));


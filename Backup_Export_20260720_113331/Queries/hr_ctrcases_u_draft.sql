-- Query: hr_ctrcases_u_draft
-- Type: 0

SELECT hr_ctrcases_u.*
FROM hr_ctrcases_u
WHERE (((hr_ctrcases_u.ctc_status)="Draft"))
ORDER BY hr_ctrcases_u.ctc_id DESC;


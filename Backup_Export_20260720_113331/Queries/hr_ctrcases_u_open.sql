-- Query: hr_ctrcases_u_open
-- Type: 0

SELECT hr_ctrcases_u.*
FROM hr_ctrcases_u
WHERE (((hr_ctrcases_u.ctc_status) Not In ("Draft","Fulfilled","Cancelled","Not Approved")))
ORDER BY hr_ctrcases_u.ctc_id DESC;


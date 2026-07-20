-- Query: hr_ctrcaseminute_status_chrf
-- Type: 0

SELECT Sum(hr_ctrcases.ctc_price) AS amount, First(hr_ctrcases.ctc_status) AS status
FROM hr_ctrcases
WHERE (((hr_ctrcases.ctc_id) In (13)));


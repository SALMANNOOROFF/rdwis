-- Query: hr_ctrcaseminute_status_grouped
-- Type: 0

SELECT hr_ctrcaseminute_status.ccp_hed_id, First(hr_ctrcaseminute_status.head_code) AS head_code, Sum(hr_ctrcaseminute_status.amount) AS amount, First(hr_ctrcaseminute_status.ctc_status) AS ctc_status
FROM hr_ctrcaseminute_status
GROUP BY hr_ctrcaseminute_status.ccp_hed_id
ORDER BY hr_ctrcaseminute_status.ccp_hed_id DESC;


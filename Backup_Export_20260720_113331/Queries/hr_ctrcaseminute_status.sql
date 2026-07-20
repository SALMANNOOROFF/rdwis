-- Query: hr_ctrcaseminute_status
-- Type: 0

SELECT hr_ctrcaseminute_status1.ccp_hed_id, hr_ctrcaseminute_status1.head_code, MakeMonthString([ctc_id],[ccp_hed_id]) AS months, Sum(hr_ctrcaseminute_status1.amount) AS amount, First(hr_ctrcaseminute_status1.sorter) AS sorter, First(hr_ctrcaseminute_status1.ctc_status) AS ctc_status
FROM hr_ctrcaseminute_status1
GROUP BY hr_ctrcaseminute_status1.ccp_hed_id, hr_ctrcaseminute_status1.head_code, MakeMonthString([ctc_id],[ccp_hed_id])
ORDER BY First(hr_ctrcaseminute_status1.sorter);


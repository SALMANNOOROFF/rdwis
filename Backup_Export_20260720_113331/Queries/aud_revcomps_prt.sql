-- Query: aud_revcomps_prt
-- Type: 0

SELECT aud_revcomps_plus.rvc_rev_id, pur_purreceipts.prt_id, pur_purreceipts.prt_date
FROM aud_revcomps_plus INNER JOIN pur_purreceipts ON aud_revcomps_plus.rvc_rowid_num = pur_purreceipts.prt_id
WHERE (((aud_revcomps_plus.rvc_table)="pur_purreceipts"));


-- Query: aud_revcomps_pcs
-- Type: 0

SELECT aud_revcomps_plus.rvc_rev_id, pur_purcases.*
FROM aud_revcomps_plus INNER JOIN pur_purcases ON aud_revcomps_plus.rvc_rowid_num = pur_purcases.pcs_id
WHERE (((aud_revcomps_plus.rvc_table)="pur_purcases"));


-- Query: aud_revcomps_sor
-- Type: 0

SELECT fin_salorders.*, aud_revcomps_plus.rvc_rev_id
FROM aud_revcomps_plus INNER JOIN fin_salorders ON aud_revcomps_plus.rvc_rowid_num = fin_salorders.sor_id
WHERE (((aud_revcomps_plus.rvc_table)="fin_salorders"));


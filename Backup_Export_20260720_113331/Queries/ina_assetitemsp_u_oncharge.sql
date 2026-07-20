-- Query: ina_assetitemsp_u_oncharge
-- Type: 0

SELECT ina_assetitemsp_u.*
FROM ina_assetitemsp_u
WHERE (((ina_assetitemsp_u.iac_status)="Untagged" Or (ina_assetitemsp_u.iac_status)="Tagged" Or (ina_assetitemsp_u.iac_status)="Held"));


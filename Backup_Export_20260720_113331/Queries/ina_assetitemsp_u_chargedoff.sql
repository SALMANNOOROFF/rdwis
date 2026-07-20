-- Query: ina_assetitemsp_u_chargedoff
-- Type: 0

SELECT ina_assetitemsp_u.*
FROM ina_assetitemsp_u
WHERE (((ina_assetitemsp_u.iac_status)="Issued to User" Or (ina_assetitemsp_u.iac_status)="Installed" Or (ina_assetitemsp_u.iac_status)="Consumed" Or (ina_assetitemsp_u.iac_status)="Written Off"));


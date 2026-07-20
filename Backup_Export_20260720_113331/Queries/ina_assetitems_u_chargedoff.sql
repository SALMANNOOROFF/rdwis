-- Query: ina_assetitems_u_chargedoff
-- Type: 0

SELECT ina_assetitems_u.*
FROM ina_assetitems_u
WHERE (((ina_assetitems_u.iac_status)="Issued to User" Or (ina_assetitems_u.iac_status)="Installed" Or (ina_assetitems_u.iac_status)="Consumed" Or (ina_assetitems_u.iac_status)="Written Off"));


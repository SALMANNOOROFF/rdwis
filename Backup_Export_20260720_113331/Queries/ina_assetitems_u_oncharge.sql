-- Query: ina_assetitems_u_oncharge
-- Type: 0

SELECT ina_assetitems_u.*
FROM ina_assetitems_u
WHERE (((ina_assetitems_u.iac_status)="Untagged" Or (ina_assetitems_u.iac_status)="Tagged" Or (ina_assetitems_u.iac_status)="Held"));


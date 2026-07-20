-- Query: ina_inventoryitems_u_oncharge
-- Type: 0

SELECT ina_inventoryitems_u.*
FROM ina_inventoryitems_u
WHERE (((ina_inventoryitems_u.iac_status)="Untagged" Or (ina_inventoryitems_u.iac_status)="Tagged" Or (ina_inventoryitems_u.iac_status)="Held"));


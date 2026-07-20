-- Query: ina_inventoryitems_u_chargedoff
-- Type: 0

SELECT ina_inventoryitems_u.*
FROM ina_inventoryitems_u
WHERE (((ina_inventoryitems_u.iac_status)="Issued to User" Or (ina_inventoryitems_u.iac_status)="Installed" Or (ina_inventoryitems_u.iac_status)="Consumed" Or (ina_inventoryitems_u.iac_status)="Written Off"));


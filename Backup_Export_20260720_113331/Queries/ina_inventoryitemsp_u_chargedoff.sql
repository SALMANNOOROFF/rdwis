-- Query: ina_inventoryitemsp_u_chargedoff
-- Type: 0

SELECT ina_inventoryitemsp_u.*
FROM ina_inventoryitemsp_u
WHERE ((([ina_inventoryitemsp_u].[iac_status])="Issued to User" Or ([ina_inventoryitemsp_u].[iac_status])="Installed" Or ([ina_inventoryitemsp_u].[iac_status])="Consumed" Or ([ina_inventoryitemsp_u].[iac_status])="Written Off"));


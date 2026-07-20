-- Query: fin_sto_cf_received1
-- Type: 0

SELECT fin_sharesinstall.shi_cf AS amount
FROM fin_sharesinstall
WHERE (((fin_sharesinstall.shi_hed_id)=[Forms]![vars]![Parameter1]));


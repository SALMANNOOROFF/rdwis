-- Query: fin_sto_pcc_received1
-- Type: 0

SELECT fin_sharesinstall.shi_pcc AS amount
FROM fin_sharesinstall
WHERE (((fin_sharesinstall.shi_hed_id)=[Forms]![vars]![Parameter1]));


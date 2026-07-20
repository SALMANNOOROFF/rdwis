-- Query: aud_chk_items_invats_missing1
-- Type: 0

SELECT ina_invats.ias_pci_id, Sum(ina_invats.ias_qty) AS sum_ias_qty, Sum(ina_invatcomps.iac_qty) AS sum_iac_qty
FROM ina_invats INNER JOIN ina_invatcomps ON ina_invats.ias_id = ina_invatcomps.iac_ias_id
GROUP BY ina_invats.ias_pci_id;


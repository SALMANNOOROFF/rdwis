-- Query: aud_chk_receipts_double3
-- Type: 0

SELECT aud_chk_receipts_double1.prt_pcs_id, Count(pur_purcaseitems.pci_id) AS pcase_items
FROM aud_chk_receipts_double1 INNER JOIN pur_purcaseitems ON aud_chk_receipts_double1.prt_pcs_id = pur_purcaseitems.pci_pcs_id
GROUP BY aud_chk_receipts_double1.prt_pcs_id;


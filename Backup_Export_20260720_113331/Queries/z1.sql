-- Query: z1
-- Type: 0

SELECT pur_purreceipts.prt_id, pur_purreceipts.prt_pcs_id, Sum([pti_qty]*[pci_price]) AS [sum]
FROM (pur_purreceipts INNER JOIN pur_purreceiptitems ON pur_purreceipts.prt_id = pur_purreceiptitems.pti_prt_id) INNER JOIN pur_purcaseitems ON pur_purreceiptitems.pti_pci_id = pur_purcaseitems.pci_id
GROUP BY pur_purreceipts.prt_id, pur_purreceipts.prt_pcs_id
ORDER BY pur_purreceipts.prt_id DESC;


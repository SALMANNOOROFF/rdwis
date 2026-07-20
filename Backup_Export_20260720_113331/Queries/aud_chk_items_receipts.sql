-- Query: aud_chk_items_receipts
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_date, pur_purcases.pcs_effhed_id, pur_purcases.pcs_status, pur_purcaseitems.pci_qty, pur_purcaseitems.pci_id, Nz([pci_fulfilment],0) AS ful_qty, aud_chk_items_receipts1.sum_pti_qty, aud_chk_items_receipts1.count_pti_id, IIf(Nz([pci_fulfilment],0)*[count_pti_id]=[sum_pti_qty],"OK","Oooo") AS [check]
FROM (pur_purcases INNER JOIN pur_purcaseitems ON pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id) LEFT JOIN aud_chk_items_receipts1 ON pur_purcaseitems.pci_id = aud_chk_items_receipts1.pti_pci_id
WHERE (((pur_purcases.pcs_status)="Fulfilled") AND ((Nz([pci_fulfilment],0))<>[pci_qty])) OR (((pur_purcases.pcs_status)<>"Cancelled" And (pur_purcases.pcs_status)<>"Draft") AND ((Nz([pci_fulfilment],0))<>[sum_pti_qty]))
ORDER BY pur_purcases.pcs_status;


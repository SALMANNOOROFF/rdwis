-- Query: aud_chk_items_invats_missing
-- Type: 0

SELECT pur_purcases.pcs_id, cen_heads.hed_code, pur_purcases.pcs_status, pur_purcaseitems.pci_id, pur_purcaseitems.pci_desc, pur_purcaseitems.pci_type, pur_purcaseitems.pci_estprice, pur_purcaseitems.pci_qty, Nz([pci_fulfilment],0) AS ful_qty, aud_chk_items_invats_missing1.ias_pci_id, aud_chk_items_invats_missing1.sum_ias_qty, aud_chk_items_invats_missing1.sum_iac_qty
FROM ((pur_purcaseitems INNER JOIN pur_purcases ON pur_purcaseitems.pci_pcs_id = pur_purcases.pcs_id) LEFT JOIN aud_chk_items_invats_missing1 ON pur_purcaseitems.pci_id = aud_chk_items_invats_missing1.ias_pci_id) INNER JOIN cen_heads ON pur_purcases.pcs_effhed_id = cen_heads.hed_id
WHERE (((pur_purcases.pcs_status)<>"Cancelled" And (pur_purcases.pcs_status)<>"Draft") AND ((pur_purcaseitems.pci_type)<>3) AND ((pur_purcaseitems.pci_estprice)<>0 Or (pur_purcaseitems.pci_estprice) Is Null) AND ((Nz([pci_fulfilment],0))<>[sum_ias_qty])) OR (((pur_purcases.pcs_status)<>"Cancelled" And (pur_purcases.pcs_status)<>"Draft") AND ((pur_purcaseitems.pci_type)<>3) AND ((pur_purcaseitems.pci_estprice)<>0 Or (pur_purcaseitems.pci_estprice) Is Null) AND ((Nz([pci_fulfilment],0))>0) AND ((aud_chk_items_invats_missing1.ias_pci_id) Is Null)) OR (((aud_chk_items_invats_missing1.sum_iac_qty)<>[sum_ias_qty]))
ORDER BY pur_purcases.pcs_id, pur_purcaseitems.pci_id;


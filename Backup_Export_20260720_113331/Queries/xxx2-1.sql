-- Query: xxx2-1
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcaseitems.pci_desc, [pci_qty]*[pci_price] AS price, cen_heads.hed_code, pur_purcases.pcs_sudohed, IIf(IsNull([hed_code]),"Center/CSRF",[hed_code]) AS project, pur_purcaseitems.pci_subtype, pur_purcaseitems.pci_subhead, pur_purcases.pcs_status
FROM (pur_purcases LEFT JOIN cen_heads ON pur_purcases.pcs_hed_id = cen_heads.hed_id) INNER JOIN pur_purcaseitems ON pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id
WHERE (((pur_purcases.pcs_status) Like "*Fulfilled"));


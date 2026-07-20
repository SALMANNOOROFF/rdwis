-- Query: aud_chk_purcases_falselyfulfilled
-- Type: 0

SELECT pur_purcases.pcs_id, pur_purcases.pcs_status
FROM pur_purcases
WHERE (((pur_purcases.pcs_status)="Fulfilled") AND ((Exists (Select pci_id From pur_purcaseitems Where pci_pcs_id = pur_purcases.pcs_id And  pci_qty <> Nz([pci_fulfilment],0)))=True));


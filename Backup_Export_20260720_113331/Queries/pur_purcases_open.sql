-- Query: pur_purcases_open
-- Type: 0

SELECT pur_purcases.*
FROM pur_purcases
WHERE (((pur_purcases.pcs_status)="Under Scrutiny" Or (pur_purcases.pcs_status)="Under Approval" Or (pur_purcases.pcs_status)="Approved" Or (pur_purcases.pcs_status)="Under Revision"));


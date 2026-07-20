-- Query: fin_warnunverifiedcontracts
-- Type: 0

SELECT fin_contractsverif.cvf_verif
FROM fin_contractsverif INNER JOIN hr_contracts_u_last_active ON fin_contractsverif.cvf_ctr_id = hr_contracts_u_last_active.ctr_id
WHERE (((fin_contractsverif.cvf_verif)="False"));


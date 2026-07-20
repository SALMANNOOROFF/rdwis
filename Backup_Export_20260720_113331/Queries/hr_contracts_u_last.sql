-- Query: hr_contracts_u_last
-- Type: 0

SELECT hr_contracts.*, IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]) AS ctr_effenddt
FROM hr_contracts INNER JOIN hr_contracts_u_last1 ON (hr_contracts.ctr_date = hr_contracts_u_last1.MaxOfctr_date) AND (hr_contracts.ctr_num = hr_contracts_u_last1.ctr_num);


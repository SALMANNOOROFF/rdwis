-- Query: hr_contracts_relevant
-- Type: 0

SELECT hr_contracts.ctr_num, hr_contracts.ctr_date, hr_contracts.ctr_startdt, hr_contracts.ctr_enddt, IIf(IsNull([ctr_termindt]),[ctr_enddt],[ctr_termindt]) AS ctr_effenddt, Sgn(DateDiff("d",[StartDate],[ctr_effenddt]))*Sgn(DateDiff("d",[ctr_startdt],[EndDate])) AS relevance
FROM hr_contracts;


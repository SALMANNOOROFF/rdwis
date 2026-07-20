-- Query: hr_ctrcases_minutes
-- Type: 0

SELECT hr_ctrcases.*, hr_contracts.*, cen_units.unt_namesh AS unt_newnamesh, cen_units_1.unt_namesh, IIf([ctc_newctrtype]=1,"Full Time","Part Time") AS newctrtype, IIf([ctr_type]=1,"Full Time","Part Time") AS ctype
FROM ((hr_ctrcases LEFT JOIN hr_contracts ON hr_ctrcases.ctc_ctr_id = hr_contracts.ctr_id) INNER JOIN cen_units ON hr_ctrcases.ctc_newunt_id = cen_units.unt_id) LEFT JOIN cen_units AS cen_units_1 ON hr_contracts.ctr_unt_id = cen_units_1.unt_id
WHERE (((hr_ctrcases.ctc_id) In (47)));


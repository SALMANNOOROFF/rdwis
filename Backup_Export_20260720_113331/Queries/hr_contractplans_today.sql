-- Query: hr_contractplans_today
-- Type: 0

SELECT hr_contractplans.cpn_id, hr_contractplans.cpn_ctr_id, hr_contractplans.cpn_startdt, hr_contractplans.cpn_enddt, hr_contractplans.cpn_hed_id
FROM hr_contractplans
WHERE (((hr_contractplans.cpn_startdt)<=Getnow()) AND ((hr_contractplans.cpn_enddt)>=Getnow()));


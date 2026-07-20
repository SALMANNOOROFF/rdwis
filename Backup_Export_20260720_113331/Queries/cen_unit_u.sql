-- Query: cen_unit_u
-- Type: 0

SELECT cen_units.unt_id, cen_units.unt_leadname, cen_units.unt_leadtitle, cen_units.unt_leadrank, cen_units.unt_leaddesig, cen_units.unt_leaddesigshort
FROM cen_units
WHERE (((cen_units.unt_id)>=getvar("varLower") And (cen_units.unt_id)<=getvar("varUpper")));


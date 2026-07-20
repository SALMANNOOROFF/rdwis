-- Query: hr_attachsummary4
-- Type: 0

SELECT hr_emps.emp_id, "Active" AS [Currency], "photo" AS eat_type, IIf(IsNull([emp_photodest]),"Missing","Attached") AS Status
FROM hr_emps
WHERE (((hr_emps.emp_status) Like "Active*"));


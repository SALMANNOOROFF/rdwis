-- Query: hr_attachsummary2
-- Type: 0

SELECT hr_emps.emp_id, IIf([emp_joindt]<#1/1/2022#,"Old","Recent") AS [Currency], hr_empattachments.eat_type, IIf(IsNull([eat_path]),"Missing","Attached") AS Status
FROM hr_empattachments INNER JOIN hr_emps ON hr_empattachments.eat_objid = hr_emps.emp_id
WHERE (((hr_empattachments.eat_type)="Notice") AND ((hr_emps.emp_status)="Released"));


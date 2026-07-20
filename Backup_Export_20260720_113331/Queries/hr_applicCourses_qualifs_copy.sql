-- Query: hr_applicCourses_qualifs_copy
-- Type: 64

INSERT INTO hr_qualifs ( qlf_emp_id, qlf_level, qlf_name, qlf_inst, qlf_duration, qlf_unit, qlf_enddt, qlf_grade, qlf_spec, qlf_type, qlf_license )
SELECT [EmployeeId] AS Expr1, hr_applicqualifs.apq_level, hr_applicqualifs.apq_name, hr_applicqualifs.apq_inst, hr_applicqualifs.apq_duration, hr_applicqualifs.apq_unit, hr_applicqualifs.apq_enddt, hr_applicqualifs.apq_grade, hr_applicqualifs.apq_spec, hr_applicqualifs.apq_type, hr_applicqualifs.apq_license
FROM hr_applicqualifs
WHERE (((hr_applicqualifs.apq_type)="Course") AND ((hr_applicqualifs.apq_id)=[ApplicId]));


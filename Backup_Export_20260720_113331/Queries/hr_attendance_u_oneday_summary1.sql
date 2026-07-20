-- Query: hr_attendance_u_oneday_summary1
-- Type: 0

SELECT hr_attendance_u_oneday_temp.*, IIf([attendance]="Present","Present","Away") AS attend_group
FROM hr_attendance_u_oneday_temp;


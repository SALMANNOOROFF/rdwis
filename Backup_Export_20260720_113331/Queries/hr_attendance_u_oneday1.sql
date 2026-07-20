-- Query: hr_attendance_u_oneday1
-- Type: 0

SELECT hr_attendanceremarks.*
FROM hr_attendanceremarks
WHERE (((hr_attendanceremarks.atr_attday)=[Forms]![vars]![Parameter2] Or (hr_attendanceremarks.atr_attday) Is Null));


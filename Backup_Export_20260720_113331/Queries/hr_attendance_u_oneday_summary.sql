-- Query: hr_attendance_u_oneday_summary
-- Type: 16

TRANSFORM Count(hr_attendance_u_oneday_summary1.attendance) AS attend_count
SELECT hr_attendance_u_oneday_summary1.attend_date, hr_attendance_u_oneday_summary1.attend_group
FROM hr_attendance_u_oneday_summary1
GROUP BY hr_attendance_u_oneday_summary1.attend_date, hr_attendance_u_oneday_summary1.attend_group
ORDER BY hr_attendance_u_oneday_summary1.attend_group DESC 
PIVOT hr_attendance_u_oneday_summary1.unt_namesh In ("Comm","Enab","NWS","Sensors","Sys","SoSE","HR","Admin","IS");


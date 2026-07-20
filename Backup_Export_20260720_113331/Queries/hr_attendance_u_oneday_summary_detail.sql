-- Query: hr_attendance_u_oneday_summary_detail
-- Type: 16

TRANSFORM Count(hr_attendance_u_oneday_summary1.attendance) AS attend_count
SELECT hr_attendance_u_oneday_summary1.attend_date, hr_attendance_u_oneday_summary1.attendance, hr_attendance_u_oneday_summary1.attend_sort
FROM hr_attendance_u_oneday_summary1
WHERE (((hr_attendance_u_oneday_summary1.attendance)<>"Present"))
GROUP BY hr_attendance_u_oneday_summary1.attend_date, hr_attendance_u_oneday_summary1.attendance, hr_attendance_u_oneday_summary1.attend_sort
ORDER BY hr_attendance_u_oneday_summary1.attend_sort
PIVOT hr_attendance_u_oneday_summary1.unt_namesh In ("Comm","Enab","NWS","Sensors","Sys","SoSE","HR","Admin","IS");


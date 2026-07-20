-- Query: hr_attendance_u
-- Type: 0

SELECT hr_attendance.*
FROM hr_attendance
WHERE (((hr_attendance.att_unt_id)>=getvar("varLower") And (hr_attendance.att_unt_id)<=getvar("varUpper")))
ORDER BY hr_attendance.att_unt_id, hr_attendance.att_emp_id;


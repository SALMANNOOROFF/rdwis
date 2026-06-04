VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_attendance_summary_monthly"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdCalMonth_Click()
Dim dbsAttMon As Database
Dim rstAttMon As Recordset
Dim dcnAttMon As Variant
Dim dtStart As Date
Dim dtEnd As Date

Me.Dirty = False
Set dbsAttMon = CurrentDb()

dtStart = FirstDateThisMonth(Me!enddt)
dtEnd = LastDateThisMonth(Me!enddt)
dbsAttMon.Execute "Delete From hr_attendance_summary_temp"
Set rstAttMon = Forms!hr_attendance_u.RecordsetClone
rstAttMon.MoveFirst
Do While Not rstAttMon.EOF
    Set dcnAttMon = EmpAttendance(rstAttMon!att_emp_id, dtStart, dtEnd)
    DictToTable dcnAttMon, "hr_attendance_summary_temp"
    rstAttMon.MoveNext
    Loop
Me.Requery
Me.cmdCalMonth.Visible = False

End Sub

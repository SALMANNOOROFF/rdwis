VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_attendance_u_oneday_summary"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdChangeDate_Click()
DoCmd.OpenForm "date_picker", acNormal, "", "", , acNormal, "change_att_date~" & Me!attend_date
End Sub

Private Sub Report_Open(Cancel As Integer)
If getVar("varRoleLevel") >= 29 Then Me.cmdChangeDate.Visible = True
End Sub

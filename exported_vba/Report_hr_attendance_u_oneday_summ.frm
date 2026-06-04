VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_attendance_u_oneday_summ"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
Dim strDept As String
strDept = getVar("varUnitNameShort")
Me.lbl.Caption = strDept
Me.dept.ControlSource = strDept
Me.dept_sum.ControlSource = "=Sum(Nz([" & strDept & "],""0""))"
End Sub


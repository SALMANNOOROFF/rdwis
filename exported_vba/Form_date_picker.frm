VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_date_picker"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
arrArgs = Split(Me.OpenArgs, "~")
Me.txtContext = arrArgs(0)
Me.txtdate = arrArgs(1)
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub cmdOK_Click()

If Nz(Me.txtdate, "") = "" Or Not IsDate(Me.txtdate) Then
    MsgBox "Please enter a valid date.", vbCritical
    Exit Sub
    End If

Select Case Me.txtContext
    Case "change_att_date"
        Dim dbs As Database
        Dim qdf As QueryDef
        Dim dtMaster As Date
        Dim strMode As String
        
        dtMaster = Me.txtdate
        SetOneDayAttendanceSql FirstDateThisMonth(dtMaster), Day(dtMaster)
        
        Set dbs = CurrentDb()
        dbs.Execute "Delete From hr_attendance_u_oneday_temp"
        Set qdf = dbs.QueryDefs("hr_attendance_u_oneday_adder")
        qdf.Parameters("[Forms]![vars]![Parameter1]") = Forms!vars.Parameter1
        qdf.Parameters("[Forms]![vars]![Parameter2]") = Forms!vars.Parameter2
        qdf.Execute
        
        Reports!hr_attendance_u_oneday_summary.Requery
        DoCmd.Close
    End Select
    
End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_attendance_u_oneday"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit
Dim dtgAttStop As Date
Dim dtTodayDate As Date

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim dtMaster As Date
Dim strMode As String
Dim dtgInitial As Date

'arrArgs = Split(Me.OpenArgs, "~")
'dtMaster = arrArgs(1)
'dtgInitial = GetNow()
'dtTodayDate = DateValue(dtgInitial)
'dtgAttStop = dtMaster & " " & getVarGlobal("att_cutoff_time")
'Me.txtClock = Format(TimeValue(dtgInitial), "HH:nn:ss")
'Me.lblTimeLimit.Caption = "Lock Time  " & Format(getVarGlobal("att_cutoff_time"), "HH:nn")
'Me.attendance_date = dtMaster
'
'If dtMaster = dtTodayDate Then
'    Me.txtClock.Visible = True
'    Me.lblTimeLimit.Visible = True
'    End If
'
'If arrArgs(2) = "locked" Or _
'   dtMaster < dtTodayDate Or _
'   CDate(dtTodayDate & " " & Me.txtClock) >= dtgAttStop Then
'    Me.att.Locked = True
'    Me.att.BorderStyle = 0
'    Me.atr_remarks.Locked = True
'    Me.atr_remarks.BorderStyle = 0
'    Me.cmdReverse.Visible = True
'    Else
'    strMode = getVar("varMode")
'    Select Case strMode
'        Case "approver-s"
'            Me.AllowEdits = True
'            Me.lblLocked.Visible = False
'        End Select
'    End If

'------- Alt Code ----------------
arrArgs = Split(Me.OpenArgs, "~")
dtMaster = arrArgs(1)
Me.attendance_date = dtMaster
If arrArgs(2) = "locked" Then
    Me.att.Locked = True
    Me.att.BorderStyle = 0
    Me.atr_remarks.Locked = True
    Me.atr_remarks.BorderStyle = 0
    Me.cmdReverse.Visible = True
    Else
    strMode = getVar("varMode")
    Select Case strMode
        Case "approver-s"
            Me.AllowEdits = True
            Me.lblLocked.Visible = False
        End Select
    End If
'---------------------------------

Me.Visible = True

End Sub

Private Sub Form_Timer()
'Me.txtClock = Format(TimeValue(Me.txtClock) + TimeValue("00:00:01"), "HH:nn:ss")
'If CDate(dtTodayDate & " " & Me.txtClock) >= dtgAttStop And Me.att.Locked = False Then
'    Me.Dirty = False
'    Me.att.Locked = True
'    Me.att.BorderStyle = 0
'    Me.lblLocked.Visible = True
'    Me.cmdReverse.Visible = True
'    End If
End Sub

Private Sub Form_Close()
Forms!hr_attendance_u.Refresh
End Sub

Private Sub atr_remarks_AfterUpdate()     'Default value property for atr_attday is not working
If IsNull(Me!atr_attday) Then Me!atr_attday = Day(Me.attendance_date)
Me.Requery
End Sub

Private Sub cmdReverse_Click()
Dim dbs As Database
Dim qdf As QueryDef
Dim rst As Recordset
Dim n As Integer

Set dbs = CurrentDb()
dbs.Execute "Delete From hr_attendance_u_oneday_temp"
Set qdf = dbs.QueryDefs("hr_attendance_u_oneday_adder")
qdf.Parameters("[Forms]![vars]![Parameter1]") = Forms!vars.Parameter1
qdf.Parameters("[Forms]![vars]![Parameter2]") = Forms!vars.Parameter2
qdf.Execute
qdf.Close

n = Day(Me.attend_date)
Set rst = dbs.OpenRecordset("hr_attendance_u_oneday_temp")
Do While Not rst.EOF
    rst.Edit
    rst("att_" & n) = rst!att
    rst("att_" & n & "_old") = rst!att
    rst("att_" & n & "_detail") = "c,att_" & n & ",Attendance,(emp_namecomp),1,varchar,i,i,,,Select * From hr_attendance Where att_id = P1,(att_id),,,,hr_attendance,att_id,(att_id),i,,"
    rst.Update
    rst.MoveNext
    Loop
rst.Close

DoCmd.OpenForm "hr_attendance_rev2", acNormal, "", "", , acHidden
End Sub

Private Sub cmdPrint_Click()
Dim dbs As Database
Dim qdf As QueryDef
Dim strMode As String

Set dbs = CurrentDb()
dbs.Execute "Delete From hr_attendance_u_oneday_temp"
Set qdf = dbs.QueryDefs("hr_attendance_u_oneday_adder")
qdf.Parameters("[Forms]![vars]![Parameter1]") = Forms!vars.Parameter1
qdf.Parameters("[Forms]![vars]![Parameter2]") = Forms!vars.Parameter2
qdf.Execute

strMode = getVar("varMode")
Select Case True
    Case strMode Like "*-s": DoCmd.OpenReport "hr_attendance_u_oneday_summ", acViewReport
    Case strMode Like "*-m": DoCmd.OpenReport "hr_attendance_u_oneday_summary", acViewReport
    End Select

End Sub

Private Sub att_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub


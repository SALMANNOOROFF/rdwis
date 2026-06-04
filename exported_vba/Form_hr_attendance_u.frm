VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_attendance_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim strMode As String
Dim arrArgs() As String
Dim dtMaster As Date
Dim dtTodayDt As Date

arrArgs = Split(Me.OpenArgs, "~")
dtTodayDt = GetNow()
dtMaster = FirstDateThisMonth(CDate(arrArgs(1)))
Me.RecordSource = "Select * From hr_attendance_u Where att_startdt =#" & FirstDateThisMonth(dtMaster) & "#"
Me.attendance_month = dtMaster
If Me.attendance_month = FirstDateThisMonth(dtTodayDt) Then Me.cmdNext.Enabled = False
If Me.attendance_month = #11/1/2020# Then Me.cmdPrev.Enabled = False

PrepareAttendanceSheet dtMaster

strMode = getVar("varMode")
Select Case strMode
    Case "approver-m"
        If dtMaster = FirstDateThisMonth(dtTodayDt) Then Me.cmdHoliday.Visible = True
    Case "approver-s"
        'Me.AllowEdits = True
        Me.cmbDates.Visible = True
        Me.cmbAtt.Visible = True
        Me.cmdExecute.Visible = True
        Me.cmdCalcSalaries.Visible = True
        Me.cmdReverse.Visible = True
    End Select
Me.Visible = True
End Sub

Private Sub PrepareAttendanceSheet(dtDate As Date)
Dim ctl As Control
Dim strCtlName As String
Dim intCtl As Integer
Dim dtLoop As Date
Dim dtStart As Date
Dim dtEnd As Date
Dim dtTodayDate As Date
Dim strDates As String
Dim intDay As Integer

If Me.Form.Recordset.RecordCount = 0 Then Exit Sub

'Lock and hide attendence boxes as required
intDay = AttMonthStartDay()
dtTodayDate = GetNow()
For Each ctl In Me.Controls
    strCtlName = ctl.Name
    If Not strCtlName Like "att_[0-9]*" Then GoTo Loop_End
    intCtl = Right(strCtlName, Len(strCtlName) - 4)
    dtLoop = DateSerial(Year(dtDate), Month(dtDate), intCtl)
    If Me!att_startdt = #11/1/2020# Then Me.att_23.SetFocus
    
    If intCtl < intDay And Me!att_locked1 = 1 Then   'Lock and mark finalized records
        ctl.Locked = True
        ctl.BorderStyle = 0
        End If
    If intCtl >= intDay And Me!att_locked2 = 1 Then
        ctl.Locked = True
        ctl.BorderStyle = 0
        End If
    If ctl.Value = "X" Then                          'Lock and hide invalid dates
        ctl.Locked = True
        ctl.Visible = False
        Me("cmd" & intCtl).Visible = False
        End If
    If ctl.Value = "Z" Then                          'Lock and mark weekends
        ctl.Locked = True
        ctl.BackColor = 10921638
        ctl.ForeColor = 10921638
        End If
    If dtLoop > dtTodayDate Then                            'Lock and mark future dates
        ctl.Locked = True
        ctl.BorderStyle = 0
        End If
Loop_End:
    Next ctl

If Me!att_locked2 = 1 Then att_eahreplace.Locked = True

'Populate ComboBox
dtStart = Me!att_startdt
dtEnd = Me!att_enddt
strDates = "Month;"
For dtLoop = dtStart To dtEnd
    If dtLoop > dtTodayDate Then Exit For
    strCtlName = "att_" & Format(dtLoop, "d")
    If Me(strCtlName).Locked = False Then
        strDates = strDates & Format(dtLoop, "dd mmm yy") & ";"
        End If
    Next dtLoop
Me.cmbDates.RowSource = strDates

End Sub

Private Sub cmdPrev_Click()
Dim dtDate As Date
dtDate = DateAdd("m", -1, Me.attendance_month)
DoCmd.Echo False
DoCmd.Close
DoCmd.OpenForm "hr_attendance_u", acNormal, , , , acHidden, "~" & dtDate
DoCmd.Echo True
If Forms!hr_attendance_u.cmdPrev.Enabled = True Then    'To prevent strange behaviour of vanishing form close button
    Forms!hr_attendance_u.cmdPrev.SetFocus
    Else
    Forms!hr_attendance_u.cmdNext.SetFocus
    End If
End Sub

Private Sub cmdNext_Click()
Dim dtDate As Date
dtDate = DateAdd("m", 1, Me.attendance_month)
DoCmd.Echo False
DoCmd.Close
DoCmd.OpenForm "hr_attendance_u", acNormal, , , , acHidden, "~" & dtDate
DoCmd.Echo True
If Forms!hr_attendance_u.cmdNext.Enabled = True Then    'To prevent strange behaviour of vanishing form close button
    Forms!hr_attendance_u.cmdNext.SetFocus
    Else
    Forms!hr_attendance_u.cmdPrev.SetFocus
    End If
End Sub

Private Sub cmdReverse_Click()
DoCmd.OpenForm "hr_attendance_rev", acNormal, "", "", , acHidden
End Sub

Private Sub cmdHoliday_Click()
Dim rstHol As Recordset
Dim strHName As String
Dim intHLoop  As Integer
Dim dtDate As Date
Dim varNew As Variant

strHName = InputBox("Please enter the day of this month to be marked as holiday. Attendance of all employees will be changed for the day.", "Enter date")
If strHName = "" Then Exit Sub
If CInt(strHName) < Day(Me!att_startdt) Or _
   CInt(strHName) > Day(Me!att_enddt) Then
    MsgBox "Entered date is not valid."
    Exit Sub
    End If
strHName = "att_" & strHName
'If rstHol(strHName) = "X" Then
'    MsgBox "Entered date is not valid."
'    Exit Sub
'    End If

Set rstHol = Me.RecordsetClone
rstHol.MoveFirst
varNew = IIf(Nz(rstHol(strHName), "") <> "Z", "Z", Null)
DoCmd.Echo False
Do While Not rstHol.EOF
    rstHol.Edit
    rstHol(strHName) = varNew
    rstHol.Update
    rstHol.MoveNext
    Loop
DoCmd.Close
DoCmd.OpenForm "hr_attendance_u", acNormal, "", "", , , "~" & Date
DoCmd.Echo True
End Sub

Private Sub cmdExecute_Click()
Dim rstExe As Recordset
Dim strName As String
Dim intLoop  As Integer

If IsNull(Me.cmbDates) Then
    MsgBox "Please select a date first.", vbCritical
    Exit Sub
    End If

Set rstExe = Me.RecordsetClone
DoCmd.Echo False
Select Case Me.cmbDates
    Case "Month"
    'For selection 'Month'
    For intLoop = 1 To Day(Me!att_enddt)
        strName = "att_" & intLoop
        If Me(strName).Locked = True Then GoTo ForNext_Next
        rstExe.MoveFirst
        Do While Not rstExe.EOF
            rstExe.Edit
            rstExe(strName) = Me.cmbAtt.Value
            rstExe.Update
            rstExe.MoveNext
            Loop
ForNext_Next:
        Next
    'For selection other than 'Month'
    Case Else
    strName = "att_" & Format(Me.cmbDates.Value, "d")
    If Me(strName).Locked = True Then Exit Sub
    rstExe.MoveFirst
    Do While Not rstExe.EOF
        rstExe.Edit
        rstExe(strName) = Me.cmbAtt.Value
        rstExe.Update
        rstExe.MoveNext
        Loop
    End Select
DoCmd.Echo True
End Sub

Private Sub cmdCalcSalaries_Click()
Dim SalariesCalculated As Integer
Me.Dirty = False
DoCmd.Hourglass True
SalariesCalculated = CalcSalReqs()
DoCmd.Hourglass False
If SalariesCalculated > 0 Then
    DoCmd.OpenForm "hr_salreqs_temp", , "", "", , acWindowNormal
    Else
    MsgBox "No salaries calculated.", vbCritical
    End If
End Sub

Private Sub cmdPrint_Click()
Dim dbsAttMon As Database
Dim rstAttMon As Recordset
Dim dcnAttMon As Variant
Dim dtStart As Date
Dim dtEnd As Date

Me.Dirty = False
Set dbsAttMon = CurrentDb()

dtStart = AttMonthStartDate(Me.attendance_month)
dtEnd = AttMonthEndDate(Me.attendance_month)
dbsAttMon.Execute "Delete From hr_attendance_summary_temp"
Set rstAttMon = Me.RecordsetClone
rstAttMon.MoveFirst
Do While Not rstAttMon.EOF
    Set dcnAttMon = EmpAttendance(rstAttMon!att_emp_id, dtStart, dtEnd)
    DictToTable dcnAttMon, "hr_attendance_summary_temp"
    rstAttMon.MoveNext
    Loop

DoCmd.OpenReport "hr_attendance_summary_monthly", acViewReport
End Sub

Private Sub att_1_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_2_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_3_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_4_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_5_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_6_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_7_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_8_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_9_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_10_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_11_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_12_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_13_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_14_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_15_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_16_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_17_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_18_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_19_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_20_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_21_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_22_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_23_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_24_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_25_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_26_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_27_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_28_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_29_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_30_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_31_KeyDown(KeyCode As Integer, Shift As Integer)
KeyCode = KeyDownWork(KeyCode)
End Sub

Private Sub att_1_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_2_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_3_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_4_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_5_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_6_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_7_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_8_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_9_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_10_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_11_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_12_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_13_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_14_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_15_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_16_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_17_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_18_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_19_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_20_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_21_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_22_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_23_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_24_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_25_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_26_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_27_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_28_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_29_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_30_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_31_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub OpenOneDayAtt()
Dim dtAttDate As Date
Dim strAttDate As String
Dim strAttlock As String
Dim intAttDay As Integer

intAttDay = Right(Me.ActiveControl.Name, Len(Me.ActiveControl.Name) - 3)
If Nz(Me.Controls("att_" & intAttDay), "") = "Z" Then
    MsgBox "This is a public holiday.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.Controls("att_" & intAttDay), "") = "X" Then
    MsgBox "This day is not valid for this month.", vbCritical
    Exit Sub
    End If
   
dtAttDate = SetOneDayAttendanceSql(Me!att_startdt, intAttDay)
If dtAttDate > DateValue(GetNow()) Then
    MsgBox "Future attendence cannot be set.", vbCritical
    Exit Sub
    End If
    
strAttDate = CStr(Format(dtAttDate, "dd-mmm-yy"))
strAttlock = IIf(Me.Controls("att_" & intAttDay).Locked = True, "locked", "unlocked")
DoCmd.OpenForm "hr_attendance_u_oneday", acNormal, "", "", , acHidden, "~" & strAttDate & "~" & strAttlock
End Sub

Private Sub cmd1_Click()
OpenOneDayAtt
End Sub
Private Sub cmd2_Click()
OpenOneDayAtt
End Sub
Private Sub cmd3_Click()
OpenOneDayAtt
End Sub
Private Sub cmd4_Click()
OpenOneDayAtt
End Sub
Private Sub cmd5_Click()
OpenOneDayAtt
End Sub
Private Sub cmd6_Click()
OpenOneDayAtt
End Sub
Private Sub cmd7_Click()
OpenOneDayAtt
End Sub
Private Sub cmd8_Click()
OpenOneDayAtt
End Sub
Private Sub cmd9_Click()
OpenOneDayAtt
End Sub
Private Sub cmd10_Click()
OpenOneDayAtt
End Sub
Private Sub cmd11_Click()
OpenOneDayAtt
End Sub
Private Sub cmd12_Click()
OpenOneDayAtt
End Sub
Private Sub cmd13_Click()
OpenOneDayAtt
End Sub
Private Sub cmd14_Click()
OpenOneDayAtt
End Sub
Private Sub cmd15_Click()
OpenOneDayAtt
End Sub
Private Sub cmd16_Click()
OpenOneDayAtt
End Sub
Private Sub cmd17_Click()
OpenOneDayAtt
End Sub
Private Sub cmd18_Click()
OpenOneDayAtt
End Sub
Private Sub cmd19_Click()
OpenOneDayAtt
End Sub
Private Sub cmd20_Click()
OpenOneDayAtt
End Sub
Private Sub cmd21_Click()
OpenOneDayAtt
End Sub
Private Sub cmd22_Click()
OpenOneDayAtt
End Sub
Private Sub cmd23_Click()
OpenOneDayAtt
End Sub
Private Sub cmd24_Click()
OpenOneDayAtt
End Sub
Private Sub cmd25_Click()
OpenOneDayAtt
End Sub
Private Sub cmd26_Click()
OpenOneDayAtt
End Sub
Private Sub cmd27_Click()
OpenOneDayAtt
End Sub
Private Sub cmd28_Click()
OpenOneDayAtt
End Sub
Private Sub cmd29_Click()
OpenOneDayAtt
End Sub
Private Sub cmd30_Click()
OpenOneDayAtt
End Sub
Private Sub cmd31_Click()
OpenOneDayAtt
End Sub

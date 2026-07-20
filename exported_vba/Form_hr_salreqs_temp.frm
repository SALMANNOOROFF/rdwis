VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_salreqs_temp"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub selected_BeforeUpdate(Cancel As Integer)
If Me!reason <> "" Then
    Cancel = True
    Me.selected.Undo
    MsgBox "Salary requisition cannot be generated for this employee.", vbCritical
    End If
End Sub

Private Sub chkAll_AfterUpdate()
Dim rstAll As Recordset
Dim booVal As Boolean

Me.Dirty = False
booVal = Me.chkAll
Set rstAll = Me.Recordset
rstAll.MoveFirst
Do While Not rstAll.EOF
    If rstAll!reason = "" Then
        rstAll.Edit
        rstAll!selected = booVal
        rstAll.Update
        End If
    rstAll.MoveNext
    Loop
Me.Refresh
End Sub

Private Sub cmdGenSalReqs_Click()
Dim dtRef As Date
Dim intResponse As Integer
Dim dbsSal As Database
Dim qdfSal As QueryDef
Dim rstSal As Recordset
Dim d As Integer

'Exit if no record selected
Me.Dirty = False
d = 0
Set rstSal = Me.RecordsetClone
rstSal.MoveFirst
Do While Not rstSal.EOF
    If rstSal!selected = True Then
        d = d + 1
        End If
    rstSal.MoveNext
    Loop
rstSal.Close
If d = 0 Then
    MsgBox "No record selected.", vbCritical
    Exit Sub
    End If

'Exit if attendance is mandatory and it is incomplete
dtRef = Me!srq_month
d = AttMonthStartDay()
If CDate(getVarGlobal("att_disregard")) <> DateValue(GetNow()) Then
    intResponse = MsgBox("Attendance upto " & _
        Format(DateSerial(Year(dtRef), Month(dtRef), d - 1), "dd mmm") & _
        " will be locked. Do you want to proceed?", vbYesNo)
    If intResponse = vbNo Then Exit Sub
    If LockAttendanceSheet(Me!srq_month) = False Then Exit Sub
    End If

'Generate salary requisitions
Set dbsSal = CurrentDb()
Set qdfSal = dbsSal.QueryDefs("hr_salreq_add")
Set rstSal = Me.RecordsetClone
rstSal.MoveFirst
Do While Not rstSal.EOF
    If rstSal!selected = 0 Or rstSal!reason <> "" Then GoTo Loop_End
    qdfSal.Parameters("EmployeeId") = rstSal!srq_emp_id
    qdfSal.Parameters("EmpNameComp") = rstSal!srq_empnamecomp
    qdfSal.Parameters("Month") = rstSal!srq_month
    qdfSal.Parameters("Contracts") = rstSal!srq_contracts
    qdfSal.Parameters("HeadId") = Nz(rstSal!srq_hed_id, "")
    qdfSal.Parameters("UnitId") = rstSal!srq_unt_id
    qdfSal.Parameters("EffHeadId") = rstSal!srq_effhed_id
    qdfSal.Parameters("EffUnitId") = rstSal!srq_effunt_id
    qdfSal.Parameters("Status") = "Draft"
    qdfSal.Parameters("ContractSalary") = rstSal!srq_ctrsalary
    qdfSal.Parameters("GroSalary") = rstSal!srq_grosalary
    qdfSal.Parameters("NetSalary") = rstSal!srq_netsalary
    qdfSal.Parameters("Salary") = rstSal!srq_salary
    qdfSal.Parameters("UnpaidWorkDays") = rstSal!srq_unpaiddays
    qdfSal.Parameters("PaidHolidays") = rstSal!srq_paidholidays
    qdfSal.Parameters("UnderWork") = rstSal!srq_underwork
    qdfSal.Parameters("OverWork") = rstSal!srq_overwork
    qdfSal.Parameters("Award") = rstSal!srq_award
    qdfSal.Parameters("Penalty") = rstSal!srq_penalty
    qdfSal.Parameters("Loaned") = rstSal!srq_loaned
    qdfSal.Parameters("Withheld") = rstSal!srq_withheld
    qdfSal.Parameters("Arrears") = rstSal!srq_arrears
    qdfSal.Parameters("Dues") = rstSal!srq_dues
    qdfSal.Parameters("AlreadyPaid") = rstSal!srq_paidalready
    qdfSal.Parameters("Remarks") = Nz(rstSal!srq_remarks, "")
    qdfSal.Parameters("BankAccDetail") = rstSal!srq_bnkaccdetail
    qdfSal.Parameters("BankAccTitle") = rstSal!srq_bnkacctitle
    qdfSal.Parameters("SudoHead") = rstSal!srq_sudohed
    qdfSal.Parameters("Parent") = ""
    qdfSal.Execute
Loop_End:
    rstSal.MoveNext
    Loop

DoCmd.OpenForm "hr_salreqs_u", , "", "", , acHidden, "Draft"
DoCmd.Close acForm, "hr_salreqs_temp"
DoCmd.Close acForm, "hr_attendance_u"
End Sub

Private Function LockAttendanceSheet(ForMonth As Date)
On Error GoTo Error_Handler
Dim dbsLock As Database
Dim rstLock As Recordset
Dim intLoop As Integer
Dim strStep As String
Dim strSql As String
Dim s As Integer

s = AttMonthStartDay()
Set dbsLock = CurrentDb()

'If all attendance is already locked, true and exit
Set rstLock = dbsLock.OpenRecordset("Select * From hr_attendance_u Where att_enddt <= #" & ForMonth & "# And (att_locked1 = '0' or att_locked2 = '0')")
If rstLock.EOF = True Then
    LockAttendanceSheet = True
    Exit Function
    End If

'If attendance is not complete, false and exit
rstLock.MoveFirst
Do While Not rstLock.EOF
    For intLoop = 1 To 31
        If IsNull(rstLock.Fields("att_" & intLoop)) And _
           DateSerial(Year(rstLock!att_startdt), Month(rstLock!att_startdt), intLoop) < DateSerial(Year(ForMonth), Month(ForMonth), s) Then
            MsgBox "Please complete attendance."
            LockAttendanceSheet = False
            Exit Function
            End If
        Next intLoop
        rstLock.MoveNext
        Loop

'Lock attendance
rstLock.MoveFirst
Do While Not rstLock.EOF
    rstLock.Edit
    rstLock!att_locked1 = "1"
    If rstLock!att_enddt <> ForMonth Then rstLock!att_locked2 = "1"
    rstLock.Update
    rstLock.MoveNext
    Loop
rstLock.Close

LockAttendanceSheet = True

Exit Function
Error_Handler:
MsgBox Error$
End Function

Private Sub srq_salary_DblClick(Cancel As Integer)
SalaryTest Me!srq_emp_id, Me!srq_month
DoCmd.OpenForm "salary_matrix_test", acNormal, "", "", , acNormal
End Sub

Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err

DoCmd.OpenReport "hr_salreqs_temp", acViewReport, "", "", acNormal

cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub




VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_salreq_split"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)

CurrentDb.Execute "Delete From hr_salreqs_temp"

Me.Salary = Forms!hr_salreqs_u!srq_salary
Me.effhed_id = Forms!hr_salreqs_u!srq_effhed_id

Me.Visible = True
End Sub

Private Sub Form_BeforeUpdate(Cancel As Integer)
If IsNull(Me.srq_salary) Or IsNull(Me.srq_effhed_id) Then
    Cancel = True
    MsgBox "Salary amount and head is mandatory."
    End If
End Sub

Private Sub Form_AfterUpdate()
Me.Recalc
Me.Salary = Me.salreq_salary - Nz(Me.sum_salary_partial, 0)
End Sub

Private Sub cmdDiscardnExit_Click()
Me.Undo
DoCmd.Close
End Sub

Private Sub cmdSavenExit_Click()
Dim wksSplit As Workspace
Dim dbsSplit As Database
Dim rstSplit As Recordset
Dim qdfSplit As QueryDef
Dim strCommit As String

'On Error GoTo Error_Part
Me.Dirty = False

Set wksSplit = DBEngine.Workspaces(0)
'wksSplit.BeginTrans
strCommit = "Begun"

'Change amount and add remarks in current requisition
Set dbsSplit = CurrentDb()
Set rstSplit = dbsSplit.OpenRecordset("Select * From hr_salreqs Where srq_id = " & Me.salreq_id)
rstSplit.Edit
rstSplit!srq_salary = Me.Salary
rstSplit!srq_remarks = rstSplit!srq_remarks & IIf(Nz(rstSplit!srq_remarks, "") = "", "", " ") & "Split Salary."
rstSplit!srq_parent = 0
rstSplit.Update
rstSplit.Close

'Add new requisitions
Set rstSplit = dbsSplit.OpenRecordset("hr_salreqs_temp")
rstSplit.MoveFirst
Set qdfSplit = dbsSplit.QueryDefs("hr_salreq_add")
Do While Not rstSplit.EOF
    qdfSplit.Parameters("EmployeeId") = Me.salreq_emp_id
    qdfSplit.Parameters("EmpNameComp") = Me.salreq_empnamecomp
    qdfSplit.Parameters("HeadId") = IIf(IsNull(Me.salreq_hed_id), "", rstSplit!srq_effhed_id)
    qdfSplit.Parameters("UnitId") = Me.salreq_unt_id
    qdfSplit.Parameters("EffHeadId") = rstSplit!srq_effhed_id
    qdfSplit.Parameters("EffUnitId") = UnitFromHead(rstSplit!srq_effhed_id)
    qdfSplit.Parameters("Month") = Me.salreq_month
    qdfSplit.Parameters("Status") = Me.salreq_status
    qdfSplit.Parameters("Contracts") = Me.salreq_contracts
    qdfSplit.Parameters("ContractSalary") = Me.salreq_ctrsalary
    qdfSplit.Parameters("GroSalary") = Me.salreq_grosalary
    qdfSplit.Parameters("NetSalary") = Me.salreq_netsalary
    qdfSplit.Parameters("Salary") = rstSplit!srq_salary
    qdfSplit.Parameters("BankAccDetail") = Me.salreq_bankaccdetail
    qdfSplit.Parameters("BankAccTitle") = Me.salreq_bankacctitle
    qdfSplit.Parameters("Remarks") = "Split Salary."
    qdfSplit.Parameters("UnpaidWorkDays") = Me.salreq_unpaiddays
    qdfSplit.Parameters("PaidHolidays") = Me.salreq_paidholidays
    qdfSplit.Parameters("OverWork") = Me.salreq_overwork
    qdfSplit.Parameters("UnderWork") = Me.salreq_underwork
    qdfSplit.Parameters("Award") = Me.salreq_award
    qdfSplit.Parameters("Penalty") = Me.salreq_penalty
    qdfSplit.Parameters("Loaned") = Me.salreq_loaned
    qdfSplit.Parameters("Withheld") = Me.salreq_withheld
    qdfSplit.Parameters("Arrears") = Me.salreq_arrears
    qdfSplit.Parameters("Dues") = Me.salreq_dues
    qdfSplit.Parameters("AlreadyPaid") = Me.salreq_paidalready
    qdfSplit.Parameters("SudoHead") = Me.salreq_sudohed
    qdfSplit.Parameters("Parent") = Me.salreq_id
    qdfSplit.Execute
    rstSplit.MoveNext
    Loop
'wksSplit.CommitTrans
strCommit = "Committed"
MsgBox "Salary splitted successfully.", vbInformation
Forms!hr_salreqs_u.Requery
DoCmd.Close


Exit Sub
Error_Part:
If strCommit = "Begun" Then
    'wksSplit.Rollback
    MsgBox "Salary requisition cannot be split." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub


Attribute VB_Name = "ContractCase"
Option Compare Database
Option Explicit

Public Function AddCtrCase(EmpId As String, CaseType As String)
Dim dbsAdd As Database
Dim rstAdd As Recordset
Dim rstLast As Recordset
Dim lngCtrCaseID As Long
Dim lngUnitId As Long
Dim dtStart As Date
Dim dtEnd As Date
Dim strEmpName As String

Set dbsAdd = CurrentDb()
Set rstAdd = dbsAdd.OpenRecordset("hr_ctrcases", dbOpenDynaset, dbSeeChanges)
Set rstLast = dbsAdd.OpenRecordset("Select * From hr_emps Where emp_id = '" & EmpId & "'", dbOpenSnapshot)
strEmpName = NameComplete(rstLast!emp_name, rstLast!emp_title, rstLast!emp_rank)
Set rstLast = dbsAdd.OpenRecordset("Select * From hr_contracts_last Where ctr_num = '" & EmpId & "'", dbOpenSnapshot)
With rstAdd
    .AddNew
    !ctc_date = DateValue(GetNow())
    !ctc_emp_id = rstLast!ctr_num
    !ctc_empnamecomp = strEmpName
    !ctc_unt_id = rstLast!ctr_unt_id
    !ctc_ctr_id = rstLast!ctr_id
    !ctc_type = CaseType
    !ctc_status = "Draft"
    !ctc_newunt_id = rstLast!ctr_unt_id
    !ctc_newstartdt = DateAdd("d", 1, IIf(IsNull(rstLast!ctr_termindt), rstLast!ctr_enddt, rstLast!ctr_termindt))
    Select Case CaseType
        Case "Cr"
            !ctc_newenddt = DateAdd("d", -1, DateAdd("yyyy", 1, DateAdd("d", 1, IIf(IsNull(rstLast!ctr_termindt), rstLast!ctr_enddt, rstLast!ctr_termindt))))
        Case "Ce"
            !ctc_newenddt = DateAdd("m", 1, IIf(IsNull(rstLast!ctr_termindt), rstLast!ctr_enddt, rstLast!ctr_termindt))
        End Select
    !ctc_newsalary = rstLast!ctr_salary
    !ctc_newjobtitle = rstLast!ctr_jobtitle
    !ctc_newgrade = rstLast!ctr_grade
    !ctc_newctrtype = rstLast!ctr_type
    !ctc_approvedunt_id = rstLast!ctr_unt_id
    !ctc_approvedstartdt = DateAdd("d", 1, IIf(IsNull(rstLast!ctr_termindt), rstLast!ctr_enddt, rstLast!ctr_termindt))
    !ctc_approvedenddt = DateAdd("d", -1, DateAdd("yyyy", 1, DateAdd("d", 1, IIf(IsNull(rstLast!ctr_termindt), rstLast!ctr_enddt, rstLast!ctr_termindt))))
    !ctc_approvedsalary = rstLast!ctr_salary
    !ctc_approvedjobtitle = rstLast!ctr_jobtitle
    !ctc_approvedgrade = rstLast!ctr_grade
    !ctc_approvedctrtype = rstLast!ctr_type
    .Update
    .Bookmark = .LastModified
    lngCtrCaseID = !ctc_id
    dtStart = !ctc_newstartdt
    dtEnd = !ctc_newenddt
    lngUnitId = !ctc_unt_id
    End With

GeneratePlan "CtrCase", dtStart, dtEnd, lngCtrCaseID    'Condition deleted --> If lngUnitId >= 200000 And lngUnitId < 800000 Then

rstAdd.Edit
rstAdd!ctc_price = CalculateCcPrice(rstAdd!ctc_id)
rstAdd.Update

AddCtrCase = lngCtrCaseID
End Function

Public Sub ReleaseCC()
Dim frmRel As Form
Dim intresponse  As Integer

Set frmRel = Screen.ActiveForm
If CCClearForRelease(frmRel!ctc_type) = False Then Exit Sub

intresponse = MsgBox("The case will be forwarded to HR Department. Are you sure you want to release this case?", vbExclamation + vbYesNo, "Release Confirmation")
If intresponse <> 6 Then Exit Sub

frmRel!ctc_status = "Under Approval"    '--"Under HR Scrutiny"
If frmRel!ctc_status = "Draft" Then frmRel!ctc_releasedtg = GetNow()
MsgBox "The contract case has been released", vbInformation
DoCmd.Close

End Sub

Public Sub ForwardCC()
Select Case Screen.ActiveForm!ctc_status
    Case "Under HR Scrutiny"
        Screen.ActiveForm!ctc_status = "Under Finance Scrutiny"
    Case "Under Finance Scrutiny"
        Screen.ActiveForm!ctc_status = "Under Approval"
    End Select
DoCmd.Close
End Sub

Public Sub ReturnCC()
Select Case Screen.ActiveForm!ctc_status
    Case "Under HR Scrutiny"
        Screen.ActiveForm!ctc_status = "Under Revision"
    Case "Under Finance Scrutiny"
        Screen.ActiveForm!ctc_status = "Under HR Scrutiny"
    Case "Under Approval"
        Screen.ActiveForm!ctc_status = "Under Revision"
    End Select
DoCmd.Close
End Sub

Public Sub CancelCC()
Dim frmPC As Form
Dim intresponse  As Integer

intresponse = MsgBox("This contract case will be cancelled. Do you want to proceed?", vbExclamation + vbYesNo, "Cancellation Confirmation")
If intresponse <> 6 Then Exit Sub

Set frmPC = Screen.ActiveForm
If frmPC!ctc_status <> "Draft" Then frmPC!ctc_closedtg = GetNow()
frmPC!ctc_status = "Cancelled"
MsgBox "The purchase case has been cancelled.", vbInformation
DoCmd.Close
End Sub

Public Sub RejectCC()
Dim frmPC As Form
Dim intresponse  As Integer

intresponse = MsgBox("This contract case will be marked as 'Not Approved' and closed. Do you want to proceed?", vbExclamation + vbYesNo, "Cancellation Confirmation")
If intresponse <> 6 Then Exit Sub

Screen.ActiveForm!ctc_status = "Not Approved"
Screen.ActiveForm!ctc_closedtg = GetNow()
DoCmd.Close
End Sub

Public Sub ApproveCC()
Dim frm As Form
Dim intresponse  As Integer

intresponse = MsgBox("This contract case will be marked as 'Approved' and locked. Do you want to proceed?", vbExclamation + vbYesNo, "Approval Confirmation")
If intresponse <> 6 Then Exit Sub

'Update status
Set frm = Screen.ActiveForm
frm!ctc_status = "Approved"

'Add attachment slots
CreateAttachmentSlot "ctc", frm!ctc_id, "Minute"
DoCmd.Close

End Sub

Public Sub FulfillCC()
Dim wksAppCC As Workspace
Dim frm As Form
Dim dbsAppCC As Database
Dim rstAppCC As Recordset
Dim rstSource As Recordset
Dim lngNewCtrId As Long
Dim strCommit As String
Dim intresponse  As Integer
On Error GoTo Error_Part

Set frm = Screen.ActiveForm
Set dbsAppCC = CurrentDb()

'Checks
If IsNull(frm!ctc_newsigndt) Then
    MsgBox "Please enter signing date of the new contract.", vbCritical
    frm.ctc_newsigndt.SetFocus
    Exit Sub
    End If

If frm!ctc_newstartdt <> DateAdd("d", 1, frm.subContract.Form!ctr_enddt) And Nz(frm!ctc_terminremarks, "") = "" Then
    MsgBox "Reason for early termination of last contract is required.", vbCritical
    Exit Sub
    End If

intresponse = MsgBox("The case will be closed and a new contract will be created. Do you want to proceed?", vbExclamation + vbYesNo, "Fulfilment Confirmation")
If intresponse <> 6 Then Exit Sub

'Start Transaction
Set wksAppCC = DBEngine.Workspaces(0)
wksAppCC.BeginTrans
strCommit = "Begun"

'If required, add remarks to old contract and trim old contract plan
If frm!ctc_approvedstartdt <> DateAdd("d", 1, frm.subContract.Form!ctr_enddt) Then
    Set rstAppCC = dbsAppCC.OpenRecordset("Select * from hr_contracts Where ctr_id = " & frm.subContract.Form!ctr_id)
    With rstAppCC
        .Edit
        !ctr_termindt = DateAdd("d", -1, frm!ctc_approvedstartdt)
        !ctr_remarks = !ctr_remarks & IIf(Nz(!ctr_remarks, "") = "", "", IIf(!ctr_remarks Like "*.", " ", ". ")) & frm!ctc_terminremarks
        .Update
        .Close
        End With
    If frm!ctc_approvedunt_id >= 200000 And frm!ctc_approvedunt_id < 800000 Then AdjustPlan "Contract", frm.subContract.Form!ctr_id
    End If

'Create new contract
Set rstAppCC = dbsAppCC.OpenRecordset("hr_contracts", dbOpenDynaset, dbSeeChanges)
With rstAppCC
    .AddNew
    !ctr_num = frm.subEmp.Form!emp_id
    !ctr_date = frm!ctc_newsigndt
    !ctr_unt_id = frm!ctc_approvedunt_id
    !ctr_startdt = frm!ctc_approvedstartdt
    !ctr_enddt = frm!ctc_approvedenddt
    !ctr_salary = frm!ctc_approvedsalary
    !ctr_jobtitle = frm!ctc_approvedjobtitle
    !ctr_grade = frm!ctc_approvedgrade
    !ctr_type = frm!ctc_approvedctrtype
    !ctr_ctc_id = frm!ctc_id
    .Update
    .Bookmark = .LastModified
    lngNewCtrId = !ctr_id
    .Close
    End With

'Add attachment slots
CreateAttachmentSlot "ctc", frm!ctc_id, "Minute"

'Create new contract plan if applicable
If frm!ctc_approvedunt_id >= 200000 And frm!ctc_approvedunt_id < 800000 Then
    Set rstSource = frm.subPlan.Form.RecordsetClone
    Set rstAppCC = dbsAppCC.OpenRecordset("hr_contractplans")
    rstSource.MoveFirst
    Do While Not rstSource.EOF
        rstAppCC.AddNew
        rstAppCC!cpn_ctr_id = lngNewCtrId
        rstAppCC!cpn_startdt = rstSource!ccp_startdt
        rstAppCC!cpn_enddt = rstSource!ccp_enddt
        If Not IsNull(rstAppCC!cpn_hed_id) Then rstAppCC!cpn_hed_id = rstSource!ccp_hed_id
        rstAppCC.Update
        rstSource.MoveNext
        Loop
End If

'Add new contract verification record
Set rstAppCC = dbsAppCC.OpenRecordset("fin_contractsverif", dbOpenDynaset)
With rstAppCC
    .AddNew
    !cvf_ctr_id = lngNewCtrId
    !cvf_verif = 0
    .Update
    .Close
    End With

'Update contract case status
frm!ctc_newctr_id = lngNewCtrId
frm!ctc_status = "Fulfilled"
frm!ctc_closedtg = GetNow()

'End transaction
wksAppCC.CommitTrans
strCommit = "Committed"
MsgBox "A new contract has been added and the contract case has been marked 'Fufilled'.", vbInformation
DoCmd.Close
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksAppCC.Rollback
    MsgBox "The contract case could not be fulfilled." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Public Sub FulfillCCExt()
Dim wksAppCC As Workspace
Dim frm As Form
Dim dbsAppCC As Database
Dim rstAppCC As Recordset
Dim rstSource As Recordset
Dim strCommit As String
Dim intresponse  As Integer
On Error GoTo Error_Part

Set frm = Screen.ActiveForm
Set dbsAppCC = CurrentDb()

'Checks
If Nz(frm!ctc_terminremarks, "") = "" Then
    MsgBox "Reason for contract extension is required.", vbCritical
    Exit Sub
    End If

intresponse = MsgBox("The case will closed and the contract will be extended. Do you want to proceed?", vbExclamation + vbYesNo, "Closure Confirmation")
If intresponse <> 6 Then Exit Sub

'Start Transaction
Set wksAppCC = DBEngine.Workspaces(0)
wksAppCC.BeginTrans
strCommit = "Begun"

'Add termin date & remarks to old contract, and modify old contract plan
Set rstAppCC = dbsAppCC.OpenRecordset("Select * from hr_contracts Where ctr_id = " & frm.subContract.Form!ctr_id)
With rstAppCC
    .Edit
    !ctr_termindt = frm!ctc_newenddt
    !ctr_remarks = !ctr_remarks & IIf(Nz(!ctr_remarks, "") = "", "", IIf(!ctr_remarks Like "*.", " ", ". ")) & frm!ctc_terminremarks
    .Update
    .Close
    End With
AdjustPlan "Contract", frm.subContract.Form!ctr_id
    
'Add attachment slots
CreateAttachmentSlot "ctc", frm!ctc_id, "Minute"

'Update contract case status
frm!ctc_status = "Fulfilled"
frm!ctc_closedtg = GetNow()

'End transaction
wksAppCC.CommitTrans
strCommit = "Committed"
MsgBox "The contract has been extended and the contract case has been marked 'Fulfilled'.", vbInformation
DoCmd.Close
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksAppCC.Rollback
    MsgBox "The contract case could not be fulfilled." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Public Sub FulfillCCHg()
Dim wksAppCC As Workspace
Dim frm As Form
Dim dbsAppCC As Database
Dim rstAppCC As Recordset
Dim rstSource As Recordset
Dim lngNewCtrId As Long
Dim strCommit As String
Dim intresponse  As Integer
On Error GoTo Error_Part

Set frm = Screen.ActiveForm
Set dbsAppCC = CurrentDb()

'Checks
If IsNull(frm!ctc_newsigndt) Then
    MsgBox "Please enter signing date of the new contract.", vbCritical
    frm.ctc_newsigndt.SetFocus
    Exit Sub
    End If

intresponse = MsgBox("The case will be closed and a new contract will be created. Do you want to proceed?", vbExclamation + vbYesNo, "Closure Confirmation")
If intresponse <> 6 Then Exit Sub

'Start Transaction
Set wksAppCC = DBEngine.Workspaces(0)
wksAppCC.BeginTrans
strCommit = "Begun"

'Create new contract
Set rstAppCC = dbsAppCC.OpenRecordset("hr_contracts", dbOpenDynaset, dbSeeChanges)
With rstAppCC
    .AddNew
    !ctr_num = frm!ctc_emp_id
    !ctr_date = frm!ctc_newsigndt
    !ctr_unt_id = frm!ctc_approvedunt_id
    !ctr_startdt = frm!ctc_approvedstartdt
    !ctr_enddt = frm!ctc_approvedenddt
    If Not IsNull(frm!ctc_approvedprob) Then !ctr_prob = frm!ctc_approvedprob
    If Not IsNull(frm!ctc_approvedprobsal) Then !ctr_probsal = frm!ctc_approvedprobsal
    !ctr_salary = frm!ctc_approvedsalary
    !ctr_jobtitle = frm!ctc_approvedjobtitle
    !ctr_grade = frm!ctc_approvedgrade
    !ctr_type = frm!ctc_approvedctrtype
    !ctr_ctc_id = frm!ctc_id
    .Update
    .Bookmark = .LastModified
    lngNewCtrId = !ctr_id
    .Close
    End With

'Create new contract plan if applicable
If frm!ctc_approvedunt_id >= 200000 And frm!ctc_approvedunt_id < 800000 Then
    Set rstSource = frm.subPlan.Form.RecordsetClone
    Set rstAppCC = dbsAppCC.OpenRecordset("hr_contractplans")
    rstSource.MoveFirst
    Do While Not rstSource.EOF
        rstAppCC.AddNew
        rstAppCC!cpn_ctr_id = lngNewCtrId
        rstAppCC!cpn_startdt = rstSource!ccp_startdt
        rstAppCC!cpn_enddt = rstSource!ccp_enddt
        If Not IsNull(rstSource!ccp_hed_id) Then rstAppCC!cpn_hed_id = rstSource!ccp_hed_id
        rstAppCC.Update
        rstSource.MoveNext
        Loop
End If

'Add new contract verification record
Set rstAppCC = dbsAppCC.OpenRecordset("fin_contractsverif", dbOpenDynaset)
With rstAppCC
    .AddNew
    !cvf_ctr_id = lngNewCtrId
    !cvf_verif = 0
    .Update
    .Close
    End With

'Update contract case status
frm!ctc_newctr_id = lngNewCtrId
frm!ctc_status = "Fulfilled"
frm!ctc_closedtg = GetNow()

'End transaction
wksAppCC.CommitTrans
strCommit = "Committed"
MsgBox "A new contract has been added and the contract case has been marked 'Fulfilled'.", vbInformation
DoCmd.Close
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksAppCC.Rollback
    MsgBox "The contract case could not be fulfilled." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Public Function CCClearForRelease(CaseType As String) As Boolean
Dim frm As Form
Dim dbsType As Database
Dim rstCheck  As Recordset
Dim booClear As Boolean

booClear = True
Set frm = Screen.ActiveForm

Select Case CaseType
    Case "Cr"
'        If frm!ctc_newstartdt <> DateAdd("d", 1, frm.subContract.Form!ctr_enddt) And Nz(frm!ctc_terminremarks, "") = "" Then
'            MsgBox "Reason for early termination of last contract is required.", vbCritical
'            booClear = False
'            GoTo The_End
'            End If
    Case "Ce"
'        If Nz(frm!ctc_terminremarks, "") = "" Then
'            MsgBox "Reason for contract extension is required.", vbCritical
'            booClear = False
'            GoTo The_End
'            End If
    End Select

The_End:
CCClearForRelease = booClear
End Function

Public Function GetCtrCaseSalary(CtrCaseID As Long, ForMonth As Date) As Long
Dim arrPrice As Variant

arrPrice = GetSalaryMatrix2(CtrCaseID, ForMonth)
GetCtrCaseSalary = Round(arrPrice(11, 20), 0)
End Function

Public Function CalculateCcPrice(CtrCaseIDs As String) As Long
Dim dbsPrice As Database
Dim rstPrice As Recordset
Dim strSql As String

Set dbsPrice = CurrentDb()
strSql = dbsPrice.QueryDefs("hr_ctrcaseplans_summary1").sql
dbsPrice.QueryDefs("hr_ctrcaseplans_summary1").sql = Left(strSql, InStr(strSql, "In (") + 3) & CtrCaseIDs & ")))"
Set rstPrice = dbsPrice.OpenRecordset("Select Sum (amount) As sum_amount From hr_ctrcaseplans_summary", dbOpenSnapshot)
If Not rstPrice.EOF Then CalculateCcPrice = Round(rstPrice!sum_amount, 0)
End Function

Sub zxzx()
Debug.Print GetCtrCaseSalary(11, #4/1/2025#)
End Sub

Function GetSalaryMatrix2(CtrCaseID As Long, SalMonth As Date, Optional Record As Boolean) As Variant()
On Error GoTo Error_Handler

Dim dbsSalMat As Database
Dim rstSalMat As Recordset
Dim dtFrom As Date, dtTo As Date
Dim strSql As String
Dim arrTemp(1 To 36, 20) As Variant
Dim arrSalMat(1 To 11, 20) As Variant
Dim n As Integer, m As Integer

'Establish basic dates
dtFrom = FirstDateThisMonth(SalMonth)
dtTo = LastDateThisMonth(SalMonth)

'Get relevant contracts
Set dbsSalMat = CurrentDb()
strSql = "SELECT * FROM hr_ctrcases WHERE ctc_id = " & CtrCaseID
Set rstSalMat = dbsSalMat.OpenRecordset(strSql, dbOpenSnapshot)
If rstSalMat.EOF = True Then GoTo Output

'Create temporary matrix ******************************************************************************

'Add types, contract ids, start dates, end dates, salaries, projects, heads (columns 0, 1, 2, 3, 4, 18, 19)
arrTemp(1, 0) = 3
arrTemp(1, 1) = rstSalMat!ctc_id
arrTemp(1, 2) = rstSalMat!ctc_newstartdt
arrTemp(1, 3) = rstSalMat!ctc_newenddt
arrTemp(1, 4) = rstSalMat!ctc_newsalary

'Merge additional periods
m = n - 1
rstSalMat.MoveFirst                                              'period 2 (probation)
Do While Not rstSalMat.EOF
        If Not IsNull(rstSalMat!ctc_newprob) And rstSalMat!ctc_newprobsal <> rstSalMat!ctc_newsalary Then
            SplitPeriod arrTemp, rstSalMat!ctc_newstartdt, DateAdd("m", rstSalMat!ctc_newprob, rstSalMat!ctc_newstartdt) - 1, m, 2, rstSalMat!ctc_newprobsal
            End If
    rstSalMat.MoveNext
    Loop
SplitPeriod arrTemp, dtFrom, DateAdd("d", -1, dtFrom), m, 1
SplitPeriod arrTemp, DateAdd("d", 1, dtTo), dtTo, m, 4

'Mark irrelevant periods
n = 1
Do While Not IsEmpty(arrTemp(n, 0))
    If Not (arrTemp(n, 2) >= dtFrom And arrTemp(n, 3) <= dtTo) Then arrTemp(n, 0) = 0
    n = n + 1
    Loop

'Create salary matrix ******************************************************************************

'Copy relevant periods to salary matrix
n = 1
m = 1

Do While Not IsEmpty(arrTemp(n, 0))
    If arrTemp(n, 0) <> 0 Then
        arrSalMat(m, 0) = arrTemp(n, 0)
        arrSalMat(m, 1) = arrTemp(n, 1)
        arrSalMat(m, 2) = arrTemp(n, 2)
        arrSalMat(m, 3) = arrTemp(n, 3)
        arrSalMat(m, 4) = arrTemp(n, 4)
        m = m + 1
        End If
    n = n + 1
    Loop

'Salaries (column 13)
For n = 1 To 7
    If IsEmpty(arrSalMat(n, 0)) Then Exit For
    arrSalMat(n, 13) = IIf(arrSalMat(n, 0) = 1, 0, arrSalMat(n, 4) / (DateDiff("d", dtFrom, dtTo) + 1) * (DateDiff("d", arrSalMat(n, 2), arrSalMat(n, 3)) + 1))
    Next n

    
'Summary (row 11)
For n = 1 To 10
    If IsEmpty(arrSalMat(n, 1)) Then Exit For
    arrSalMat(11, 0) = 9
    arrSalMat(11, 1) = IIf(arrSalMat(11, 1) Like "*" & arrSalMat(n, 1) & "*", arrSalMat(11, 1), arrSalMat(11, 1) & "," & arrSalMat(n, 1))
    If arrSalMat(11, 1) Like ",*" Then arrSalMat(11, 1) = Right(arrSalMat(11, 1), Len(arrSalMat(11, 1)) - 1)
    arrSalMat(11, 2) = IIf(IsEmpty(arrSalMat(11, 2)), arrSalMat(n, 2), IIf(arrSalMat(n, 2) < arrSalMat(11, 2), arrSalMat(n, 3), arrSalMat(11, 2)))
    arrSalMat(11, 3) = IIf(IsEmpty(arrSalMat(11, 3)), arrSalMat(n, 3), IIf(arrSalMat(n, 3) > arrSalMat(11, 3), arrSalMat(n, 3), arrSalMat(11, 3)))
    arrSalMat(11, 4) = arrSalMat(n, 4)
    arrSalMat(11, 13) = arrSalMat(11, 13) + arrSalMat(n, 13)
    Next n

'General Data (column 20)
arrSalMat(1, 20) = CtrCaseID
arrSalMat(2, 20) = Format(SalMonth, "mmm yy")
arrSalMat(3, 20) = n - 8
arrSalMat(11, 20) = arrSalMat(11, 13) - arrSalMat(11, 14) + arrSalMat(11, 15)

Output:
If Record = True Then ArrayToTable arrSalMat, "salary_matrix_test", True
GetSalaryMatrix2 = arrSalMat

Exit Function
Error_Handler:
MsgBox Err.Number & " - " & Err.Description
End Function

Function GetContractCaseProject(CtcId As Long) As Variant
Dim dbsPrj As Database
Dim rstPrj As Recordset

Set dbsPrj = CurrentDb()
Set rstPrj = dbsPrj.OpenRecordset("Select ccp_hed_id From hr_ctrcaseplans Where ccp_ctc_id = " & CtcId & " Order By ccp_startdt", dbOpenSnapshot)
GetContractCaseProject = rstPrj!ccp_hed_id
End Function

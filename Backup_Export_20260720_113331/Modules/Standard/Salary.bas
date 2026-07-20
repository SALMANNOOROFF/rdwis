Attribute VB_Name = "Salary"
Option Compare Database
Option Explicit

Sub ReleaseSalaryReqGroup(SrqId As Long)
On Error GoTo Error_Handler
Dim dbsRel As Database
Dim rstRel As Recordset

Set dbsRel = CurrentDb()
Set rstRel = dbsRel.OpenRecordset("Select * From hr_salreqs Where srq_id = " & SrqId & " Or srq_parent = " & SrqId)
Do While Not rstRel.EOF
    rstRel.Edit
    rstRel!srq_status = "In Process"
    rstRel!srq_releasedtg = GetNow()
    rstRel.Update
    rstRel.MoveNext
    Loop
rstRel.Close
Exit Sub
Error_Handler:
MsgBox "Salary requisition cannot be released.", vbCritical

End Sub

Sub AddSalaryOrderGroup(SrqId As Long)
Dim dbsAdd As Database
Dim wksAdd As Workspace
Dim rstAdd As Recordset
Dim rstRf As Recordset
Dim intTransType As Integer
Dim lngSorId As Long
Dim lngParentId As Long
Dim strSorType As String
Dim strCommit As String
On Error GoTo Error_Handler

Set wksAdd = DBEngine.Workspaces(0)
wksAdd.BeginTrans
strCommit = "Begun"

Set dbsAdd = CurrentDb()
Set rstRf = dbsAdd.OpenRecordset("Select * From hr_salreqs Where srq_id = " & SrqId & " Or srq_parent = " & SrqId & " Order By srq_parent")
Do While Not rstRf.EOF
    'Add salary order
    Set rstAdd = dbsAdd.OpenRecordset("Select hed_transtype From cen_heads where hed_id = " & rstRf!srq_effhed_id, dbOpenSnapshot)
    If rstAdd.EOF = True Then GoTo Error_Handler
    intTransType = rstAdd!hed_transtype
    rstAdd.Close
    Set rstAdd = dbsAdd.OpenRecordset("fin_salorders", dbOpenDynaset, dbSeeChanges)
    rstAdd.AddNew           'Adding null to fields executes the query but disables going to last modified record
    rstAdd!sor_srq_id = rstRf!srq_id
    rstAdd!sor_type = "Sa"
    rstAdd!sor_emp_id = rstRf!srq_emp_id
    rstAdd!sor_empnamecomp = rstRf!srq_empnamecomp
    If Not IsNull(rstRf!srq_hed_id) Then rstAdd!sor_hed_id = rstRf!srq_hed_id
    rstAdd!sor_unt_id = rstRf!srq_unt_id
    rstAdd!sor_effhed_id = rstRf!srq_effhed_id
    rstAdd!sor_effunt_id = rstRf!srq_effunt_id
    rstAdd!sor_month = rstRf!srq_month
    rstAdd!sor_ctrsalary = rstRf!srq_ctrsalary
    rstAdd!sor_netsalary = rstRf!srq_netsalary
    rstAdd!sor_salary = rstRf!srq_salary
    rstAdd!sor_bnkacctitle = rstRf!srq_bnkacctitle
    rstAdd!sor_bnkaccdetail = rstRf!srq_bnkaccdetail
    rstAdd!sor_contracts = rstRf!srq_contracts
    rstAdd!sor_status = "Draft"
    If Not IsNull(rstRf!srq_remarks) Then rstAdd!sor_remarks = rstRf!srq_remarks
    If Not IsNull(rstRf!srq_remarks2) Then rstAdd!sor_remarks2 = rstRf!srq_remarks2
    rstAdd!sor_transtype = intTransType
    rstAdd!sor_grosalary = rstRf!srq_grosalary
    rstAdd!sor_arrears = rstRf!srq_arrears
    rstAdd!sor_dues = rstRf!srq_dues
    rstAdd!sor_paidalready = rstRf!srq_paidalready
    rstAdd!sor_overwork = rstRf!srq_overwork
    rstAdd!sor_underwork = rstRf!srq_underwork
    rstAdd!sor_loaned = rstRf!srq_loaned
    rstAdd!sor_withheld = rstRf!srq_withheld
    rstAdd!sor_award = rstRf!srq_award
    rstAdd!sor_penalty = rstRf!srq_penalty
    If Not IsNull(rstRf!srq_sudohed) Then rstAdd!sor_sudohed = rstRf!srq_sudohed
    If Not IsNull(rstRf!srq_parent) Then rstAdd!sor_parent = IIf(rstRf!srq_parent > 0, lngParentId, rstRf!srq_parent)
    rstAdd!sor_noloan = IIf(IsNull(rstRf!srq_hed_id), 1, 0)
    rstAdd!sor_releasedtg = GetNow()
    rstAdd.Update
    rstAdd.Bookmark = rstAdd.LastModified
    lngSorId = rstAdd!sor_id
    strSorType = rstAdd!sor_type
    If rstRf!srq_parent = 0 Then lngParentId = rstAdd!sor_id
    rstAdd.Close
    
    'Add subheads ratios if not central employee
    If Not IsNull(rstRf!srq_hed_id) Then
        Set rstAdd = dbsAdd.OpenRecordset("fin_salorders_shd")
        With rstAdd
            .AddNew
            !sod_sor_id = lngSorId
            !sod_type = strSorType
            !sod_subhead = "HR"
            !sod_ratio = 1
            .Update
            End With
        End If
        
    'Set requisition fulfilment to 0 as a mark
    rstRf.Edit
    rstRf!srq_fulfilment = 0
    rstRf.Update
    
    rstRf.MoveNext
    Loop

wksAdd.CommitTrans
strCommit = "Committed"

rstRf.Close
Exit Sub
Error_Handler:
If strCommit = "Begun" Then
    wksAdd.Rollback
    MsgBox "Salary Order cannot be released." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox "Salary order cannot be generated.", vbCritical
    End If

End Sub

Sub CancelSalaryReqGroup(SrqId As Long)
On Error GoTo Error_Handler
Dim dbsCan As Database
Dim rstCan As Recordset

Set dbsCan = CurrentDb()
Set rstCan = dbsCan.OpenRecordset("Select * From hr_salreqs Where srq_id = " & SrqId & " Or srq_parent = " & SrqId)
Do While Not rstCan.EOF
    rstCan.Edit
    rstCan!srq_status = "Cancelled"
    rstCan!srq_closedtg = GetNow()
    rstCan.Update
    rstCan.MoveNext
    Loop
rstCan.Close
Exit Sub
Error_Handler:
MsgBox "Salary requisition cannot be cancelled.", vbCritical

End Sub

Sub ApproveSalOrderGroup(SorId As Long)
Dim dbsRel As Database
Dim wksRel As Workspace
Dim rstRel As Recordset
Dim rstRf As Recordset
Dim strCommit As String

Set wksRel = DBEngine.Workspaces(0)
wksRel.BeginTrans
strCommit = "Begun"

Set dbsRel = CurrentDb()
Set rstRf = dbsRel.OpenRecordset("Select * From fin_salorders Where sor_id = " & SorId & " Or sor_parent = " & SorId)
Do While Not rstRf.EOF
    
    'Add commitment
    Set rstRel = dbsRel.OpenRecordset("fin_commitments")
    With rstRel
        .AddNew
        !cmt_docid = rstRf!sor_id
        !cmt_type = "Sa"
        !cmt_date = DateValue(GetNow())
        !cmt_amount = -1 * rstRf!sor_salary
        !cmt_status = "Awaited"
        !cmt_effhed_id = rstRf!sor_effhed_id
        !cmt_effunt_id = rstRf!sor_effunt_id
        If Not IsNull(rstRf!sor_hed_id) Then !cmt_hed_id = rstRf!sor_hed_id
        !cmt_unt_id = rstRf!sor_unt_id
        If Nz(rstRf!sor_sudohed, "") <> "" Then
            !cmt_sudohed = rstRf!sor_sudohed
            End If
        .Update
        End With
    rstRel.Close
    
    'Update salary order status
    rstRf.Edit
    rstRf!sor_status = "Approved"
    rstRf.Update
    
    rstRf.MoveNext
    Loop

wksRel.CommitTrans
strCommit = "Committed"
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksRel.Rollback
    MsgBox "Salary Order cannot be released." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Sub CancelSalOrderGroup(SorId As Long)
Dim dbsCan As Database
Dim wksCan As Workspace
Dim rstCan As Recordset
Dim rstRf As Recordset
Dim strCommit As String

Set wksCan = DBEngine.Workspaces(0)
wksCan.BeginTrans
strCommit = "Begun"

Set dbsCan = CurrentDb()
Set rstRf = dbsCan.OpenRecordset("Select * From fin_salorders Where sor_id = " & SorId & " Or sor_parent = " & SorId)
Do While Not rstRf.EOF
    Set rstCan = dbsCan.OpenRecordset("Select * from hr_salreqs Where srq_id = " & rstRf!sor_srq_id)
    rstCan.Edit
    rstCan!srq_fulfilment = Null
    rstCan.Update
    rstCan.Close
    
    rstRf.Edit
    rstRf!sor_status = "Cancelled"
    rstRf!sor_closedtg = GetNow()
    rstRf.Update
    
    rstRf.MoveNext
    Loop

wksCan.CommitTrans
strCommit = "Committed"
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksCan.Rollback
    MsgBox "Salary Order cannot be released." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Sub SalaryTest(EmployeeID As String, SalaryMonth As Date)
'Dim dbsSalTest As Database
'Dim rstSalTest As Recordset

'Set dbsSalTest = CurrentDb()
'Set rstSalTest = dbsSalTest.OpenRecordset("Select * From hr_attendance Where att_emp_id = '" & EmployeeID & "' And " & _
'                                   "att_startdt = #" & FirstDateThisMonth(SalaryMonth) & "#", dbOpenSnapshot)
'
'GetSalaryMatrix rstSalTest!att_emp_id, rstSalTest!att_enddt, True
'CalculateArrDues rstSalTest!att_emp_id, rstSalTest!att_enddt, True

GetSalaryMatrix EmployeeID, SalaryMonth, True
CalculateArrDues EmployeeID, SalaryMonth, True

End Sub

Function CalcSalReqs(Optional Generate As Boolean) As Integer
On Error GoTo Err_Handler_CreateSal
Dim dbs As Database
Dim rstAtt As Recordset
Dim rstFetch As Recordset
Dim rstTgt As Recordset
Dim strTgt As String
Dim intReq As Integer
Dim n As Integer
Dim strString As String
Dim dblPaidAlready As Double
Dim dblPaymentInProcess As Double
Dim dblArrDues As Double
Dim arrSalReq() As Variant
Dim strSalRemarks As String
Dim strReason As String

Set dbs = CurrentDb()
If Generate = True Then
    strTgt = "hr_salreqs"
    Else
    strTgt = "hr_salreqs_temp"
    dbs.Execute "Delete From hr_salreqs_temp"
    End If

Set rstTgt = dbs.OpenRecordset(strTgt, dbOpenDynaset, dbAppendOnly)
Set rstAtt = Forms!hr_attendance_u.RecordsetClone

rstAtt.MoveFirst
Do While Not rstAtt.EOF
    dblPaidAlready = 0
    dblPaymentInProcess = 0
    dblArrDues = 0
    strSalRemarks = ""
    strReason = ""

Salary_Checks:  '*****************************************************************************

    'No salary for future
    If DateDiff("m", rstAtt!att_enddt, LastDateThisMonth(Date)) < 0 Then
        MsgBox "Salary Requisitions cannot be generated for future.", vbCritical
        Exit Function
        End If

    'Multiple requisitions are not allowed unless overridden
    If getVarGlobal("salreq_allow_multiple") <> DateValue(GetNow()) Then
        'No salary if there is a draft or open requisition
        Set rstFetch = dbs.OpenRecordset("Select srq_id From hr_salreqs_u Where " & _
                                         "srq_emp_id = '" & rstAtt!att_emp_id & "' And " & _
                                         "srq_status In('Draft','In Process')", dbOpenSnapshot)
        If rstFetch.EOF = False Then
            strReason = "Not Allowed."
            GoTo After_Checks
            End If
        rstFetch.Close
        Else
        'No salary if there is a 'draft' requisition
        Set rstFetch = dbs.OpenRecordset("Select srq_id From hr_salreqs_u Where " & _
                                         "srq_emp_id = '" & rstAtt!att_emp_id & "' And " & _
                                         "srq_status = 'Draft'", dbOpenSnapshot)
        If rstFetch.EOF = False Then
            strReason = "Not Allowed."
            GoTo After_Checks
            End If
        rstFetch.Close
        End If

After_Checks:   '*************************************************************************

    arrSalReq = GetSalaryMatrix(rstAtt!att_emp_id, rstAtt!att_enddt)
     
    For n = 8 To 10
        If n > 8 And IsEmpty(arrSalReq(n, 1)) Then Exit For

        '***Add basic parameters
        rstTgt.AddNew
        rstTgt!srq_emp_id = rstAtt!att_emp_id
        rstTgt!srq_empnamecomp = rstAtt!att_empnamecomp
        rstTgt!srq_month = Format(rstAtt!att_enddt, "yyyy-mm-dd")
        rstTgt!srq_unt_id = rstAtt!att_unt_id
        If strReason <> "" Then GoTo Pre_End
        
        '***Fetch Parameters: Relevant contracts, ctr salary, project, effective head, sudohead, effective unit
        rstTgt!srq_contracts = arrSalReq(n, 1)
        rstTgt!srq_ctrsalary = arrSalReq(n, 4)
        
        If IsEmpty(arrSalReq(n, 18)) Then
            If arrSalReq(4, 20) >= 200000 And arrSalReq(4, 20) < 800000 Then
                strReason = "Contract plan not set."
                If Generate = True Then
                    MsgBox "Contract plan not set for " & rstAtt!att_empnamecomp & "(" & rstAtt!att_emp_id & ") " & _
                    "for month " & Format("mmm yy", rstAtt!att_enddt) & ". Please contact HR department.", vbCritical
                    GoTo Pre_End
                    End If
                End If
            Else
            rstTgt!srq_hed_id = arrSalReq(n, 18)
            End If
        
        If IsEmpty(arrSalReq(n, 19)) Then
            rstTgt!srq_effhed_id = 0
            rstTgt!srq_effunt_id = 0
            strReason = "Salary head not set."
            If Generate = True Then
                MsgBox "Salary head not set for " & rstAtt!att_empnamecomp & "(" & rstAtt!att_emp_id & ")." & _
                "Please contact finance department.", vbCritical
                GoTo Pre_End
                End If
            Else
            strString = arrSalReq(n, 19)
            rstTgt!srq_effhed_id = PartOfString(strString, 1, " ")
            rstTgt!srq_sudohed = PartOfString(strString, 2, " ")
            rstTgt!srq_effunt_id = UnitFromHead(PartOfString(strString, 1, " "))
            End If

        '***Fetching Parameters: Salary components
        If IsEmpty(arrSalReq(n, 1)) Then
            rstTgt!srq_effhed_id = 0
            rstTgt!srq_effunt_id = 0
            strReason = "No contract."
            GoTo Pre_End
            End If
        rstTgt!srq_grosalary = Round(arrSalReq(n, 13), 0)
        rstTgt!srq_unpaiddays = arrSalReq(n, 5) + arrSalReq(n, 6)
        rstTgt!srq_paidholidays = 0
        rstTgt!srq_underwork = arrSalReq(n, 14)
        rstTgt!srq_overwork = arrSalReq(n, 15)
        rstTgt!srq_award = 0       'IIf(n = nLast, x, 0)
        rstTgt!srq_penalty = 0     'IIf(n = nLast, x, 0)
        rstTgt!srq_loaned = 0      'IIf(n = nLast, x, 0)
        rstTgt!srq_withheld = 0    'IIf(n = nLast, x, 0)
        rstTgt!srq_paidalready = IIf(n = 8, dblPaidAlready, 0)
        dblArrDues = IIf(n = 8, CalculateArrDues(rstAtt!att_emp_id, rstAtt!att_enddt), 0)
        rstTgt!srq_arrears = IIf(dblArrDues > 0, dblArrDues, 0)
        rstTgt!srq_dues = IIf(dblArrDues < 0, -1 * dblArrDues, 0)
        Set rstFetch = dbs.OpenRecordset("Select Sum(sor_salary) As inprocess From fin_salorders Where " & _
                                     "sor_emp_id ='" & rstAtt!att_emp_id & "' And " & _
                                     "sor_month = #" & rstAtt!att_enddt & "# And " & _
                                     "sor_status In ('Draft','In Process')", dbOpenSnapshot)
        dblPaymentInProcess = IIf(Not rstFetch.EOF, Nz(rstFetch!inprocess, 0), 0)
        rstFetch.Close

        Set rstFetch = dbs.OpenRecordset("Select Sum(srq_fulfilment) As paid, Last(srq_ctrsalary) As ctrsalary " & _
                                         "From hr_salreqs Where " & _
                                         "srq_emp_id ='" & rstAtt!att_emp_id & "' And " & _
                                         "srq_month = #" & rstAtt!att_enddt & "# And " & _
                                         "srq_status = 'Fulfilled' ", dbOpenSnapshot)
        If Not IsNull(rstFetch!CtrSalary) Then dblPaidAlready = rstFetch!paid 'rstFetch.EOF does not work because of group query
        rstFetch.Close

        '***Calculating Salary
        rstTgt!srq_netsalary = rstTgt!srq_grosalary _
                              + rstTgt!srq_overwork - rstTgt!srq_underwork _
                              + rstTgt!srq_award - rstTgt!srq_penalty _
                              + rstTgt!srq_arrears - rstTgt!srq_dues _
                              - IIf(n = 8, dblPaidAlready, 0) _
                              - IIf(n = 8, dblPaymentInProcess, 0)
                              
        rstTgt!srq_salary = rstTgt!srq_netsalary _
                           - rstTgt!srq_withheld + rstTgt!srq_loaned
        
        If rstTgt!srq_salary <= 0 Then
            strReason = strReason & " No salary."
            GoTo Pre_End
            End If

        '***Creating parameter: Remarks
        strSalRemarks = IIf(n = 8 And arrSalReq(n, 2) = rstAtt!att_startdt, "", "Calculated from " & Format(arrSalReq(n, 2), "dd mmm yy") & ". ") & _
                        IIf(n = 7 + arrSalReq(3, 20) And arrSalReq(n, 3) = rstAtt!att_enddt, "", "Calculated till " & Format(arrSalReq(n, 3), "dd mmm yy") & ". ") & _
                        IIf(dblPaymentInProcess = 0, "", "Payment in process - " & Format(dblPaymentInProcess, "#,###") & ". ") & _
                        IIf(rstTgt!srq_paidalready = 0, "", "Already paid - " & Format(rstTgt!srq_paidalready, "#,###") & ". ") & _
                        IIf(rstTgt!srq_arrears = 0, "", "Arrears - " & Format(rstTgt!srq_arrears, "#,###") & ". ") & _
                        IIf(rstTgt!srq_dues = 0, "", "Dues - " & Format(rstTgt!srq_dues, "#,###") & ". ") & _
                        IIf(rstTgt!srq_underwork = 0, "", "Deduction - " & Format(rstTgt!srq_underwork, "#,###") & " (" & _
                                                           IIf(arrSalReq(n, 5) = 0, "", "absents " & arrSalReq(n, 5) & IIf(arrSalReq(n, 6) = 0, "", ",")) & _
                                                           IIf(arrSalReq(n, 6) = 0, "", "unpaid leave " & arrSalReq(n, 6)) & _
                                                           ")" & ". ") & _
                        IIf(rstTgt!srq_overwork = 0, "", "Overtime - " & Format(rstTgt!srq_overwork, "#,###") & ". ") & _
                        IIf(rstTgt!srq_award = 0, "", "Award - " & Format(rstTgt!srq_award, "#,###") & ". ") & _
                        IIf(rstTgt!srq_penalty = 0, "", "Penalty - " & Format(rstTgt!srq_penalty, "#,###") & ". ") & _
                        IIf(rstTgt!srq_loaned = 0, "", "Paid in advance - " & Format(rstTgt!srq_loaned, "#,###") & ". ") & _
                        IIf(rstTgt!srq_withheld = 0, "", "Withheld - " & Format(rstTgt!srq_withheld, "#,###") & ". ")
        strSalRemarks = Trim(strSalRemarks)
        rstTgt!srq_remarks = strSalRemarks

        '***Check if relevant contracts are verified
        Set rstFetch = dbs.OpenRecordset("Select cvf_verif From fin_contractsverif Where cvf_ctr_id In (" & arrSalReq(n, 1) & ")", dbOpenSnapshot)
        If rstFetch.EOF Then
            strReason = strReason & " Contract not verified."         'For some reason, the contract was not added in list of contracts to be verified
            Else
            Do While Not rstFetch.EOF
                If rstFetch!cvf_verif = False Then strReason = strReason & " Contract not verified."
                rstFetch.MoveNext
                Loop
            End If

        '***Fetching parameters: bank account details
        Set rstFetch = dbs.OpenRecordset("SELECT bac_bnkname, bac_bchcode, bac_acctitle, bac_accnum FROM hr_bnkaccountsforpay " & _
                                              "Where bac_emp_id = '" & rstAtt!att_emp_id & "'", dbOpenSnapshot)
        If rstFetch.EOF = False Then
            If rstFetch.RecordCount > 1 Then
                MsgBox "Multiple bank accounts are marked for salary of " & rstAtt!att_emp_id & ". Please correct bank account data.", vbCritical
                strReason = strReason & " Multiple Bank Accounts."
                GoTo Pre_End
                End If
            If rstFetch!bac_bnkname = "Meezan Bank Ltd" Then
                rstTgt!srq_bnkaccdetail = rstFetch!bac_accnum & " (" & rstFetch!bac_bchcode & ")"
                rstTgt!srq_bnkacctitle = rstFetch!bac_acctitle
                Else
                rstTgt!srq_bnkaccdetail = "(Pay by Cheque)"
                rstTgt!srq_bnkacctitle = rstFetch!bac_acctitle
                End If
            Else
                rstTgt!srq_bnkaccdetail = "(Pay by Cheque)"
                rstFetch.Close
                Set rstFetch = dbs.OpenRecordset("SELECT emp_name From hr_emps Where emp_id ='" & rstAtt!att_emp_id & "'", dbOpenSnapshot)
                rstTgt!srq_bnkacctitle = UCase(rstFetch!emp_name)
            End If
        rstFetch.Close
Pre_End:

        '***Fetching reason for not giving salary (only for Generate=False)
        If Generate = False Then rstTgt!reason = Trim(strReason)

        '***Add record for salary requisition
        If Generate = True And strReason <> "" Then
            rstTgt.CancelUpdate
            Else
            rstTgt.Update
            intReq = intReq + 1
            End If
        Next n
    rstAtt.MoveNext
    Loop
rstAtt.Close
CalcSalReqs = intReq
Exit Function

Err_Handler_CreateSal:
MsgBox "Salary requisition for employee ID " & rstAtt!att_empnamecomp & "(" & rstAtt!att_emp_id & ") cannot be generated due to incomplete data. Please contact HR department.", vbCritical
strReason = "Error."
GoTo Pre_End
End Function

Function CalculateArrDues(EmpId As String, SalMonth As Date, Optional Record As Boolean) As Long
Dim dbsPast As Database
Dim qdfPast As QueryDef
Dim rstPast As Recordset
Dim rstRecord As Recordset
Dim arrSal As Variant
Dim dtMonth As Date
Dim dblToBePaid As Double
Dim dblPaid As Double

Set dbsPast = CurrentDb()
dtMonth = LastDateThisMonth(DateAdd("m", -1, SalMonth))
If Record = True Then Set rstRecord = dbsPast.OpenRecordset("salary_matrix_test")

Do
    arrSal = GetSalaryMatrix(EmpId, dtMonth)
    If IsEmpty(arrSal(1, 1)) Then Exit Do
    dblToBePaid = arrSal(11, 20)
    dblPaid = 0
    
    Set qdfPast = dbsPast.QueryDefs("hr_arrearscalc")
    qdfPast.Parameters("EmpId") = EmpId
    qdfPast.Parameters("SalMonth") = dtMonth
    Set rstPast = qdfPast.OpenRecordset()
    If Not rstPast.EOF Then
        If Not IsNull(rstPast!marker) Then
            dblToBePaid = dblToBePaid _
                        + rstPast!sor_award - rstPast!sor_penalty _
                        + rstPast!sor_withheld - rstPast!sor_loaned
            dblPaid = rstPast!sor_salary
            End If
       End If
        
    Set qdfPast = dbsPast.QueryDefs("hr_arrearsextcomp")
    qdfPast.Parameters("EmpId") = EmpId
    qdfPast.Parameters("SalMonth") = dtMonth
    Set rstPast = qdfPast.OpenRecordset()
    If Not rstPast.EOF Then
        If Not IsNull(rstPast!marker) Then
            dblPaid = dblPaid + rstPast!ecp_amount
            End If
        End If
    
    If arrSal(11, 6) = Day(LastDateThisMonth(DateAdd("m", -1, dtMonth))) And dblPaid = 0 Then
        dblToBePaid = 0
        End If
    
    If Record = True Then
        rstRecord.AddNew
        rstRecord!Type = 11
        rstRecord!End = dtMonth
        rstRecord!msal = dblToBePaid
        rstRecord!sal = dblPaid
        rstRecord.Update
        End If
    
    CalculateArrDues = CalculateArrDues + dblToBePaid - dblPaid
    dtMonth = LastDateThisMonth(DateAdd("m", -1, dtMonth))
    Loop While Round(dblToBePaid, 0) <> Round(dblPaid, 0)

End Function

Function GetTadaforSalary(dblSalary As Double) As Long
Select Case dblSalary
    Case Is < 50000: GetTadaforSalary = 2000
    Case Is > 100000: GetTadaforSalary = 5000
    Case Else: GetTadaforSalary = 3500
    End Select
End Function

Sub doooooo()
SalaryTest "12-23-07-8573", #7/30/2024#
'ArrayToTable GetSalaryMatrix("11-19-08-8746", #12/31/2024#), "salary_matrix_test"
'GetSalaryMatrix "18-21-08-5273", #6/30/2024#
'Debug.Print CalculateArrDues("18-21-08-5273", #6/30/2024#)
'Debug.Print UBound(GetSalaryMatrix("21-20-02-4593", #7/31/2024#), 2)
'Dim n As Integer
'Dim dcnDates As Scripting.Dictionary
'Set dcnDates = New Scripting.Dictionary
'
'For n = 0 To dcnDates.Count - 1
'    Debug.Print dcnDates.Keys()(n), dcnDates.Items()(n)
'    Next n

End Sub

Function GetSalaryMatrix(EmpId As String, SalMonth As Date, Optional Record As Boolean) As Variant()
On Error GoTo Error_Handler

Dim dbsSalMat As Database
Dim rstSalMat As Recordset
Dim qdfSalMat As QueryDef
Dim dtFrom As Date, dtTo As Date, dtMonth As Date
Dim dtAttFrom As Date, dtAttTo As Date
Dim strSql As String
Dim arrTemp(1 To 36, 20) As Variant
Dim arrSalMat(1 To 11, 20) As Variant
Dim dcnDays As Variant
Dim UnitId As Long
Dim x As Long
Dim n As Integer, m As Integer
Dim booNoPresence As Boolean

'Establish basic dates
dtAttFrom = AttMonthStartDate(SalMonth)
dtAttTo = AttMonthEndDate(SalMonth)
dtFrom = FirstDateThisMonth(SalMonth)
dtTo = LastDateThisMonth(SalMonth)

'Get relevant contracts
Set dbsSalMat = CurrentDb()
strSql = "SELECT * FROM hr_contracts_plus " & _
         "WHERE ctr_num ='" & EmpId & "' And Sgn([ctr_startdt]-#" & dtTo & "#)*Sgn(#" & dtAttFrom & "#-[ctr_effenddt]) >=0 " & _
         "ORDER BY ctr_startdt, cpn_startdt;"
Set rstSalMat = dbsSalMat.OpenRecordset(strSql, dbOpenSnapshot)
If rstSalMat.EOF = True Then GoTo Output

'Create temporary matrix ******************************************************************************

'Add types, contract ids, start dates, end dates, salaries, projects, heads (columns 0, 1, 2, 3, 4, 18, 19)
n = 1
Do While Not rstSalMat.EOF
    arrTemp(n, 0) = 3
    arrTemp(n, 1) = rstSalMat!ctr_id
    arrTemp(n, 2) = rstSalMat!cpn_startdt
    arrTemp(n, 3) = rstSalMat!cpn_enddt
    arrTemp(n, 4) = rstSalMat!ctr_salary
    arrTemp(n, 2) = IIf(rstSalMat!ctr_unt_id >= 200000 And rstSalMat!ctr_unt_id < 800000, rstSalMat!cpn_startdt, rstSalMat!ctr_effstartdt)
    arrTemp(n, 3) = IIf(rstSalMat!ctr_unt_id >= 200000 And rstSalMat!ctr_unt_id < 800000, rstSalMat!cpn_enddt, rstSalMat!ctr_effenddt)
    arrTemp(n, 18) = IIf(Not IsNull(rstSalMat!cpn_hed_id), rstSalMat!cpn_hed_id, Empty)
    arrTemp(n, 19) = IIf(Not IsNull(rstSalMat!cpn_hed_id), rstSalMat!cpn_hed_id, Empty)
    n = n + 1
    rstSalMat.MoveNext
    Loop

'Merge additional periods
m = n - 1
x = 0
rstSalMat.MoveFirst                                              'period 2 (probation)
Do While Not rstSalMat.EOF
    If rstSalMat!ctr_id <> x Then
        If Not IsNull(rstSalMat!ctr_prob) And rstSalMat!ctr_probsal <> rstSalMat!ctr_salary Then
            SplitPeriod arrTemp, rstSalMat!ctr_startdt, DateAdd("m", rstSalMat!ctr_prob, rstSalMat!ctr_startdt) - 1, m, 2, rstSalMat!ctr_probsal
            End If
        End If
    x = rstSalMat!ctr_id
    rstSalMat.MoveNext
    Loop
SplitPeriod arrTemp, dtAttFrom, DateAdd("d", -1, dtFrom), m, 1    'Period 1
SplitPeriod arrTemp, DateAdd("d", 1, dtAttTo), dtTo, m, 4         'Period 4

'Replace heads where applicable as per global variable 'salhead_applicable'
rstSalMat.MovePrevious
x = rstSalMat!ctr_unt_id
rstSalMat.Close
n = 1
If Not (getVarGlobal("salhead_applicable") = False And x >= 200000 And x < 800000) Then
    strSql = "SELECT * FROM fin_empeffheads Where eeh_emp_id ='" & EmpId & "'"
    Set rstSalMat = dbsSalMat.OpenRecordset(strSql, dbOpenSnapshot)
    Do While Not IsEmpty(arrTemp(n, 0))
            If Not IsNull(rstSalMat!eeh_emphed_id) Then arrTemp(n, 19) = rstSalMat!eeh_emphed_id & IIf(Nz(rstSalMat!eeh_sudohed, "") = "", "", " " & rstSalMat!eeh_sudohed)
            n = n + 1
        Loop
    End If

'Mark irrelevant periods
n = 1
Do While Not IsEmpty(arrTemp(n, 0))
    If Not (arrTemp(n, 2) >= dtAttFrom And arrTemp(n, 3) <= dtTo) Then arrTemp(n, 0) = 0
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
        arrSalMat(m, 18) = arrTemp(n, 18)
        arrSalMat(m, 19) = arrTemp(n, 19)
        m = m + 1
        End If
    n = n + 1
    Loop

'Add attendance days (columns 5 to 12)
For n = 1 To 7
    If IsEmpty(arrSalMat(n, 0)) Then Exit For
    If arrSalMat(n, 0) = 4 Then
        arrSalMat(n, 5) = 0
        arrSalMat(n, 6) = 0
        Else
        Set dcnDays = EmpAttendance(EmpId, CDate(arrSalMat(n, 2)), CDate(arrSalMat(n, 3)))
        arrSalMat(n, 5) = dcnDays("A")
        arrSalMat(n, 6) = dcnDays("U")
        End If
    Next n

'Salaries (column 13)
For n = 1 To 7
    If IsEmpty(arrSalMat(n, 0)) Then Exit For
    arrSalMat(n, 13) = IIf(arrSalMat(n, 0) = 1, 0, arrSalMat(n, 4) / (DateDiff("d", dtFrom, dtTo) + 1) * (DateDiff("d", arrSalMat(n, 2), arrSalMat(n, 3)) + 1))
    Next n

'Underwork amount (column 14)
For n = 1 To 7
    If IsEmpty(arrSalMat(n, 0)) Then Exit For
    dtMonth = arrSalMat(n, 2)
    arrSalMat(n, 14) = arrSalMat(n, 4) / (DateDiff("d", FirstDateThisMonth(dtMonth), LastDateThisMonth(dtMonth)) + 1) * (arrSalMat(n, 5) + arrSalMat(n, 6))
    Next n

''Set underwork amounts to 0 for complete absence
'booNoPresence = True
'For n = 1 To 7
'    If IsEmpty(arrSalMat(n, 0)) Then Exit For
'    If DateDiff("d", arrSalMat(n, 2), arrSalMat(n, 3)) + 1 <> arrSalMat(n, 5) + arrSalMat(n, 6) Then booNoPresence = False
'    Next n
'If booNoPresence = True Then
'    For n = 1 To 7
'        If IsEmpty(arrSalMat(n, 0)) Then Exit For
'        arrSalMat(n, 14) = -1 * arrSalMat(n, 13)
'        Next n
'    End If
    
'Partial Summaries (rows 8 to 10)
m = 8
For n = 1 To 7
    If IsEmpty(arrSalMat(n, 0)) Then Exit For
    If n <> 1 And Not IsEmpty(arrSalMat(m, 19)) Then
        If arrSalMat(m, 19) <> arrSalMat(n, 19) Then m = m + 1
        End If
    If arrSalMat(n, 0) <> 1 Then
        arrSalMat(m, 1) = IIf(arrSalMat(m, 1) Like "*" & arrSalMat(n, 1) & "*", arrSalMat(m, 1), arrSalMat(m, 1) & "," & arrSalMat(n, 1))
        If arrSalMat(m, 1) Like ",*" Then arrSalMat(m, 1) = Right(arrSalMat(m, 1), Len(arrSalMat(m, 1)) - 1)
        arrSalMat(m, 2) = IIf(IsEmpty(arrSalMat(m, 2)), arrSalMat(n, 2), IIf(arrSalMat(n, 2) < arrSalMat(m, 2), arrSalMat(n, 3), arrSalMat(m, 2)))
        arrSalMat(m, 3) = IIf(IsEmpty(arrSalMat(m, 3)), arrSalMat(n, 3), IIf(arrSalMat(n, 3) > arrSalMat(m, 3), arrSalMat(n, 3), arrSalMat(m, 3)))
        arrSalMat(m, 4) = arrSalMat(n, 4)
        arrSalMat(m, 18) = arrSalMat(n, 18)
        arrSalMat(m, 19) = arrSalMat(n, 19)
        End If
    arrSalMat(m, 0) = 7
    arrSalMat(m, 5) = arrSalMat(m, 5) + Nz(arrSalMat(n, 5))
    arrSalMat(m, 6) = arrSalMat(m, 6) + Nz(arrSalMat(n, 6))
    arrSalMat(m, 13) = arrSalMat(m, 13) + Nz(arrSalMat(n, 13))
    arrSalMat(m, 14) = arrSalMat(m, 14) + Nz(arrSalMat(n, 14))
    arrSalMat(m, 15) = 0    ' Reserved for overwork
    Next n

'Summary (row 11)
For n = 8 To 10
    If IsEmpty(arrSalMat(n, 1)) Then Exit For
    arrSalMat(11, 0) = 9
    arrSalMat(11, 1) = IIf(arrSalMat(11, 1) Like "*" & arrSalMat(n, 1) & "*", arrSalMat(11, 1), arrSalMat(11, 1) & "," & arrSalMat(n, 1))
    If arrSalMat(11, 1) Like ",*" Then arrSalMat(11, 1) = Right(arrSalMat(11, 1), Len(arrSalMat(11, 1)) - 1)
    arrSalMat(11, 2) = IIf(IsEmpty(arrSalMat(11, 2)), arrSalMat(n, 2), IIf(arrSalMat(n, 2) < arrSalMat(11, 2), arrSalMat(n, 3), arrSalMat(11, 2)))
    arrSalMat(11, 3) = IIf(IsEmpty(arrSalMat(11, 3)), arrSalMat(n, 3), IIf(arrSalMat(n, 3) > arrSalMat(11, 3), arrSalMat(n, 3), arrSalMat(11, 3)))
    arrSalMat(11, 4) = arrSalMat(n, 4)
    arrSalMat(11, 5) = arrSalMat(11, 5) + arrSalMat(n, 5)
    arrSalMat(11, 6) = arrSalMat(11, 6) + arrSalMat(n, 6)
    arrSalMat(11, 13) = arrSalMat(11, 13) + arrSalMat(n, 13)
    arrSalMat(11, 14) = arrSalMat(11, 14) + arrSalMat(n, 14)
    arrSalMat(11, 15) = 0    ' Reserved for overwork
    Next n

'General Data (column 20)
arrSalMat(1, 20) = EmpId
arrSalMat(2, 20) = Format(SalMonth, "mmm yy")
arrSalMat(3, 20) = n - 8
arrSalMat(4, 20) = x
If booNoPresence = True Then arrSalMat(5, 20) = "No Presence"
arrSalMat(8, 20) = arrSalMat(8, 13) - arrSalMat(8, 14) + arrSalMat(8, 15)
arrSalMat(9, 20) = arrSalMat(9, 13) - arrSalMat(9, 14) + arrSalMat(9, 15)
arrSalMat(10, 20) = arrSalMat(10, 13) - arrSalMat(10, 14) + arrSalMat(10, 15)
arrSalMat(11, 20) = arrSalMat(11, 13) - arrSalMat(11, 14) + arrSalMat(11, 15)

Output:
If Record = True Then ArrayToTable arrSalMat, "salary_matrix_test", True
GetSalaryMatrix = arrSalMat

Exit Function
Error_Handler:
MsgBox Err.Number & " - " & Err.Description
End Function

Sub SplitPeriod(InArray() As Variant, StartDate As Date, EndDate As Date, BaseLevel As Integer, PeriodType As Integer, Optional AltSalary As Double)
Dim R As Integer
Dim c As Integer
Dim booRepeat As Boolean

R = 1
Do While Not IsEmpty(InArray(R, 0))
    Select Case True
        Case StartDate = InArray(R, 2)
            Exit Do
        Case StartDate > InArray(R, 2) And StartDate <= InArray(R, 3)
            AddRowAt InArray, R + 1
            For c = LBound(InArray, 2) To UBound(InArray, 2)
                InArray(R + 1, c) = InArray(R, c)
                Next c
            InArray(R, 3) = DateAdd("d", -1, StartDate)
            InArray(R + 1, 2) = StartDate
            Exit Do
        End Select
    R = R + 1
    Loop

Do While Not IsEmpty(InArray(R, 0))
    Select Case True
        Case EndDate >= InArray(R, 2) And EndDate < InArray(R, 3)
            AddRowAt InArray, R + 1
            For c = LBound(InArray, 2) To UBound(InArray, 2)
                InArray(R + 1, c) = InArray(R, c)
                Next c
            InArray(R, 3) = EndDate
            InArray(R + 1, 2) = DateAdd("d", 1, EndDate)
            Exit Do
        Case EndDate = InArray(R, 3)
            Exit Do
        End Select
    R = R + 1
    Loop

R = 1
Do While Not IsEmpty(InArray(R, 0))
    If InArray(R, 2) >= StartDate And InArray(R, 2) <= EndDate Then
        InArray(R, 0) = PeriodType
        Select Case PeriodType
            'Case 1: InArray(r, 4) = 0
            Case 2: InArray(R, 4) = AltSalary
            End Select
        End If
    R = R + 1
    Loop

End Sub













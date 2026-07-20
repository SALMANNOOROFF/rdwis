VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_salorders_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

'****By default form allowedits = false and all fields locked = true
Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft"
        Me.RecordSource = "fin_salorders_u_draft"
        Me.lblTitle.Caption = "Salary Orders - Draft"
        Me.cmdMissing.Visible = True
        If getVar("varMode") = "approver-m" Then
            Me.AllowEdits = True
            Me.sor_remarks2.Locked = False
            Me.sor_remarks2.BorderStyle = 1
            Me.sor_salary.Locked = False
            Me.sor_salary.BorderStyle = 1
            Me.sor_checked.Locked = False
            Me.cmdMinute.Visible = True
            Me.cmdApprove.Visible = True
            Me.cmdCancel.Visible = True
            Me.cmdMissing.Visible = True
            Me.cmdReportSalOrders.Visible = True
            Me.OrderBy = "[sor_checked]"
            Me.OrderByOn = True
            End If
    Case "Open"
        Me.RecordSource = "fin_salorders_u_open"
        Me.lblTitle.Caption = "Salary Orders - Open"
        If getVar("varMode") = "approver-m" Then
            Me.cmdApprove.Visible = True
            Me.cmdCancel.Visible = True
            End If
        If getVar("varMode") = "approver-m" Then Me.cmdReverse.Visible = True
    Case "Closed"
        Me.RecordSource = "fin_salorders_u_closed"
        Me.lblTitle.Caption = "Salary Orders - Closed"
        If getVar("varMode") = "approver-m" Then Me.cmdReverse.Visible = True
    Case Else
        MsgBox "Access denied."
        Exit Sub
    End Select
Me.Visible = True
End Sub

Private Sub sor_salary_BeforeUpdate(Cancel As Integer)
If Me.sor_salary.Value > Me.sor_netsalary Then
    MsgBox "Salary cannot be increased manually.", vbCritical
    Cancel = True
    Me.sor_salary.Undo
    End If
End Sub

Private Sub sor_salary_AfterUpdate()
Dim intPos As Integer

intPos = InStr(Nz(Me.sor_remarks, 0), "Pending")
If intPos > 0 Then Me.sor_remarks = Trim(Left(Me.sor_remarks, intPos - 1))
If Me.sor_salary.Value < Me.sor_netsalary Then
    Me.sor_remarks = Trim(Me.sor_remarks & " Pending - " & Me.sor_netsalary - Me.sor_salary.Value & ".")
    End If
Me.Refresh
End Sub

Private Sub cmdApprove_Click()
Dim dbsRc As Database
Dim rstRc As Recordset
Dim qdfRc As QueryDef
Dim allVerified As Boolean

Me.Dirty = False
If Me!sor_parent > 0 Then
    MsgBox "This salary order cannot be released directly. Please relaese the parent salary order.", vbCritical
    Exit Sub
    End If

'*** If contract(s) for salary duration are not verified, donot release the salary order
Set dbsRc = CurrentDb()
Set rstRc = dbsRc.OpenRecordset("Select cvf_verif From fin_contractsverif Where cvf_ctr_id In (" & Me!sor_contracts & ")", dbOpenSnapshot)
If rstRc.EOF Then GoTo NotVerified          'If for some reason, the contract was not added in list of contracts to be verified
allVerified = rstRc!cvf_verif
Do While Not rstRc.EOF
    allVerified = allVerified And rstRc!cvf_verif
    rstRc.MoveNext
    Loop
If allVerified = False Then
NotVerified:
    MsgBox "This salary order cannot be released because salary is not verified. Please verify the salary.", vbCritical
    Exit Sub
    End If
rstRc.Close

ApproveSalOrderGroup Me!sor_id
Me.Requery
End Sub

Private Sub cmdCancel_Click()
Dim rstCan As Recordset

Me.Dirty = False
If Me!sor_parent > 0 Then
    MsgBox "This requisition cannot be cancelled directly. Please cancel the parent requisition.", vbCritical
    Exit Sub
    End If

CancelSalOrderGroup Me!sor_id
Me.Requery
End Sub

Private Sub cmdReportSalOrders_Click()
On Error GoTo cmdReportSalOrders_Click_Err
Dim strDate As String

Me.Dirty = False
Select Case Me.lblTitle.Caption
    Case "Salary Orders - Draft"
    DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Draft"
    Case "Salary Orders - Open"
    strDate = InputBox("Please enter salary order date.", "Date Required", CStr(Date))
    DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Open~" & strDate
    Case "Salary Orders - Closed"
    strDate = InputBox("Please enter salary order date.", "Date Required", CStr(Date))
    DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Closed~" & strDate
End Select

cmdReportSalOrders_Click_Exit:
    Exit Sub

cmdReportSalOrders_Click_Err:
    MsgBox Error$
    Resume cmdReportSalOrders_Click_Exit

End Sub

Private Sub cmdMissing_Click()
Dim strMiss As String
Dim dteReq  As Date

dteReq = FirstDateThisMonth(Me.sor_month)
strMiss = "Select * From " & _
          "(Select * From hr_emps Where emp_status like 'Active*' Or emp_lastdt >= #" & dteReq & "#) " & _
          "Where emp_id not In (Select sor_emp_id From fin_salorders Where sor_status = 'Draft')"

DoCmd.OpenForm "hr_emps_u", acNormal, , , , , "SalReqsAwaited~" & strMiss

End Sub

Private Sub cmdReverse_Click()
Dim intresponse  As Integer
Dim lngRevId As Long

intresponse = MsgBox("A Data Revision Request for Salary Order " & Me!sor_id & ", will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Salary Order", Me!sor_id, Me!sor_unt_id, 1)
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_salorders_u"

End Sub

Private Sub sor_emp_id_DblClick(Cancel As Integer)
DoCmd.OpenForm "hr_contracts", acNormal, , , , acHidden, "OneRecord~" & Me!sor_emp_id
End Sub

Private Sub sor_netsalary_DblClick(Cancel As Integer)
SalaryTest Me!sor_emp_id, Me!sor_month
DoCmd.OpenForm "salary_matrix_test", acNormal, "", "", , acNormal
End Sub

Private Sub cmdSO_Click()
DoCmd.OpenForm "fin_salorders_one", acNormal, , , , acHidden, "~" & Me.sor_id
End Sub

Private Sub cmdSalarySlip_Click()
On Error GoTo cmdSalarySlip_Click_Err

Me.Dirty = False
DoCmd.OpenReport "fin_salary_slip", acViewReport, "", "", acNormal


cmdSalarySlip_Click_Exit:
    Exit Sub

cmdSalarySlip_Click_Err:
    MsgBox Error$
    Resume cmdSalarySlip_Click_Exit

End Sub

Private Sub cmdMinute_Click()
On Error GoTo cmdMinute_Click_Err

DoCmd.OpenForm "fin_salorderminute", acNormal, "", "", , acNormal


cmdMinute_Click_Exit:
    Exit Sub

cmdMinute_Click_Err:
    MsgBox Error$
    Resume cmdMinute_Click_Exit

End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_rdwprj_multiple"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
StartupSteps Me.Name
Me.Visible = True
End Sub

Private Sub Form_Current()
SetMessagesMprStatus
End Sub

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub

Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
End Sub


Private Sub cmdEmpAll_Click()
On Error GoTo cmdEmpAll_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"


cmdEmpAll_Click_Exit:
    Exit Sub

cmdEmpAll_Click_Err:
    MsgBox Error$
    Resume cmdEmpAll_Click_Exit

End Sub

Private Sub cmdEmpPrev_Click()
On Error GoTo cmdEmpPrev_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Previous~"

cmdEmpPrev_Click_Exit:
    Exit Sub

cmdEmpPrev_Click_Err:
    MsgBox Error$
    Resume cmdEmpPrev_Click_Exit
End Sub

Private Sub cmdPayrol_Click()
On Error GoTo cmdPayrol_Click_Err

    DoCmd.OpenReport "payroll", acViewPreview, "", "", acNormal


cmdPayrol_Click_Exit:
    Exit Sub

cmdPayrol_Click_Err:
    MsgBox Error$
    Resume cmdPayrol_Click_Exit

End Sub

Private Sub cmdStatusProjAcc_Click()
OpenAccountsStatusReport "Open"
End Sub

Private Sub cmdStatChrf_Click()
OpenChrfReport "Open"
End Sub

Private Sub cmdAttend_Click()
On Error GoTo cmdAttend_Click_Err
Dim dbsAttend As Database
Dim rstAttend As Recordset
Dim dtAttend As Date

Set dbsAttend = CurrentDb()
'Generate this month's sheet if not yet generated
dtAttend = FirstDateThisMonth(Date)
Set rstAttend = dbsAttend.OpenRecordset("Select att_id from hr_attendance Where att_startdt = #" & dtAttend & "#")
If rstAttend.EOF = True Then makeAttendanceSheet (dtAttend)
rstAttend.Close

'If last person of dept left last month, open att_sheet in last month
Set rstAttend = dbsAttend.OpenRecordset("Select att_id from hr_attendance_u Where att_startdt = #" & dtAttend & "#")
If rstAttend.EOF = True Then dtAttend = FirstDatePrevMonth(dtAttend)
rstAttend.Close

'Open attendance sheet
DoCmd.OpenForm "hr_attendance_u", acNormal, "", "", , , "~" & Date

cmdAttend_Click_Exit:
    Exit Sub

cmdAttend_Click_Err:
    MsgBox Error$
    Resume cmdAttend_Click_Exit

End Sub

Private Sub boxGatePassNone_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnGatePassNone~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$
End Sub

Private Sub boxGatePassExpiry_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnGatePassExpiry~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$
End Sub

Private Sub boxContractNone_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnContractNone~"


Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxContractExpiry_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnContractExpiry~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxPersonala_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnPersonala~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub


Private Sub boxPersonalb_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnPersonalb~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub cmdPrepReturn_Click()
On Error GoTo cmdPrepReturn_Click_Err

DoCmd.OpenForm "prj_projects_mproverview", acNormal, "", "", , acNormal

cmdPrepReturn_Click_Exit:
    Exit Sub

cmdPrepReturn_Click_Err:
    MsgBox Error$
    Resume cmdPrepReturn_Click_Exit

End Sub

Private Sub cmdCompleteness_Click()
On Error GoTo cmdCompleteness_Click_Err

DoCmd.OpenForm "hr_completeness_u", acNormal, "", "", , acNormal

cmdCompleteness_Click_Exit:
    Exit Sub

cmdCompleteness_Click_Err:
    MsgBox Error$
    Resume cmdCompleteness_Click_Exit

End Sub

Private Sub cmdProjOpen_Click()

DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Open"


cmdProjOpen_Click_Exit:
    Exit Sub

cmdProjOpen_Click_Err:
    MsgBox Error$
    Resume cmdProjOpen_Click_Exit
End Sub

Private Sub cmdProjClosed_Click()
On Error GoTo cmdProjClosed_Click_Err

DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Closed"


cmdProjClosed_Click_Exit:
    Exit Sub

cmdProjClosed_Click_Err:
    MsgBox Error$
    Resume cmdProjClosed_Click_Exit

End Sub

Private Sub cmdSalReqsDraft_Click()
On Error GoTo cmdSalReqsDraft_Click_Err

DoCmd.OpenForm "hr_salreqs_u", acNormal, , , , acHidden, "Draft"

cmdSalReqsDraft_Click_Exit:
    Exit Sub
cmdSalReqsDraft_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsDraft_Click_Exit
    
End Sub



Private Sub cmdSalReqsInprocess_Click()
On Error GoTo cmdSalReqsInprocess_Click_Err

DoCmd.OpenForm "hr_salreqs_u", , "", "", , acHidden, "In Process"

cmdSalReqsInprocess_Click_Exit:
    Exit Sub
cmdSalReqsInprocess_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsInprocess_Click_Exit
    
End Sub

Private Sub cmdSalReqsCompleted_Click()
On Error GoTo cmdSalReqsCompleted_Click_Err

DoCmd.OpenForm "hr_salreqs_u", , "", "", , acHidden, "Closed"

cmdSalReqsCompleted_Click_Exit:
    Exit Sub
cmdSalReqsCompleted_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsCompleted_Click_Exit
    
End Sub


Private Sub cmdDeptLead_Click()
On Error GoTo cmdDeptLead_Click_Err

DoCmd.OpenForm "cen_unit_u", acNormal, "", "", , acNormal

cmdDeptLead_Click_Exit:
    Exit Sub

cmdDeptLead_Click_Err:
    MsgBox Error$
    Resume cmdDeptLead_Click_Exit

End Sub

Private Sub cmdExServicemen_Click()
On Error GoTo cmdExServicemen_Click_Err

DoCmd.OpenReport "hr_emps_exservicemen_doc", acViewReport, "", "", acNormal

cmdExServicemen_Click_Exit:
    Exit Sub

cmdExServicemen_Click_Err:
    MsgBox Error$
    Resume cmdExServicemen_Click_Exit

End Sub

Private Sub cmdGrades_Click()
On Error GoTo cmdGrades_Click_Err

DoCmd.OpenReport "hr_emps_gradewise_doc", acViewReport, "", "", acNormal

cmdGrades_Click_Exit:
    Exit Sub

cmdGrades_Click_Err:
    MsgBox Error$
    Resume cmdGrades_Click_Exit

End Sub

Private Sub cmdQualifications_Click()
On Error GoTo cmdQualifications_Click_Err

DoCmd.OpenReport "hr_emps_qualifwise_doc", acViewReport, "", "", acNormal

cmdQualifications_Click_Exit:
    Exit Sub

cmdQualifications_Click_Err:
    MsgBox Error$
    Resume cmdQualifications_Click_Exit

End Sub

Private Sub CmdPersonnel_Click()
On Error GoTo CmdPersonnel_Click_Err

DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal

CmdPersonnel_Click_Exit:
    Exit Sub

CmdPersonnel_Click_Err:
    MsgBox Error$
    Resume CmdPersonnel_Click_Exit

End Sub

Private Sub cmdSalariesPaid_Click()
On Error GoTo cmdSalariesPaid_Click_Err

DoCmd.OpenReport "fin_salariespaid-u_doc", acViewReport, "", "", acNormal

cmdSalariesPaid_Click_Exit:
    Exit Sub

cmdSalariesPaid_Click_Err:
    MsgBox Error$
    Resume cmdSalariesPaid_Click_Exit

End Sub

Private Sub cmdPurReqDraft_Click()
On Error GoTo cmdPurReqDraft_Click_Err

DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Draft"

cmdPurReqDraft_Click_Exit:
    Exit Sub

cmdPurReqDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurReqDraft_Click_Exit

End Sub

Private Sub cmdPurReqOpen_Click()
On Error GoTo cmdPurReqOpen_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Open"


cmdPurReqOpen_Click_Exit:
    Exit Sub

cmdPurReqOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurReqOpen_Click_Exit

End Sub

Private Sub cmdPurReqClosed_Click()
On Error GoTo cmdPurReqClosed_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Closed"


cmdPurReqClosed_Click_Exit:
    Exit Sub

cmdPurReqClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurReqClosed_Click_Exit

End Sub

Private Sub cmdPurReqNew_Click()
On Error GoTo cmdPurReqNew_Click_Err

DoCmd.OpenForm "pur_purreqs_detail", acNormal, "", "", acFormAdd, acWindowNormal, "DataEntry~"


cmdPurReqNew_Click_Exit:
    Exit Sub

cmdPurReqNew_Click_Err:
    MsgBox Error$
    Resume cmdPurReqNew_Click_Exit

End Sub

Private Sub cmdPurCaseDraft_Click()
On Error GoTo cmdPurCaseDraft_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Draft"

cmdPurCaseDraft_Click_Exit:
    Exit Sub

cmdPurCaseDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseDraft_Click_Exit

End Sub

Private Sub cmdPurCaseOpen_Click()
On Error GoTo cmdPurCaseOpen_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Open"

cmdPurCaseOpen_Click_Exit:
    Exit Sub

cmdPurCaseOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseOpen_Click_Exit

End Sub

Private Sub cmdPurCaseClosed_Click()
On Error GoTo cmdPurCaseClosed_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Closed"

cmdPurCaseClosed_Click_Exit:
    Exit Sub

cmdPurCaseClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseClosed_Click_Exit

End Sub


Private Sub mdItemsStatus_Click()
On Error GoTo mdItemsStatus_Click_Err

    DoCmd.OpenReport "pur_itemsstatus", acViewReport, "", "", acNormal


mdItemsStatus_Click_Exit:
    Exit Sub

mdItemsStatus_Click_Err:
    MsgBox Error$
    Resume mdItemsStatus_Click_Exit

End Sub


Private Sub cmdPurRecDraft_Click()
On Error GoTo cmdPurRecDraft_Click_Err

    DoCmd.OpenForm "pur_purreceipts_u", acNormal, "", "", , acHidden, "Draft"


cmdPurRecDraft_Click_Exit:
    Exit Sub

cmdPurRecDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurRecDraft_Click_Exit

End Sub


Private Sub cmdPurRecClosed_Click()
On Error GoTo cmdPurRecClosed_Click_Err

    DoCmd.OpenForm "pur_purreceipts_u", acNormal, "", "", , acNormal, "Closed"


cmdPurRecClosed_Click_Exit:
    Exit Sub

cmdPurRecClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurRecClosed_Click_Exit

End Sub

Private Sub cmdAccStatus_Click()
On Error GoTo cmdAccStatus_Click_Err

'If IsNull(Me.cmbAccount) Then
'    MsgBox "Please select an account."
'    Exit Sub
'    End If
'Forms!vars!Parameter1 = Me.cmbAccount
'
'DoCmd.OpenForm "fin_headstatus", acNormal, "", "", , acNormal

cmdAccStatus_Click_Exit:
    Exit Sub

cmdAccStatus_Click_Err:
    MsgBox Error$
    Resume cmdAccStatus_Click_Exit

End Sub

Private Sub cmdAccStatusBrief_Click()
On Error GoTo cmdAccStatusBrief_Click_Err

'If IsNull(Me.cmbAccount) Then
'    MsgBox "Please select an account."
'    Exit Sub
'    End If
'Forms!vars!Parameter1 = Me.cmbAccount
'
'DoCmd.OpenForm "fin_headstatus_brief", acNormal, "", "", , acNormal


cmdAccStatusBrief_Click_Exit:
    Exit Sub

cmdAccStatusBrief_Click_Err:
    MsgBox Error$
    Resume cmdAccStatusBrief_Click_Exit

End Sub

Private Sub cmdLoans_Click()
OpenLoansForm
End Sub


Private Sub cmdProjectShares_Click()

'If IsNull(Me.cmbAccount) Then
'    MsgBox "Please select an account."
'    Exit Sub
'    End If
'Forms!vars!Parameter1 = Me.cmbAccount
'
'DoCmd.OpenForm "fin_sharesalloc_plus", acNormal, "", "", , acNormal

End Sub

Private Sub cmdPurCaseNew_Click()
On Error GoTo cmdPurCaseNew_Click_Err

DoCmd.OpenForm "pur_purcases_source", acNormal, "", "", , acNormal

cmdPurCaseNew_Click_Exit:
    Exit Sub

cmdPurCaseNew_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseNew_Click_Exit

End Sub

Private Sub cmdPCAwaited_Click()
On Error GoTo cmdPCAwaited_Click_Err

    DoCmd.OpenReport "fin_purcases_awaited", acViewReport, "", "", acNormal


cmdPCAwaited_Click_Exit:
    Exit Sub

cmdPCAwaited_Click_Err:
    MsgBox Error$
    Resume cmdPCAwaited_Click_Exit

End Sub

Private Sub cmdChrf_Click()
On Error GoTo cmdChrf_Click_Err
Dim dbsChrf As Database
Dim qryChrf As QueryDef

DoCmd.Hourglass True
Set dbsChrf = CurrentDb()
dbsChrf.Execute "Delete From fin_chrfstatus_open_temp"
Set qryChrf = dbsChrf.QueryDefs("fin_chrfstatus_open_temp_adder")
qryChrf.Execute
DoCmd.Hourglass False

DoCmd.OpenReport "fin_chrfstatus_open", acViewReport, "", "", acNormal


cmdChrf_Click_Exit:
    Exit Sub

cmdChrf_Click_Err:
    MsgBox Error$
    Resume cmdChrf_Click_Exit

End Sub





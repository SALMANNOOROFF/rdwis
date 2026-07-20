VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_hr_multiple"
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
SetWarningsGatePass
SetWarningsContract
SetWarningsPersonalData
SetWarningsClearance
End Sub

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub

Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
End Sub

Private Sub cmdEmpCurrent_Click()
On Error GoTo cmdEmpCurrent_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"

cmdEmpCurrent_Click_Exit:
    Exit Sub

cmdEmpCurrent_Click_Err:
    MsgBox Error$
    Resume cmdEmpCurrent_Click_Exit

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

Private Sub cmdEmpAdd_Click()
On Error GoTo cmdEmpAdd_Click_Err
Dim ctl As Control

DoCmd.OpenForm "hr_emps_add", acNormal, "", ""

cmdEmpAdd_Click_Exit:
    Exit Sub

cmdEmpAdd_Click_Err:
    MsgBox Error$
    Resume cmdEmpAdd_Click_Exit

End Sub

Private Sub cmdNotCleared_Click()
On Error GoTo cmdNotCleared_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "NotCleared~"

cmdNotCleared_Click_Exit:
    Exit Sub

cmdNotCleared_Click_Err:
    MsgBox Error$
    Resume cmdNotCleared_Click_Exit

End Sub

Private Sub CmdCtrCasesOpen_Click()
On Error GoTo CmdCtrCasesOpen_Click_Err

DoCmd.OpenForm "hr_ctrcases_u", acNormal, "", "", , acNormal, "Open~"

CmdCtrCasesOpen_Click_Exit:
    Exit Sub

CmdCtrCasesOpen_Click_Err:
    MsgBox Error$
    Resume CmdCtrCasesOpen_Click_Exit

End Sub

Private Sub CmdCtrCasesClosed_Click()
On Error GoTo CmdCtrCasesClosed_Click_Err

DoCmd.OpenForm "hr_ctrcases_u", acNormal, "", "", , acNormal, "Closed~"

CmdCtrCasesClosed_Click_Exit:
    Exit Sub

CmdCtrCasesClosed_Click_Err:
    MsgBox Error$
    Resume CmdCtrCasesClosed_Click_Exit

End Sub

Private Sub cmdApplicsActive_Click()
On Error GoTo cmdApplicsActive_Click_Err

DoCmd.OpenForm "hr_applicants", acNormal, "", "", , acHidden, "Active~"

cmdApplicsActive_Click_Exit:
    Exit Sub
cmdApplicsActive_Click_Err:
    MsgBox Error$
    Resume cmdApplicsActive_Click_Exit

End Sub

Private Sub cmdApplicsInactive_Click()
On Error GoTo cmdApplicsInactive_Click_Err

DoCmd.OpenForm "hr_applicants", acNormal, "", "", , acHidden, "Inactive~"

cmdApplicsInactive_Click_Exit:
    Exit Sub
cmdApplicsInactive_Click_Err:
    MsgBox Error$
    Resume cmdApplicsInactive_Click_Exit

End Sub

Private Sub cmdApplicAdd_Click()
On Error GoTo cmdApplicAdd_Click_Err
Dim ctl As Control

DoCmd.OpenForm "hr_applics_detail", acNormal, "", "", acFormAdd, acHidden, "DataEntry~"

cmdApplicAdd_Click_Exit:
    Exit Sub
cmdApplicAdd_Click_Err:
    MsgBox Error$
    Resume cmdApplicAdd_Click_Exit

End Sub

Private Sub cmdPayrol_Click()
On Error GoTo cmdPayrol_Click_Err

DoCmd.OpenReport "hr_payroll", acViewPreview, "", "", acNormal

cmdPayrol_Click_Exit:
    Exit Sub

cmdPayrol_Click_Err:
    MsgBox Error$
    Resume cmdPayrol_Click_Exit

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

Private Sub cmdAttend_Click()
On Error GoTo cmdAttend_Click_Err
Dim dbsAttend As Database
Dim rstAttend As Recordset
Dim dtAttend As Date

'If attendance sheet for the month does not exist, then create new
dtAttend = FirstDateThisMonth(Date)
Set dbsAttend = CurrentDb()
Set rstAttend = dbsAttend.OpenRecordset("Select att_id from hr_attendance Where att_startdt = #" & dtAttend & "#")
If rstAttend.EOF = True Then makeAttendanceSheet (Date)
rstAttend.Close

'Open attendance sheet
DoCmd.OpenForm "hr_attendance_u", acNormal, "", "", , , "~" & Date

cmdAttend_Click_Exit:
    Exit Sub

cmdAttend_Click_Err:
    MsgBox Error$
    Resume cmdAttend_Click_Exit

End Sub

Private Sub cmdApplicsImport_Click()
On Error GoTo cmdApplicsImport_Click_Err

Dim dbs As Database
Dim tdf As TableDef
Set dbs = CurrentDb

Set tdf = dbs.TableDefs("hr_applicants_stage")
tdf.Connect = "Excel 12.0 Xml;HDR=YES;IMEX=2;ACCDB=YES;" & _
                "DATABASE=" & CurrentProject.Path & "\Employment Application Form (Responses).xlsx"
tdf.RefreshLink

DoCmd.OpenForm "hr_applicants_stage", acNormal, "", "", , acNormal

cmdApplicsImport_Click_Exit:
    Exit Sub

cmdApplicsImport_Click_Err:
    MsgBox Error$
    Resume cmdApplicsImport_Click_Exit

End Sub

Private Sub cmdMyUnit_Click()
On Error GoTo cmdMyUnit_Click_Err

DoCmd.OpenForm "start_all_single", acNormal, "", "", , acNormal


cmdMyUnit_Click_Exit:
    Exit Sub

cmdMyUnit_Click_Err:
    MsgBox Error$
    Resume cmdMyUnit_Click_Exit

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

Private Sub boxClearance_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnClearance"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub cmdRevDraft_Click()
On Error GoTo cmdRevDraft_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Draft"

cmdRevDraft_Click_Exit:
    Exit Sub

cmdRevDraft_Click_Err:
    MsgBox Error$
    Resume cmdRevDraft_Click_Exit

End Sub

Private Sub cmdRevOpen_Click()
On Error GoTo cmdRevOpen_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Open"

cmdRevOpen_Click_Exit:
    Exit Sub

cmdRevOpen_Click_Err:
    MsgBox Error$
    Resume cmdRevOpen_Click_Exit

End Sub

Private Sub cmdRevClosed_Click()
On Error GoTo cmdRevClosed_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Closed"

cmdRevClosed_Click_Exit:
    Exit Sub

cmdRevClosed_Click_Err:
    MsgBox Error$
    Resume cmdRevClosed_Click_Exit

End Sub

Private Sub cmdGrades_Click()
On Error GoTo cmdGrades_Click_Err

DoCmd.OpenReport "hr_emps_gradewise_graph", acViewReport, "", "", acNormal


cmdGrades_Click_Exit:
    Exit Sub

cmdGrades_Click_Err:
    MsgBox Error$
    Resume cmdGrades_Click_Exit

End Sub

Private Sub cmdQualifications_Click()
On Error GoTo cmdQualifications_Click_Err

    DoCmd.OpenReport "hr_emps_qualifwise_graph", acViewReport, "", "", acNormal


cmdQualifications_Click_Exit:
    Exit Sub

cmdQualifications_Click_Err:
    MsgBox Error$
    Resume cmdQualifications_Click_Exit

End Sub

Private Sub cmdActiveEmp_Click()
On Error GoTo cmdActiveEmp_Click_Err

DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal


cmdActiveEmp_Click_Exit:
    Exit Sub

cmdActiveEmp_Click_Err:
    MsgBox Error$
    Resume cmdActiveEmp_Click_Exit

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

Private Sub cmdAttachSummary_Click()
DoCmd.OpenReport "hr_attachsummary", acViewReport, "", "", acNormal
End Sub

Private Sub cmdDevices_Click()
DoCmd.OpenReport "hr_emps_u_devices", acViewReport, "", "", acNormal
End Sub

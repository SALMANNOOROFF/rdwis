VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim ctl As Control

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "DataEntry"
        Me.DataEntry = True
        Me.tabEmps.Visible = False
        For Each ctl In Me.Controls
            If ctl.ControlType = acTextBox Then ctl.BorderStyle = 1
            If ctl.ControlType = acComboBox Then
                ctl.BorderStyle = 1
                Me.Controls("box_" & ctl.Name).Visible = False
                End If
            Next ctl
    Case "Current"
        If arrArgs(1) <> "" Then Me.RecordSource = "Select * From hr_emps_u Where emp_id = '" & arrArgs(1) & "'"
        Select Case getVar("varMode")
        Case "approver-m"
            Me.lock.Visible = True
            If getVar("varUnitArea") = "hr" Then Me.cmdReverse.Visible = True
            Me.cmdStatusChange.Visible = True
            If Nz(Me!emp_photodest, "") = "" Then Me.cmdAttachPhoto.Visible = True
            If Me.subExta.Form.CurrentRecord = 0 Then Me.cmdCopyData.Visible = True
        Case "approver-s"
            If Me.emp_locked = 0 Then
                AllowEditsAdvanced Me.Name
                Else
                Me.lblLocked.Visible = True
                End If
            Me.cmdReverse.Visible = True
            If Me.subExta.Form.Recordset.RecordCount = 0 Then Me.subExta.Form.AllowAdditions = True
            If Me.subExtb.Form.Recordset.RecordCount = 0 Then Me.subExtb.Form.AllowAdditions = True
            If Me.subExtc.Form.Recordset.RecordCount = 0 Then Me.subExtc.Form.AllowAdditions = True
            If Me.subJobs.Form.Recordset.RecordCount = 0 Then Me.subJobs.Form.AllowAdditions = True
            If Nz(Me!emp_photodest, "") = "" Then Me.cmdAttachPhoto.Visible = True
        End Select
    Case "Previous"
        If arrArgs(1) <> "" Then Me.RecordSource = "Select * From hr_emps_u Where emp_id = '" & arrArgs(1) & "'"
        Select Case getVar("varMode")
            Case "approver-m"
                If Me!emp_cleared = "0" Then Me.cmdClear.Visible = True
                End Select
    End Select

Me.Visible = True
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdCopyData_Click()
On Error GoTo Error_Handler
Dim dbsCopier As Database
Dim rstCopier As Recordset
Dim qryCopier As QueryDef
Dim strProceed As String
Dim intApplicId As Integer
Dim strStep As String
Dim strStatus As String

Me.Dirty = False
strProceed = MsgBox("Data will be copied in following tabs:" & vbNewLine & _
            "           Personal 1" & vbNewLine & _
            "           Education" & vbNewLine & _
            "           Courses / Certifications" & vbNewLine & _
            "           Career" & vbNewLine & vbNewLine & _
            "Data in other tabs has to be filled manually by the employee." & vbNewLine & vbNewLine & _
            "Do you want to proceed?", vbExclamation + vbYesNo, "Confirmation required")

If strProceed = 0 Then Exit Sub

'Check source for existance of record or for multiple records
Set dbsCopier = CurrentDb()
Set rstCopier = dbsCopier.OpenRecordset("Select apl_id, apl_cnic From hr_applicants Where apl_cnic = '" & Me!emp_cnic & "' And apl_status like 'Active*'", dbOpenSnapshot)
rstCopier.MoveLast
If rstCopier.RecordCount < 1 Then
    MsgBox "No record found in Applicants with matching CNIC."
    Exit Sub
    End If
If rstCopier.RecordCount > 1 Then
    MsgBox "More than one record found in Applicants with matching CNIC. Please mark the undesired record as inactive.", vbCritical
    Exit Sub
    End If
intApplicId = rstCopier!apl_id

'Copy personal data ---------------------------------------------------
strStep = "Personal data"
Set qryCopier = dbsCopier.QueryDefs("hr_applics_empexta_copy")
qryCopier.Parameters("CNIC") = Me!emp_cnic
qryCopier.Parameters("EmployeeId") = Me!emp_id
qryCopier.Execute
If qryCopier.RecordsAffected = 0 Then
    MsgBox "No records added"
    Exit Sub
    End If
strStatus = "Personal data imported - " & qryCopier.RecordsAffected & " record"

'Copy education data --------------------------------------------------------
Set qryCopier = dbsCopier.QueryDefs("hr_applicEdu_qualifs_copy")
qryCopier.Parameters("ApplicId") = intApplicId
qryCopier.Parameters("EmployeeId") = Me!emp_id
qryCopier.Execute
strStatus = strStatus & vbCrLf & "Education data imported - " & qryCopier.RecordsAffected & " record(s)"

'Copy courses / certifications data --------------------------------------------------------
Set qryCopier = dbsCopier.QueryDefs("hr_applicCourses_qualifs_copy")
qryCopier.Parameters("ApplicId") = intApplicId
qryCopier.Parameters("EmployeeId") = Me!emp_id
qryCopier.Execute
strStatus = strStatus & vbCrLf & "Courses data imported - " & qryCopier.RecordsAffected & " record(s)"

'Copy job data --------------------------------------------------------
Set qryCopier = dbsCopier.QueryDefs("hr_applicjobs_jobs_copy")
qryCopier.Parameters("ApplicId") = intApplicId
qryCopier.Parameters("EmployeeId") = Me!emp_id
qryCopier.Execute
strStatus = strStatus & vbCrLf & "Career data imported - " & qryCopier.RecordsAffected & " record"

MsgBox strStatus
Me.emp_id.SetFocus
Me.cmdCopyData.Visible = False
Me.Refresh

Exit Sub
Error_Handler:
MsgBox strStep & " - Error " & Err.Number & "; " & Err.Description & vbCrLf & strStatus
End Sub

Private Sub cmdAttachPhoto_Click()
Dim strTitle As String
strTitle = Me!emp_id & "  -  " & NameComplete(Me!emp_name, Me!emp_title, Me!emp_rank)
FileResponse "emp", Me!emp_id, "Photo", Me!emp_id, Nz(Me!emp_photodest, ""), Me.Name, strTitle
End Sub

Private Sub cmdClear_Click()
Me!emp_cleared = "1"
Me.cmdContracts.SetFocus
Me.cmdClear.Visible = False
MsgBox "Clearence of the employee done.", vbInformation
End Sub

Private Sub cmdContracts_Click()
On Error GoTo cmdContracts_Click_Err

Me.Dirty = False
DoCmd.OpenForm "hr_contracts", acNormal, "", "", , acHidden, "One Record~" & Me!emp_id


cmdContracts_Click_Exit:
    Exit Sub

cmdContracts_Click_Err:
    MsgBox Error$
    Resume cmdContracts_Click_Exit

End Sub

Private Sub cmdEntry_Click()
On Error GoTo cmdEntry_Click_Err

Me.Dirty = False
Forms!vars.Parameter1 = Me!emp_id
DoCmd.OpenReport "hr_gkaentry_doc", acViewReport


cmdEntry_Click_Exit:
    Exit Sub

cmdEntry_Click_Err:
    MsgBox Error$
    Resume cmdEntry_Click_Exit

End Sub

Private Sub cmdPer_Click()
On Error GoTo cmdPer_Click_Err

Me.Dirty = False
Forms!vars.Parameter1 = Me!emp_id
DoCmd.OpenReport "hr_personnel_doc", acViewReport


cmdPer_Click_Exit:
    Exit Sub

cmdPer_Click_Err:
    MsgBox Error$
    Resume cmdPer_Click_Exit

End Sub

Private Sub cmdEval_Click()
On Error GoTo cmdEval_Click_Err

Me.Dirty = False
Forms!vars.Parameter1 = Me!emp_id
DoCmd.OpenReport "hr_evaluation_doc", acViewReport, "", "", acNormal


cmdEval_Click_Exit:
    Exit Sub

cmdEval_Click_Err:
    MsgBox Error$
    Resume cmdEval_Click_Exit

End Sub

Private Sub cmdReverse_Click()
Dim intResponse As Integer
Dim lngRevId As Long

Select Case Me!emp_status
    Case "Active", "Active (on notice)"
        DoCmd.OpenForm "hr_emps_rev", acNormal, "", "", , acHidden
    Case Else
        intResponse = MsgBox("A Reversal Request for Emp " & Me!emp_id & " will be generated. Do you want to continue?", 4, "Confirmation")
        If intResponse <> 6 Then Exit Sub
        
        lngRevId = CreateDataRevision("Employee", Me!emp_id, Me!emp_unt_id, 1)
        DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
        DoCmd.Close acForm, "hr_emps_detail"
        DoCmd.Close acForm, "hr_emps_u"
    End Select
End Sub

Private Sub lock_Click()
If Me.emp_locked = 0 Then
    Me.emp_locked = 1
    Else
    Me.emp_locked = 0
    End If
Me.Dirty = False
Me.lock.Requery
End Sub


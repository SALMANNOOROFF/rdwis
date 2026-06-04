VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_nrdi_multiple"
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

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub


Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
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

Private Sub frmChoose_AfterUpdate()
Select Case Me.frmChoose
    Case 1
        Me.cmbProjects.Visible = True
        Me.frmStatusProjects.Visible = False
        Me.frmStatusEmps.Visible = False
        Me.frmStatusPcs.Visible = False
        Me.frmStatusAccounts.Visible = False
        Me.frmChoose.Width = 3430
        Me.frmChoose.Left = 3855
    Case 2
        Me.cmbProjects.Visible = False
        Me.frmStatusProjects.Visible = True
        Me.frmStatusEmps.Visible = True
        Me.frmStatusPcs.Visible = True
        Me.frmStatusAccounts.Visible = True
        Me.frmChoose.Width = 4130
        Me.frmChoose.Left = 3255
    End Select
End Sub

Private Sub cmdProjects_Click()
Select Case Me.frmChoose
    Case 1
        Select Case Nz(Me.cmbProjects, 0)
            Case 0
                MsgBox "Please select a project.", vbCritical
                Exit Sub
            Case Else
                DoCmd.OpenForm "prj_projects_one", acNormal, "", "", , acHidden, "One~" & Me.cmbProjects
            End Select
    Case 2
        Select Case Me.frmStatusProjects
            Case 1
                DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Open-Approved"
            Case 2
                DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Closed-Approved"
            End Select
    End Select
End Sub

Private Sub cmdEmployees_Click()
Select Case Me.frmChoose
    Case 1
        Select Case Nz(Me.cmbProjects, 0)
            Case 0
                MsgBox "Please select a project.", vbCritical
                Exit Sub
            Case Else
                DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "ProjectWise~" & Me.cmbProjects
            End Select
    Case 2
        Select Case Me.frmStatusEmps
            Case 1
                DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"
            Case 2
                DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Previous~"
            End Select
        End Select
End Sub

Private Sub cmdPurCases_Click()
Select Case Me.frmChoose
    Case 1
        Select Case Nz(Me.cmbProjects, 0)
            Case 0
                MsgBox "Please select a project.", vbCritical
                Exit Sub
            Case Else
                DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "ProjectWise~" & Me.cmbProjects
            End Select
    Case 2
        Select Case Me.frmStatusPcs
            Case 1
                DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Open"
            Case 2
                DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Closed"
            End Select
        End Select
End Sub

Private Sub cmdAccounts_Click()
Select Case Me.frmChoose
    Case 1
        Select Case Nz(Me.cmbProjects, 0)
            Case 0
                MsgBox "Please select a project.", vbCritical
                Exit Sub
            Case Else
                Forms!vars!Parameter1 = Me.cmbProjects
                DoCmd.OpenForm "fin_headstatus", acNormal, "", "", , acHidden
            End Select
    Case 2
        Select Case Me.frmStatusAccounts
            Case 1
                DoCmd.OpenForm "fin_headstatusall", acNormal, "", "", , acNormal
            Case 2
            End Select
        End Select
End Sub

Private Sub cmdLoans_Click()
OpenLoansForm
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

Private Sub cmdPurchaseFirms_Click()
On Error GoTo cmdPurchaseFirms_Click_Err

DoCmd.OpenReport "fin_firms_overall_graph", acViewReport, "", "", acNormal


cmdPurchaseFirms_Click_Exit:
    Exit Sub

cmdPurchaseFirms_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirms_Click_Exit

End Sub

Private Sub cmdPurchaseFirmsDetailed_Click()
On Error GoTo cmdPurchaseFirmsDetailed_Click_Err

DoCmd.OpenReport "fin_firms_projectwise", acViewReport, "", "", acNormal

cmdPurchaseFirmsDetailed_Click_Exit:
    Exit Sub

cmdPurchaseFirmsDetailed_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirmsDetailed_Click_Exit

End Sub

Private Sub cmdPurchaseFirmSingle_Click()

If Nz(Me.cmbFirms, "") = "" Then
    MsgBox "Please select a firm.", vbCritical
    Exit Sub
    End If

DoCmd.Close acForm, "frm_firm_choose"
Forms!vars!Parameter1 = Me.cmbFirms
Forms!vars!Parameter2 = Me.cmbFirms.Column(1)
DoCmd.OpenReport "fin_firms_one_graph", acViewReport, "", "", acNormal
End Sub

Private Sub cmdAllocations_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenAllocationsReport "Open", Me.cmbUnit
End Sub

Private Sub cmdAccountsStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenAccountsStatusReport "Open", Me.cmbUnit
End Sub

Private Sub cmdProjSharesStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenProjectSharesReport "Open", Me.cmbUnit
End Sub

Private Sub cmdChrf_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenChrfReport "Open", Me.cmbUnit
End Sub

Private Sub cmdSubheadsStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select a department.", vbCritical
    Exit Sub
    End If
If Me.cmbUnit = 0 Then
    MsgBox "This report is not available for all departments. Please select one department.", vbCritical
    Exit Sub
    End If
OpenSubheadsStatusReport "Open", Me.cmbUnit
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





VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim strMode As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Current"
        Me.lblTitle.Caption = "Current Employees"
    Case "Previous"
        Me.RecordSource = "Select * From hr_emps_u Where emp_status Not like 'Active*'"
        Me.lblTitle.Caption = "Previous Employees"
    Case "NotCleared"
        Me.RecordSource = "hr_emps_u_notcleared"
        Me.lblTitle.Caption = "Previous Employees - Clearance Awaited"
    Case "warnGatePassNone"
        Me.RecordSource = "hr_warnGatePassNone"
        Me.lblTitle.Caption = "Employees without gate pass"
    Case "warnGatePassExpiry"
        Me.RecordSource = "hr_warnGatePassExpiry"
        Me.lblTitle.Caption = "Employees - Gate pass expiry"
        Me.emp_cexpdt.Visible = True
        Me.lblemp_cexpdt.Visible = True
    Case "warnContractNone"
        Me.RecordSource = "hr_warnContractNone"
        Me.lblTitle.Caption = "Employees without first contract"
    Case "warnContractExpiry"
        Me.RecordSource = "hr_warnContractExpiry"
        Me.lblTitle.Caption = "Employees - Contract Expiry"
        Me.enddt.Visible = True
        Me.lblctr_enddt.Visible = True
    Case "warnPersonala"
        Me.RecordSource = "hr_warnPersonala"
        Me.lblTitle.Caption = "Employees - 'Personal 1' data missing"
    Case "warnPersonala"
        Me.RecordSource = "hr_warnPersonalb"
        Me.lblTitle.Caption = "Employees - 'Personal 2' data missing"
    Case "warnClearance"
        Me.RecordSource = "hr_warnClearance"
        Me.lblTitle.Caption = "Employees - Clearance Awaited"
    Case "ProjectWise"
        Me.RecordSource = "Select * From hr_emps Where emp_hed_id = " & arrArgs(1)
        Me.lblTitle.Caption = "Employees - Single Project"
    Case "SalReqsAwaited"
        Me.RecordSource = arrArgs(1)
        Me.lblTitle.Caption = "Employees - Salary Requisitions Awaited"
    End Select

strMode = getVar("varMode")
If strMode Like "*-s" Then Me.cmdAddCtrCaseHiring.Visible = True
Me.Visible = True
End Sub

Private Sub cmdCtr_Click()
Dim strFormName As String
Dim strArgs As String
Dim strUnitArea As String
Dim strMode As String

strUnitArea = getVar("varUnitArea")
strMode = getVar("varMode")
Select Case True
    Case strMode Like "*-s", strUnitArea = "rdw", strUnitArea = "hr"
        strFormName = "hr_emps_detail"
        strArgs = IIf(Me!emp_status Like "Active*", "Current", "Previous") & "~" & Me!emp_id
    Case Else
        strFormName = "hr_contracts"
        strArgs = "One Record~" & Me!emp_id
    End Select

DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden, strArgs
End Sub

Private Sub cmdAddCtrCaseHiring_Click()
On Error GoTo cmdAddCtrCaseHiring_Click_Err

DoCmd.OpenForm "hr_ctrcasehg_add", acNormal, "", "", acFormAdd, acWindowNormal, "New~"

cmdAddCtrCaseHiring_Click_Exit:
    Exit Sub

cmdAddCtrCaseHiring_Click_Err:
    MsgBox Error$
    Resume cmdAddCtrCaseHiring_Click_Exit

End Sub



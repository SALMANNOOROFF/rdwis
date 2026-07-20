VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_applicants"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Active"
        Me.lblTitle.Caption = "Active Applicants"
    Case "Inactive"
        Me.RecordSource = "Select * FROM hr_applicants WHERE apl_status <> 'Active'"
        Me.lblTitle.Caption = "Inactive Applicants"
    End Select
Me.Visible = True
End Sub

Private Sub emp_namewithrank_DblClick(Cancel As Integer)
Dim ctl As Control

DoCmd.OpenForm "hr_applics_detail", acNormal, "", "", , acHidden, "Previous,"
Forms!hr_applics_detail.RecordSource = "Select * from hr_applicants Where apl_id = " & Me!apl_id
Forms!hr_applics_detail.AllowEdits = True
For Each ctl In Forms!hr_applics_detail.Controls
    If ctl.ControlType = acSubform Then
        ctl.Form.AllowAdditions = True
        ctl.Form.AllowEdits = True
        End If
    Next ctl
Forms!hr_applics_detail.Visible = True
End Sub


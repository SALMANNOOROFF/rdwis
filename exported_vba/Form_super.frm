VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_super"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdGo_Click()
On Error GoTo Error_Handler

Dim strAcc As String
Dim strStrAccessHr As String
strAcc = Me.cmbAcc.Value
DoCmd.Close
If strAcc = "" Then Exit Sub
DoCmd.OpenForm "vars", acNormal, "", "", , acHidden
SetStaticVariables (strAcc)

'Open appropriate form
Dim strFormName As String

Select Case getVar("varRoleAccess")
    Case "multiple"
        DoCmd.OpenForm "start_" & getVar("varUnitArea") & "_multiple", acNormal, "", "", , acHidden
    Case "single"
        DoCmd.OpenForm "start_all_single", acNormal, "", "", , acHidden
    End Select
    
Exit Sub
Error_Handler:
MsgBox Err.Description
End Sub



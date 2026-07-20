VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_firm_choose"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdOK_Click()
Dim strFirm As String

If Nz(Me.cmbFirms, "") = "" Then
    MsgBox "Please select a firm.", vbCritical
    Exit Sub
    End If

strFirm = Me.cmbFirms
DoCmd.Close acForm, "frm_firm_choose"
Forms!vars!Parameter1 = strFirm
DoCmd.OpenReport "fin_firms_one", acViewReport, "", "", acNormal


End Sub

Private Sub cmdCancel_Click()
DoCmd.Close acForm, "frm_firm_choose"
End Sub

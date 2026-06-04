VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_source"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub cmdOK_Click()
Dim intType As Integer
Dim intSource As Integer

intType = Me.frmPurCaseType
intSource = 2   'Me.frmPurCaseSource
DoCmd.Close acForm, "pur_purcases_source"
Select Case intSource
    Case 1
    'DoCmd.OpenForm
    Case 2
    Select Case intType
        Case 1
        DoCmd.OpenForm "pur_purcases_add", acNormal, "", "", , acNormal, "DataEntry~" & intType
        Case 2
        DoCmd.OpenForm "pur_purcasespetty_add", acNormal, "", "", , acNormal, "DataEntry~" & intType
        Case 3
        DoCmd.OpenForm "pur_purcasestada_add", acNormal, "", "", , acNormal, "DataEntry~" & intType
        End Select
    End Select
End Sub


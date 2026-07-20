VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_sharesinstall_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdReverse_Click()
Dim strStatus As String
DoCmd.OpenForm "fin_sharesinstall_rev", acNormal, "", "", , acHidden
End Sub

Private Sub Form_BeforeUpdate(Cancel As Integer)
'If Me.txtCheck <> 0 Then
'    Cancel = True
'    MsgBox "Equipment, HR and Misc amounts should add upp to RDW share."
'    Me.Undo
'    End If
End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_extcompenses_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdAddExtCompense_Click()
On Error GoTo cmdAddExtCompense_Click_Err

DoCmd.OpenForm "fin_extcompenses_add", acNormal, "", "", , acNormal

cmdAddExtCompense_Click_Exit:
    Exit Sub

cmdAddExtCompense_Click_Err:
    MsgBox Error$
    Resume cmdAddExtCompense_Click_Exit

End Sub

Private Sub Form_Activate()
Me.Refresh
End Sub

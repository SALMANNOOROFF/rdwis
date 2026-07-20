VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_it_multiple"
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

Private Sub cmdAccountsOpen_Click()
On Error GoTo cmdAccountsOpen_Click_Err

DoCmd.OpenForm "cen_accounts", acNormal, "", "", , acHidden, "Open"

cmdAccountsOpen_Click_Exit:
    Exit Sub

cmdAccountsOpen_Click_Err:
    MsgBox Error$
    Resume cmdAccountsOpen_Click_Exit
End Sub

Private Sub cmdAccountsClosed_Click()
On Error GoTo cmdAccountsClosed_Click_Err

DoCmd.OpenForm "cen_accounts", acNormal, "", "", , acHidden, "Closed"


cmdAccountsClosed_Click_Exit:
    Exit Sub

cmdAccountsClosed_Click_Err:
    MsgBox Error$
    Resume cmdAccountsClosed_Click_Exit

End Sub

Private Sub cmdAccountAddxx_Click()
On Error GoTo cmdAccountAddxx_Click_Err

DoCmd.OpenForm "cen_accounts_detail_add", acNormal, "", "", , acNormal


cmdAccountAddxx_Click_Exit:
    Exit Sub

cmdAccountAddxx_Click_Err:
    MsgBox Error$
    Resume cmdAccountAddxx_Click_Exit

End Sub



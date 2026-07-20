VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_accounts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub Form_Open(Cancel As Integer)
Select Case Me.OpenArgs
    Case "Open"
        Me.RecordSource = "cen_accounts_open"
        Me.lblTitle.Caption = "Accounts Open"
    Case "Closed"
        Me.RecordSource = "cen_accounts_closed"
        Me.lblTitle.Caption = "Accounts Closed"
    End Select
Me.Visible = True
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdAccs_Click()
DoCmd.OpenForm "cen_accounts_detail", acNormal, "", "", , acHidden, Me.acc_id & "~" & Me.acc_status
End Sub



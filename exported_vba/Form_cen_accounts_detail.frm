VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_accounts_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From cen_accounts where acc_id = " & arrArgs(0)
Select Case arrArgs(1)
    Case "Active"
        Me.cmdResetPass.Visible = True
        Me.cmdCloseAccount.Visible = True
    Case "Inactive"
    Case "Closed"
    End Select
Me.Visible = True
End Sub


Private Sub cmdCloseAccount_Click()
Dim intResponse As Integer
intResponse = MsgBox("The account will be closed and the user will no longer be able to access the system. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub
Me.acc_status = "Closed"
Me.acc_enddt = GetNow()
MsgBox "The Account is closed.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdResetPass_Click()
Dim intResponse As Integer
intResponse = MsgBox("The account password will be reset. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub
ResetPassword (Me.acc_username)
MsgBox "The password has been reset", vbInformation
Me.Refresh
End Sub

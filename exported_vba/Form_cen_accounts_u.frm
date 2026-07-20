VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_accounts_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.acc_id = getVar("varAccId")
Me.acc_name = getVar("varAccName")
Me.acc_rank = getVar("varAccRank")
Me.acc_title = getVar("varAccTitle")
Me.acc_desig = getVar("varRoleDesig")
Me.acc_username = getVar("varAccUsername")
End Sub

Private Sub cmdChangeRank_Click()
Dim dbsRank As Database
Dim rstRank As Recordset
Dim strRank As String

strRank = InputBox("Please enter new rank.", "Input required", " ")
If strRank = "" Then Exit Sub

Set dbsRank = CurrentDb()
Set rstRank = dbsRank.OpenRecordset("Select acc_rank From cen_accounts Where acc_id = " & Me.acc_id)
rstRank.Edit
rstRank!acc_rank = IIf(strRank = " ", Null, Trim(strRank))
rstRank.Update
Me.acc_rank = Trim(strRank)
SetStaticVariables (Me.acc_id)
End Sub

Private Sub cmdChangeTitle_Click()
Dim dbsTitle As Database
Dim rstTitle As Recordset
Dim strTitle As String

strTitle = InputBox("Please enter new title.", "Input required", " ")
If strTitle = "" Then Exit Sub

Set dbsTitle = CurrentDb()
Set rstTitle = dbsTitle.OpenRecordset("Select acc_title From cen_accounts Where acc_id = " & Me.acc_id)
rstTitle.Edit
rstTitle!acc_title = IIf(strTitle = " ", Null, Trim(strTitle))
rstTitle.Update
Me.acc_title = Trim(strTitle)
SetStaticVariables (Me.acc_id)
End Sub


Private Sub cmdChangePass_Click()
On Error GoTo Error_Handler
Dim strUser As String
Dim strPass As String

strUser = InputBoxDK("Please enter username.", "Change password")
If strUser = "" Then Exit Sub

strPass = InputBoxDK("Please enter current password.", "Change password")
If strPass = "" Then Exit Sub

'Identification
Dim rstIdentity As Recordset
Set rstIdentity = CurrentDb.OpenRecordset("Select acc_pass From cen_accounts Where acc_id =" & getVar("varAccId"))
If StoreEncryptAES(strUser, strPass, 5) <> rstIdentity!acc_pass Then
    MsgBox "Incorrect password."
    Exit Sub
    End If

'Password renewal
Dim strPassRepeat As String
Dim intAccess As Integer
Renew_Password:
Do
    strPass = InputBoxDK("Please enter new password.", "Change password")
    If strPass = "12345" Then MsgBox "Pasword '12345' is not allowed."
    If strPass = "" Then Exit Sub
    Loop While strPass = "12345"
strPassRepeat = InputBoxDK("Please repeat your password.", "Change password")
If strPass <> strPassRepeat Then
    MsgBox "Passwords donot match. Please try again."
    GoTo Renew_Password
    End If
rstIdentity.Edit
rstIdentity!acc_pass = StoreEncryptAES(strUser, strPassRepeat, 5)
rstIdentity.Update
MsgBox "Your password has been changed successfully.", vbInformation
rstIdentity.Close
Set rstIdentity = Nothing

The_End:
Exit Sub

Error_Handler:
MsgBox Error$

End Sub




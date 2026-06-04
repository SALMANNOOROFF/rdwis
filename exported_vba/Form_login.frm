VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_login"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Binary
Option Explicit

Private Sub Form_Open(Cancel As Integer)

'Prepare for production (run only for the first time for accde file)
If CurrentDb().Name Like "*.accde" And CurrentDb().Properties("ReadyForDeployment") = False Then
    ProtectDb
    If ChangeDbConnections() = False Then
        Cancel = True
        MsgBox "Security Error"
        Exit Sub
        End If
    CurrentDb().Properties("ReadyForDeployment") = True
    End If

'Check system date format
If Not Date Like "##-[A-Z][a-z][a-z]-##*" Then
    MsgBox "Incompatible system date format. Please set short date format to " & Format(Date, "dd-mmm-yy") & " in system settings."
    Cancel = True
    End If

'Establish link with database and provide password
MakeFirstConnection

''Check system date/time
'If Abs(GetNow() - Now) > 5 / 24 / 60 Then
'    MsgBox "Incorrect system date/time. Please set correct date/time in system settings."
'    Cancel = True
'    End If

'Verify if software version is allowed
If IsVersionAllowed() = False Then
    MsgBox "Version conflict has occured. Please contact IS department.", vbCritical
    Cancel = True
    End If

End Sub

Private Sub cmdLogin_Click()
PressLogin
End Sub

Private Sub txtPassword_KeyDown(KeyCode As Integer, Shift As Integer)
If KeyCode = 13 Then
    Me.cmdLogin.SetFocus    'To update the password field
    PressLogin
    End If
End Sub

Private Sub PressLogin()
On Error GoTo cmdLogin_Click_Err

'No user name or password
If IsNull(Me.txtUserName) Or IsNull(Me.txtPassword) Then
    MsgBox "Please enter username and password."
    Exit Sub
    End If
    
'Authentication
Dim rstIdentity As Recordset
Set rstIdentity = CurrentDb.OpenRecordset("Select * From cen_accounts Where acc_username = '" & Me.txtUserName & "' And acc_status <> 'Closed'")
If rstIdentity.EOF = True Then
    MsgBox "Identification failed.", vbCritical
    DoCmd.Close
    Exit Sub
    End If
If rstIdentity!acc_status <> "Active" Then
    MsgBox "You account is not active. Please contact system admin for account activation.", vbCritical
    DoCmd.Close
    Exit Sub
    End If
If StoreEncryptAES(Me.txtUserName, Me.txtPassword, 5) <> rstIdentity!acc_pass Then
    MsgBox "Identification failed.", vbCritical
    DoCmd.Close
    Exit Sub
    End If

''******* Obsolete. Now there is only one frontend ********
''Authorization
'Dim rstAuth As Recordset
'Debug.Print "Select fte_depts From frontends Where fte_frontend = '" & CurrentDb.Properties("FrontEndType") & "'"
'Set rstAuth = CurrentDb.OpenRecordset("Select fte_dpts From frontends Where fte_frontend = '" & CurrentDb.Properties("FrontEndType") & "'")
'If InStr(rstAuth!fte_dpts, rstIdentity!acc_untid) = 0 Then
'    MsgBox "Authorization denied. Please contact system administrator.", vbCritical
'    DoCmd.Close
'    Exit Sub
'    End If
'rstAuth.Close
'Set rstAuth = Nothing

'Password renewal for first idenditification
If Me.txtPassword = "12345" Then
Dim strPass As String
Dim strPassRepeat As String
Dim intAccess As Integer
Renew_Password:
    Do
        strPass = InputBoxDK("Your password needs to be changed. Please enter new password.", "Change password")
        If strPass = "12345" Then MsgBox "Pasword '12345' is not allowed."
        If strPass = "" Then Exit Sub
        Loop While strPass = "12345"
    strPassRepeat = InputBoxDK("Please repeat your password.", "Change password")
    If strPass <> strPassRepeat Then
        MsgBox "Passwords donot match. Please try again.", vbCritical
        GoTo Renew_Password
        End If
    rstIdentity.Edit
    rstIdentity!acc_pass = StoreEncryptAES(Me.txtUserName, strPassRepeat, 5)
    rstIdentity.Update
    MsgBox "Your password has been changed successfully.", vbInformation
    End If
    
intAccess = rstIdentity!acc_id
rstIdentity.Close
Set rstIdentity = Nothing
DoCmd.Close

'Set state for the user
DoCmd.OpenForm "vars", acNormal, "", "", , acHidden
SetStaticVariables (intAccess)

'Open appropriate form
Dim strFormName As String
Select Case getVar("varRoleAccess")
    Case "multiple"
        DoCmd.OpenForm "start_" & getVar("varUnitArea") & "_multiple", acNormal, "", "", , acHidden
    Case "single"
        DoCmd.OpenForm "start_all_single", acNormal, "", "", , acHidden
    End Select

cmdLogin_Click_Exit:
    Exit Sub
cmdLogin_Click_Err:
    If Err.Number = 3151 Then
        MsgBox "User name or password is incorrect."
        Else
        MsgBox Error$ & "--" & Err.Number
        End If
    Resume cmdLogin_Click_Exit
End Sub





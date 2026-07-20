VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_accounts_detail_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub acc_desig_AfterUpdate()
Dim dbsAcc As Database
Dim rstAcc As Recordset

Set dbsAcc = CurrentDb()
Set rstAcc = dbsAcc.OpenRecordset("Select * From cen_roles Where rol_desig = '" & Me.acc_desig & "'")
Me.acc_desigshort = rstAcc!rol_desigshort
Me.acc_desigtype = rstAcc!rol_desigtype
Me.acc_access = rstAcc!rol_access
Me.acc_auth = rstAcc!rol_auth
Me.acc_level = rstAcc!rol_level
Me.acc_reportlevel = rstAcc!rol_reportlevel
Me.acc_unt_id = rstAcc!rol_unt_id

Set rstAcc = dbsAcc.OpenRecordset("Select * From cen_units Where unt_id = " & Me.acc_unt_id)
Me.acc_unttype = rstAcc!unt_type
Me.acc_untname = rstAcc!unt_name
Me.acc_untnamesh = rstAcc!unt_namesh
Me.acc_untarea = rstAcc!unt_area
Me.acc_lowerm = rstAcc!unt_lowerm
Me.acc_upperm = rstAcc!unt_upperm
Me.acc_lowers = rstAcc!unt_lowers
Me.acc_uppers = rstAcc!unt_uppers
End Sub

Private Sub acc_username_AfterUpdate()
Me.acc_username = Trim(Me.acc_username)
If Me.acc_username = "" Then
    Me.acc_pass = ""
    Else
    Me.acc_pass = StoreEncryptAES(Me.acc_username, "12345", 5)
    End If
End Sub

Private Sub cmdAdd_Click()
Dim dbsAdd As Database
Dim rstAdd As Recordset
Dim intSuffix As Integer
Dim strAltUsername  As String

'Check Data
If Nz(Me.acc_username, "") = "" Or Nz(Me.acc_name, "") = "" Or Nz(Me.acc_desig, "") = "" Then
    MsgBox "Username, Name and Designation are required.", vbCritical
    Exit Sub
    End If
    
If Len(Me.acc_username) < 4 Then
    MsgBox "Username has to be atleast 4 caharacters long.", vbCritical
    Exit Sub
    End If

'Check if there is an open account for current role
Set dbsAdd = CurrentDb()
Set rstAdd = dbsAdd.OpenRecordset("Select acc_username from cen_accounts Where acc_status = 'Active' And acc_desig = '" & Me.acc_desig & "'", dbOpenSnapshot)
If Not rstAdd.EOF Then
    MsgBox "An account for role '" & Me.acc_desig & "' is already open. First close the account to create a new one.", vbCritical
    Exit Sub
    End If

'Check if username already exists
intSuffix = 1
strAltUsername = Me.acc_username
Do
    Set rstAdd = dbsAdd.OpenRecordset("Select acc_username from cen_accounts Where acc_username = '" & strAltUsername & "'", dbOpenSnapshot)
    If Not rstAdd.EOF Then
        intSuffix = intSuffix + 1
        strAltUsername = Me.acc_username & intSuffix
        End If
    Loop While Not rstAdd.EOF

If Me.acc_username <> strAltUsername Then
    MsgBox "The username '" & Me.acc_username & "' already exists in the database. Username '" & strAltUsername & "' is available. To use this username press 'Add' again.", vbCritical
    Me.acc_username = strAltUsername
    Me.acc_pass = StoreEncryptAES(Me.acc_username, "12345", 5)
    Exit Sub
    End If

'Add Account
Set rstAdd = dbsAdd.OpenRecordset("cen_accounts")
With rstAdd
    .AddNew
    !acc_type = Me.acc_type
    !acc_username = Me.acc_username
    !acc_pass = Me.acc_pass
    !acc_startdt = GetNow()
    !acc_status = Me.acc_status
    !acc_name = Me.acc_name
    If Me.acc_title <> "" Then !acc_title = Me.acc_title
    If Me.acc_rank <> "" Then !acc_rank = Me.acc_rank
    !acc_desig = Me.acc_desig
    !acc_desigshort = Me.acc_desigshort
    !acc_desigtype = Me.acc_desigtype
    !acc_access = Me.acc_access
    !acc_auth = Me.acc_auth
    !acc_level = Me.acc_level
    !acc_reportlevel = Me.acc_reportlevel
    !acc_unt_id = Me.acc_unt_id
    !acc_unttype = Me.acc_unttype
    !acc_untname = Me.acc_untname
    !acc_untnamesh = Me.acc_untnamesh
    !acc_untarea = Me.acc_untarea
    !acc_lowerm = Me.acc_lowerm
    !acc_upperm = Me.acc_upperm
    !acc_lowers = Me.acc_lowers
    !acc_uppers = Me.acc_uppers
    .Update
    End With
MsgBox "User '" & Me.acc_username & "' has been added. The default password is '12345'. The user is to be instructed to login at the earliest.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

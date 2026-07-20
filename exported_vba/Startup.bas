Attribute VB_Name = "Startup"
Option Compare Database
Option Explicit
Private Declare Function GetSystemMenu Lib "user32" (ByVal hwnd As Long, ByVal wRevert As Long) As Long
Private Declare Function EnableMenuItem Lib "user32" (ByVal hMenu As Long, ByVal wIDEnableItem As Long, ByVal wEnable As Long) As Long

Public Sub StartupSteps(FormName As String)
On Error GoTo Err_Form_Open:

Dim objForm As Form
Set objForm = Forms(FormName)


If FormName Like "*single" And getVar("varRoleAccess") = "multiple" Then GoTo User_Settings

Application_settings: '----------------'
Call AccessCloseButtonEnabled(False)
Application.SetOption "Default Find/Replace Behavior", 0
If Application.CommandBars("Ribbon").Height > 100 Then CommandBars.ExecuteMso "MinimizeRibbon"
'DoCmd.ShowToolbar "Ribbon", acToolbarNo
DoCmd.SelectObject acForm, FormName
DoCmd.Maximize

User_Settings: '------------------------"
SetDynamicVariables Mid(FormName, InStrRev(FormName, "_") + 1, 1)
objForm.lblVersion.Caption = GetVersion()
If FormName Like "*single" Then objForm.lblTitle.Caption = UCase(getVar("varUnitNameShort") & " " & getVar("varUnitType"))
objForm.lblUser.Caption = getVar("varRoleDesigShort") & " - " & RankFirstPart(getVar("varAccRank")) & " " & getVar("varAccTitle") & " " & getVar("varAccName") & IIf(getVar("varAccRank") Like "*PN", " PN", "")
If CurrentDb().Properties("AllowBypassKey").Value = False Then objForm.boxSecurity.Visible = False
Select Case getVar("varDatabase")
    Case "Development": objForm.txtDbType = "Dev"
    Case "Training": objForm.txtDbType = "Trn"
    End Select

Exit_Form_Open:
Exit Sub
Err_Form_Open:
MsgBox Error$
End Sub

'Set AllowBypassKey property to false (To be run for the first startup for accde file)
Public Sub ProtectDb()
On Error GoTo Error_Handler

Dim dbs As Database
Dim prp As Property
Set dbs = CurrentDb()
dbs.Properties("AllowBypassKey").Value = False

Exit Sub
Error_Handler:
If Err.Number = 3270 Then    'If AllowBypassKey property doesnot exist, create it and initialize as false
    Set prp = dbs.CreateProperty("AllowBypassKey", dbBoolean, False)
    dbs.Properties.Append prp
    End If
End Sub

'Change path from dev to required database (To be run only for the first startup for accde file)
Public Function ChangeDbConnections() As Boolean
On Error GoTo Error_Handler_C

Dim dbsC As Database
Dim tdfC As TableDef
Dim qdfC As QueryDef
Dim strString As String
Dim fileName As String

'Provide conword for connections
Set dbsC = CurrentDb()
dbsC.Properties("ConWord").Value = InputBoxDK("Please enter the connection string", "Input required")

'Connect each table to production database
fileName = Application.CurrentProject.Name
strString = dbsC.Properties("ConWord").Value
For Each tdfC In dbsC.TableDefs
    If Len(tdfC.Connect) > 1 Then               'Only modify linked tables
        If Left(tdfC.Connect, 4) = "ODBC" Then  'Only modify ODBC tables
            If InStr(tdfC.Connect, "DATABASE=dev;SERVER=localhost") > 0 Then
                If fileName Like "RDWIS*" Then _
                    tdfC.Connect = Replace(tdfC.Connect, "DATABASE=dev;SERVER=localhost", "DATABASE=rdw;SERVER=10.120.29.100")
                If fileName Like "Trainer*" Then _
                    tdfC.Connect = Replace(tdfC.Connect, "DATABASE=dev;SERVER=localhost", "DATABASE=trn;SERVER=10.120.29.100")
                End If
            tdfC.Connect = tdfC.Connect & "; UID=user1; PWD=" & strString
            tdfC.RefreshLink
            End If
        End If
    Next tdfC

'Connect pass through queries to production database
For Each qdfC In dbsC.QueryDefs
    If Len(qdfC.Connect) > 1 Then               'Only modify linked tables
        If Left(qdfC.Connect, 4) = "ODBC" Then  'Only modify ODBC tables
            If InStr(qdfC.Connect, "DATABASE=dev;SERVER=localhost") > 0 Then
                If fileName Like "RDWIS*" Then _
                    qdfC.Connect = Replace(qdfC.Connect, "DATABASE=dev;SERVER=localhost", "DATABASE=rdw;SERVER=10.120.29.100")
                If fileName Like "Trainer*" Then _
                    qdfC.Connect = Replace(qdfC.Connect, "DATABASE=dev;SERVER=localhost", "DATABASE=trn;SERVER=10.120.29.100")
                End If
            End If
        End If
    Next qdfC

ChangeDbConnections = True

The_End:
Exit Function
Error_Handler_C:
ChangeDbConnections = False
MsgBox "Connection with target database not established."
End Function

'Makes first connection with database (To be run every time)
Public Sub MakeFirstConnection()
Dim dbs As Database
Dim tdf As TableDef
Dim strString As String

Set dbs = CurrentDb
Set tdf = dbs.TableDefs("cen_units")
strString = dbs.Properties("ConWord").Value
tdf.Connect = tdf.Connect & "; UID=user1; PWD=" & strString
tdf.RefreshLink
End Sub

'Public Function IsVersionAllowed() As Boolean
'Dim dbsVer As Database
'Dim rstVer1 As Recordset
'Dim rstVer2 As Recordset
'
'Set dbsVer = CurrentDb()
'Set rstVer1 = dbsVer.OpenRecordset("cen_version", dbOpenSnapshot)
'Set rstVer2 = dbsVer.OpenRecordset("version_local", dbOpenSnapshot)
'If rstVer2!Version >= rstVer1!ver_compat And rstVer2!Version <= rstVer1!ver_version Then
'    IsVersionAllowed = True
'    Else
'    IsVersionAllowed = False
'    End If
'
'End Function

Public Function IsVersionAllowed() As Boolean
Dim dbsVer As Database
Dim rstVer1 As Recordset
Dim rstVer2 As Recordset

Set dbsVer = CurrentDb()
Set rstVer1 = dbsVer.OpenRecordset("cen_version", dbOpenDynaset)
Set rstVer2 = dbsVer.OpenRecordset("version_local", dbOpenSnapshot)
Select Case rstVer2!Version
    Case Is < rstVer1!ver_version   'Front-end version is older
        IsVersionAllowed = False
    Case Is = rstVer1!ver_version   'Front-end version is current
        IsVersionAllowed = True
    Case Is > rstVer1!ver_version   'Front-end version is newer
        rstVer1.Edit
        rstVer1!ver_version = rstVer2!Version
        rstVer1!ver_compat = rstVer2!Version
        rstVer1.Update
        IsVersionAllowed = True
    End Select
    
End Function

Public Function GetVersion() As String
Dim dbsVer3 As Database
Dim rstVer3 As Recordset

Set dbsVer3 = CurrentDb()
Set rstVer3 = dbsVer3.OpenRecordset("version_local", dbOpenSnapshot)
GetVersion = Format(rstVer3!Version, "#0.00")
End Function


Public Sub AccessCloseButtonEnabled(pfEnabled As Boolean)
  ' Comments: Control the Access close button.
  '           Disabling it forces the user to exit within the application
  ' Params  : pfEnabled       TRUE enables the close button, FALSE disabled it
  ' Owner   : Copyright (c) FMS, Inc.
  ' Source  : Total Visual SourceBook
  ' Usage   : Permission granted to subscribers of the FMS Newsletter

  On Error Resume Next

  Const clngMF_ByCommand As Long = &H0&
  Const clngMF_Grayed As Long = &H1&
  Const clngSC_Close As Long = &HF060&

  Dim lngWindow As Long
  Dim lngMenu As Long
  Dim lngFlags As Long

  lngWindow = Application.hWndAccessApp
  lngMenu = GetSystemMenu(lngWindow, 0)
  If pfEnabled Then
    lngFlags = clngMF_ByCommand And Not clngMF_Grayed
  Else
    lngFlags = clngMF_ByCommand Or clngMF_Grayed
  End If
  Call EnableMenuItem(lngMenu, clngSC_Close, lngFlags)
End Sub


Private Sub sdasdf()
Dim dbs As Database
Dim prp As Property
Set dbs = CurrentDb()
Set prp = CurrentDb.CreateProperty("AllowBypassKey", dbBoolean, True)
dbs.Properties.Append prp
End Sub

Private Sub fdsgdsf()

'ChangeDbConnections

'CurrentDb.Properties("ReadyForDeployment") = False
'Debug.Print CurrentDb.Properties("ReadyForDeployment")
'CurrentDb.Properties("ConWord").Value = "Nothing"
'Debug.Print CurrentDb.Properties("ConWord").Value

'CurrentDb.Properties("ConWord") = "null"
'Debug.Print CurrentDb.Properties("ConWord")
'Application.SetOption "Default Find/Replace Behavior", 0
End Sub

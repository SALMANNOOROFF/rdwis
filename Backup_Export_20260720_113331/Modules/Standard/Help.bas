Attribute VB_Name = "Help"
Option Compare Database
Option Explicit

Public Const HH_DISPLAY_TOPIC = &H0
Public Const HH_DISPLAY_TOC = &H1
Public Const HH_DISPLAY_INDEX = &H2
Public Const HH_HELP_CONTEXT = &HF

Declare Function HTMLHelp Lib "hhctrl.ocx" Alias "HtmlHelpA" (ByVal hwndCaller As Long, ByVal pszFile As String, ByVal uCommand As Long, ByVal dwDAta As Long) As Long

Public Function HelpDest() As String
Dim dbsFile As Database
Dim tblFile As TableDef
Dim strLocal As String

Set dbsFile = CurrentDb()
Set tblFile = dbsFile.TableDefs("cen_version")
If InStr(tblFile.Connect, "DATABASE=rdw;") > 0 Then HelpDest = "\\10.120.29.100\help\"
If InStr(tblFile.Connect, "DATABASE=trn;") > 0 Then HelpDest = "\\10.120.29.100\help\"
If InStr(tblFile.Connect, "DATABASE=dev;") > 0 Then HelpDest = Left(CurrentProject.Path, 39) & "RDWIS help\Output\chm\"
End Function

Public Function CallHelp() As Boolean
Call HTMLHelp(0, "\\10.120.29.100\help\RDWIS help.chm", HH_DISPLAY_TOC, 0)
CallHelp = True
End Function

Public Function GetHelp()
Dim objFSO As Object
Dim objFile As Object
Dim strLocalFile As String
Dim strRemoteFile As String
Dim dtLocalDate As Date
Dim dtRemoteDate As Date
On Error GoTo Error_Part:

Set objFSO = CreateObject("Scripting.FileSystemObject")
strLocalFile = CurrentProject.Path & "\rdwis_help.chm"
If objFSO.FileExists(strLocalFile) = True Then
    Set objFile = objFSO.GetFile(strLocalFile)
    dtLocalDate = objFile.DateCreated
    End If

strRemoteFile = "\\10.120.29.100\help" & "\rdwis_help.chm"
If objFSO.FileExists(strRemoteFile) = True Then
    Set objFile = objFSO.GetFile(strRemoteFile)
    dtRemoteDate = objFile.DateCreated
    End If

If dtLocalDate <> 0 And dtLocalDate > dtRemoteDate Then GoTo Open_File
DoCmd.OpenForm "wait"
objFSO.CopyFile strRemoteFile, strLocalFile, True
DoCmd.Close acForm, "wait"

Open_File:
HTMLHelp 0, strLocalFile, HH_HELP_CONTEXT, 0

GetHelp = True
Exit Function

Error_Part:
If Err.Number = 70 Then
    DoCmd.Close acForm, "wait"
    MsgBox "New help file cannot be copied from server.", vbCritical
    Exit Function
    End If
MsgBox "Error in opening help. Please restart the program and retry.", vbCritical
End Function

Sub dfsf()
GetHelp
End Sub


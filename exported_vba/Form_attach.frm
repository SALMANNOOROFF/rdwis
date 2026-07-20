VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_attach"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim n As Integer

arrArgs = Split(Me.OpenArgs, "~")
Me.txtObjectType = arrArgs(0)
Me.txtObjectId = arrArgs(1)
If arrArgs(2) <> "" Then Me.txtFileType = arrArgs(2)
If arrArgs(3) <> "" Then Me.txtSlotId = arrArgs(3)
Me.txtFormName = arrArgs(4)
Me.lblTitle.Caption = arrArgs(5)

If Nz(Me.txtFileType, "") <> "" Then
    Me.txtFileType.Locked = True
    Me.txtFileType.BorderStyle = 0
    Me.cmdSelect.Visible = True
    Me.cmdSelect.SetFocus
    Else
    Me.lblFileTypeHelp.Visible = True
    Me.txtFileType.SetFocus
    End If
End Sub

Private Sub txtFileType_BeforeUpdate(Cancel As Integer)
Dim dbsAtt As Database
Dim rstAtt As Recordset
Dim strSql As String

Set dbsAtt = CurrentDb()
Select Case Me.txtObjectType
    Case "prj"
    strSql = "Select * From prj_prjattachments Where jat_objid = " & Me.txtObjectId & " And jat_objtype = '" & Me.txtObjectType & "' And LCase(jat_type) = '" & LCase(Me.txtFileType) & "'"
    Case "emp"
    strSql = "Select * From hr_empattachments Where eat_objid = " & Me.txtObjectId & " And eat_objtype = '" & Me.txtObjectType & "' And LCase(eat_type) = '" & LCase(Me.txtFileType) & "'"
    Case "ctc"
    strSql = "Select * From hr_ctrcaseattachments Where cat_objid = " & Me.txtObjectId & " And cat_objtype = '" & Me.txtObjectType & "' And LCase(cat_type) = '" & LCase(Me.txtFileType) & "'"
    Case "pcs"
    strSql = "Select * From pur_purattachments Where pat_objid = " & Me.txtObjectId & " And pat_objtype = '" & Me.txtObjectType & "' And LCase(pat_type) = '" & LCase(Me.txtFileType) & "'"
    Case "ina"
    strSql = "Select * From ina_inaattachments Where iat_objid = " & Me.txtObjectId & " And iat_objtype = '" & Me.txtObjectType & "' And LCase(iat_type) = '" & LCase(Me.txtFileType) & "'"
    Case "rev"
    strSql = "Select * From aud_audattachments Where aat_objid = " & Me.txtObjectId & " And aat_objtype = '" & Me.txtObjectType & "' And LCase(aat_type) = '" & LCase(Me.txtFileType) & "'"
    End Select
Set rstAtt = dbsAtt.OpenRecordset(strSql)
If Not rstAtt.EOF Then
    Cancel = True
    MsgBox "A slot for this type already exists. If that slot is empty, double click it to attach a file. If you want to attach another file of this type, modify the type (eg, by adding a number as suffix).", vbCritical
    End If
End Sub

Private Sub cmdSelect_Click()

If Nz(Me.txtFileType, "") = "" Then
    MsgBox "Please enter a file type.", vbCritical
    Exit Sub
    End If

Me.txtFilePath = SelectFile(Me.txtFileType)
Me.wbFileContent.Requery
If Nz(Me.txtFilePath, "") <> "" Then
    Me.wbFileContent.Visible = True
    Me.cmdAttach.Visible = True
    End If
End Sub

Private Sub cmdAttach_Click()
Dim SlotId As Long

If Nz(Me.txtFileType, "") = "" Then
    MsgBox "Please enter a file type.", vbCritical
    Exit Sub
    End If

If Nz(Me.txtFilePath, "") = "" Then
    MsgBox "Please select a file to attach.", vbCritical
    Exit Sub
    End If

AttachFile Me.txtObjectType, Me.txtObjectId, Me.txtFileType, Nz(Me.txtSlotId, ""), Me.txtFilePath
Forms(Me.txtFormName).Refresh
MsgBox "File has been attached successfully.", vbInformation

DoCmd.Close
End Sub




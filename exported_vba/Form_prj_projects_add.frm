VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdSave_Click()
Dim dbsSave As Database
Dim rstSave As Recordset
Dim lngPrjId As Long

Me.Dirty = False

If Nz(Me.prj_title, "") = "" Then
    MsgBox "Please enter project title.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.prj_code, "") = "" Then
    MsgBox "Please enter project code.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.prj_sponsor, "") = "" Then
    MsgBox "Please enter project sponsor.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.prj_rcptdt, "") = "" Then
    MsgBox "Please enter the date when project was received.", vbCritical
    Exit Sub
    End If

Set dbsSave = CurrentDb()
Set rstSave = dbsSave.OpenRecordset("Select prj_code From prj_projects Where prj_code = '" & Me.prj_code & "'", dbOpenSnapshot)
If Not rstSave.EOF Then
    MsgBox "The project code '" & Me.prj_code & "' is already in use. Please use another code.", vbCritical
    Exit Sub
    End If

Set rstSave = dbsSave.OpenRecordset("prj_projects", dbOpenDynaset)
lngPrjId = NewIdForProjectOrAccount(getVar("varUnitId"))
With rstSave
    .AddNew
    !prj_id = lngPrjId
    !prj_code = Me.prj_code
    !prj_title = Me.prj_title
    !prj_unt_id = getVar("varUnitId")
    !prj_status = "Under assignment"
    !prj_sponsor = Me.prj_sponsor
    !prj_rcptdt = Me.prj_rcptdt
    .Update
    End With

CreateAttachmentSlot "prj", lngPrjId, "URD"
CreateAttachmentSlot "prj", lngPrjId, "PPF"
CreateAttachmentSlot "prj", lngPrjId, "Project Proposal"
CreateAttachmentSlot "prj", lngPrjId, "Work Order"

DoCmd.OpenForm "prj_projects_detail", acNormal, "", "", , acHidden, "Under assignment" & "~" & lngPrjId
Forms!prj_projects_detail.prj_scope.SetFocus
DoCmd.Close acForm, "prj_projects_add"
End Sub

Private Sub prj_code_KeyPress(KeyAscii As Integer)
'MsgBox KeyAscii
If KeyAscii >= 97 And KeyAscii <= 122 Then
    KeyAscii = KeyAscii - 32
    Exit Sub
    End If
If Not (KeyAscii = 8 Or KeyAscii = 45 Or (KeyAscii >= 65 And KeyAscii <= 90) Or (KeyAscii >= 48 And KeyAscii <= 57)) Then
    KeyAscii = 0
    Exit Sub
    End If
End Sub


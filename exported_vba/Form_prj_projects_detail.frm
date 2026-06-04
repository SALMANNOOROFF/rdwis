VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case getVar("varMode")
    Case "editor-s", "approver-s"
    Select Case arrArgs(0)
        Case "DataEntry"
            AllowEditsAdvanced Me.Name, True, True
            Me.AllowAdditions = True
            Me.DataEntry = True
            Me.txtTitle = "Enter new project"
            Me.prj_title.Locked = False
            Me.prj_title.BorderStyle = 1
            Me.prj_code.Locked = False
            Me.prj_code.BorderStyle = 1
            Me.subMilestones.Visible = False
            Me.prj_enddt.Visible = False
            Me.prj_rem.Visible = False
            Me.prj_notes.Visible = False
        Case Else
            If arrArgs(0) <> "Cancelled" And arrArgs(0) <> "Completed" Then
                AllowEditsAdvanced Me.Name, True, True
                Me.cmdAddMilestone.Visible = True
                Else
                Me.inmpr.Visible = True
                Me.tglInMPr.Visible = True
                End If
            Me.RecordSource = "Select * From prj_projects Where prj_id = " & arrArgs(1)
            Me.txtTitle = "Project  " & Me!prj_code
        End Select
    End Select

Me.prj_title.SetFocus
Me.prj_title.SelLength = 0
Me.Visible = True

End Sub

Private Sub Form_Activate()
Me.Refresh
End Sub

Private Sub Form_BeforeUpdate(Cancel As Integer)
Dim rstStatus As Recordset
Dim strStatus As String
Dim booStatus As Boolean
strStatus = Me!prj_status

Select Case strStatus
    Case "Under Feasibility"
        If IsNull(Me!prj_assigndt) Then
            MsgBox "Project assignment date is required."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
    Case "Approval Awaited"
        If IsNull(Me!prj_assigndt) Or IsNull(Me!prj_propdt) Then
            MsgBox "Project assignment and proposal dates are required."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
    Case "Funds awaited"
        If IsNull(Me!prj_assigndt) Or IsNull(Me!prj_propdt) Or IsNull(Me!prj_aprvdt) Then
            MsgBox "Project assignment, proposal and approval dates are required."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
    Case "Work in progress"
        If IsNull(Me!prj_assigndt) Or IsNull(Me!prj_propdt) Or IsNull(Me!prj_aprvdt) Or IsNull(Me!prj_startdt) Then
            MsgBox "Project assignment, proposal, approval and start dates are required."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
    Case "Completed"
        If Me.subMilestones.Form.Recordset.RecordCount = 0 Then
            MsgBox "A project must have atleast one milestone."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
        Set rstStatus = Me.subMilestones.Form.RecordsetClone
        Do While Not rstStatus.EOF
            If rstStatus!msn_status <> "Completed" Then booStatus = True
            rstStatus.MoveNext
            Loop
        If booStatus = True Then
            MsgBox "Please close all milestones and activities before closing the case.", vbCritical
            Cancel = True
            Me.Undo
            Exit Sub
            End If
        If IsNull(Me!prj_assigndt) Or IsNull(Me!prj_propdt) Or IsNull(Me!prj_aprvdt) Or IsNull(Me!prj_startdt) Or IsNull(Me!prj_enddt) Then
            MsgBox "Project assignment, proposal, approval, start and completion dates are required."
            Cancel = True
            Me.Undo
            Exit Sub
            End If
'        Set rstStatus = CurrentDb.OpenRecordset("Select pgh_xprj_id From prj_prghistory_last_open Where pgh_xprj_id = " & Me.prj_id, dbOpenForwardOnly)
'        If rstStatus.EOF = False Then
'            MsgBox "Progress for this case is open. The case can only be marked as 'Completed' or 'Cancelled' after the progress is cancelled or finalized."
'            Cancel = True
'            Me.Undo
'            Exit Sub
'            End If
    End Select

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

Private Sub prj_rem_KeyDown(KeyCode As Integer, Shift As Integer)
If KeyCode = 86 And Shift = 2 Then sanitizeClipboard
If KeyCode = vbKeyTab And Me.prj_rem.SelText = "" Then
    KeyCode = 0
    SendKeys ("   ")
    End If
End Sub

Private Sub prj_notes_KeyDown(KeyCode As Integer, Shift As Integer)
If KeyCode = 86 And Shift = 2 Then sanitizeClipboard
If KeyCode = vbKeyTab And Me.prj_notes.SelText = "" Then
    KeyCode = 0
    SendKeys ("   ")
    End If
End Sub

Private Sub prj_notes_DblClick(Cancel As Integer)
OpenNotepad
End Sub

Private Sub prj_rem_DblClick(Cancel As Integer)
OpenNotepad
End Sub

Private Sub tglInMPr_Click()
If Me.prj_reporting = 1 Then
    Me.prj_reporting = 0
    Else
    Me.prj_reporting = 1
    End If
Me.Refresh
End Sub

Private Sub cmdAddMilestone_Click()
On Error GoTo cmdAddMilestone_Click_Err

Me.Dirty = False
   
DoCmd.OpenForm "prj_milestones_details", acNormal, "", "", , acHidden, "NewMilestone~" & Me!prj_id

cmdAddMilestone_Click_Exit:
    Exit Sub

cmdAddMilestone_Click_Err:
    If Err.Number = 3155 Then
        MsgBox "Please complete case data before adding item", vbCritical
        Else
        MsgBox Err.Number & "-" & Error$, vbCritical
        End If
    Resume cmdAddMilestone_Click_Exit

End Sub

Private Sub cmdHistory_Click()
On Error GoTo cmdHistory_Click_Err

DoCmd.OpenForm "prj_events_detail", acNormal, "", "", , acHidden, "~" & Me!prj_id

cmdHistory_Click_Exit:
    Exit Sub

cmdHistory_Click_Err:
    MsgBox Error$
    Resume cmdHistory_Click_Exit

End Sub

Private Sub cmdPreview_Click()
On Error GoTo cmdPreview_Click_Err

Me.Dirty = False
Me.Refresh
DoCmd.OpenReport "prj_projects_detail", acViewPreview, "SELECT * FROM prj_projects_detail WHERE prj_id =" & Me!prj_id, "", acNormal


cmdPreview_Click_Exit:
    Exit Sub

cmdPreview_Click_Err:
    MsgBox Error$
    Resume cmdPreview_Click_Exit

End Sub

Private Sub cmdGantt_Click()
On Error GoTo cmdGantt_Click_Err

    DoCmd.OpenForm "prj_gantt", acNormal, "", "", , acNormal


cmdGantt_Click_Exit:
    Exit Sub

cmdGantt_Click_Err:
    MsgBox Error$
    Resume cmdGantt_Click_Exit

End Sub


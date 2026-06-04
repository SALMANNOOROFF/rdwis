VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects_detail_mpr"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim intLevel As Integer
Dim strStatus As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From prj_projects Where prj_id = " & arrArgs(1)
'AllowEditsAdvanced Me.Name, True, True
Me.Visible = True

End Sub

Private Sub Form_Current()
SetupForm
End Sub

Private Sub Form_Close()
Forms!prj_projects_mproverview.Requery
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
If IsNull(Me!prj_startdt) Then
    DoCmd.OpenReport "prj_mpr-ii", acViewPreview, "SELECT * FROM prj_mpr WHERE prj_id =" & Me!prj_id, "", acNormal
    Else
    DoCmd.OpenReport "prj_mpr-i", acViewPreview, "SELECT * FROM prj_mpr WHERE prj_id =" & Me!prj_id, "", acNormal
    End If

cmdPreview_Click_Exit:
    Exit Sub

cmdPreview_Click_Err:
    MsgBox Error$
    Resume cmdPreview_Click_Exit

End Sub


Private Sub cmdCreate_Click()
On Error GoTo Error_Handler
Dim intResponse As Integer
Dim dteDate As Date

intResponse = MsgBox("Do you want to create new progress for submission?", vbExclamation + vbYesNo, "Confirmation Required")
If intResponse = 7 Then Exit Sub

dteDate = GetNow()
Dim qryCreate As QueryDef
Set qryCreate = CurrentDb.QueryDefs("prj_prghistory_create_new")
qryCreate.Execute

Me.Refresh
SetupForm
Me.subProgress.Form.pgh_progress = Formatify(Me.subProgress.Form.pgh_progress)

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End
End Sub

Private Sub cmdEdit_Click()
On Error GoTo Error_Handler
Dim intResponse3 As Integer
Dim strOldStatus As String

intResponse3 = MsgBox("Do you want to edit this progress?", vbExclamation + vbYesNo, "Confirmation Required")
If intResponse3 = 7 Then Exit Sub

Me.Refresh

'Close current comments and progress
FinalizeAnyComment
strOldStatus = Me.subProgress.Form!pgh_status
Me.subProgress.Form!pgh_status = "Edited"
Me.subProgress.Form!pgh_closedtg = GetNow()

'Create a copy of this progress
Dim qryEdit As QueryDef
Set qryEdit = CurrentDb.QueryDefs("prj_prghistory_create_copy")
qryEdit.Parameters("TrailInherited") = Me.subProgress.Form!pgh_trail
qryEdit.Parameters("ProgressStatus") = strOldStatus
qryEdit.Parameters("HistoryDtg") = GetNow()
qryEdit.Execute

'Create event
CreateEvent ("Edit")

Me.Refresh
SetupForm

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End

End Sub

Private Sub cmdCancel_Click()
On Error GoTo Error_Handler
Dim intResponse2 As Integer
Dim strOldStatus As String

intResponse2 = MsgBox("Do you want to cancel this progress?", vbExclamation + vbYesNo, "Confirmation Required")
If intResponse2 = 7 Then Exit Sub

Me.Refresh
'Close comments and progress
FinalizeAnyComment
strOldStatus = Me.subProgress.Form!pgh_status
Me.subProgress.Form!pgh_status = "Cancelled"
Me.subProgress.Form!pgh_closedtg = GetNow()
Me.subProgress.Form!pgh_underedit = 0

'Create event
If strOldStatus <> "Draft" Then CreateEvent ("Cancel")

Me.Refresh
SetupForm

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End
End Sub

Private Sub cmdFinalize_Click()
On Error GoTo Error_Handler
Dim intResponse4 As Integer

intResponse4 = MsgBox("Do you want to finalize this progress?", vbExclamation + vbYesNo, "Confirmation Required")
If intResponse4 = 7 Then Exit Sub

Me.Refresh
'Close current comments and progress
FinalizeAnyComment
Me.subProgress.Form!pgh_status = "Finalized"
Me.subProgress.Form!pgh_closedtg = GetNow()
Me.subProgress.Form!pgh_underedit = 0

'Create event
CreateEvent ("Finalize")

'Copy finalised progress to project remarks
Me!prj_rem = Me.subProgress.Form!pgh_progress

Me.Refresh
SetupForm

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End

End Sub


Private Sub cmdForward_Click()
On Error GoTo Error_Handler

MoveProgress ("Forward")

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End

End Sub

Private Sub cmdReturn_Click()
On Error GoTo Error_Handler

MoveProgress ("Return")

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End

End Sub

Private Sub cmdAddComment_Click()
CurrentDb.Execute "prj_comments_create"
Me.Refresh
SetupForm
Me.subComment.SetFocus
Me.subComment.Form.cmt_comment.SetFocus
End Sub

Private Sub cmdRemoveComment_Click()
CurrentDb.Execute "prj_comments_delete"
Me.Refresh
SetupForm
End Sub

Private Sub MoveProgress(strMove As String)
On Error GoTo Error_Handler
Dim intResponse5 As Integer
Dim intDestination5 As Integer
Dim strProgStatus5 As String

intResponse5 = MsgBox("Do you want to " & strMove & " this progress?", vbExclamation + vbYesNo, "Confirmation Required")
If intResponse5 = 7 Then Exit Sub

Me.Refresh
strProgStatus5 = Me.subProgress.Form!pgh_status
FinalizeAnyComment
Select Case strMove
    Case "Forward"
        intDestination5 = NextStop("mpr_route")
        Me.subProgress.Form!pgh_status = "Under Finalization"
    Case "Return"
        intDestination5 = PrevStop("mpr_route")               'Me.cmbReturn.Column(2)
        Me.subProgress.Form!pgh_status = "Under Review"
    End Select
If Me.subProgress.Form!pgh_underedit = 1 Then Me.subProgress.Form!pgh_dtg = GetNow()
Me.subProgress.Form!pgh_underedit = 0
Me.subProgress.Form!pgh_level = intDestination5
Me.subProgress.Form!pgh_trail = Me.subProgress.Form!pgh_trail & intDestination5 & "-"

'Create event
CreateEvent (strMove)

Me.Refresh
SetupForm

The_End:
Exit Sub
Error_Handler:
MsgBox Error$
GoTo The_End

End Sub

Private Sub FinalizeAnyComment()
'If there is a draft comment, finalize it
If Me.subComment.Form.CurrentRecord > 0 Then
    If Me.subComment.Form!cmt_status = "Draft" Then
        Me.subComment.Form!cmt_status = "Finalized"
        Me.subComment.Form!cmt_dtg = GetNow()
        End If
    End If
End Sub

Private Sub CreateEvent(strEventName As String)
Dim dbsEvent As Database
Dim qryEvent As QueryDef

Set dbsEvent = CurrentDb()
Set qryEvent = dbsEvent.QueryDefs("prj_events_create")
qryEvent.Parameters("EventName") = strEventName
qryEvent.Parameters("ProjectId") = Me!prj_id
qryEvent.Parameters("ProgressId") = Me.subProgress.Form!pgh_id
qryEvent.Parameters("CommentId") = Me.subComment.Form!cmt_id
qryEvent.Parameters("EventDtg") = GetNow()
Select Case strEventName
    Case "Forward"
    qryEvent.Parameters("Effectee") = DesigInStop(NextStop("mpr_route"))      'Me("cmb" & strEventName)
    Case "Return"
    qryEvent.Parameters("Effectee") = DesigInStop(PrevStop("mpr_route"))
    Case Else
    qryEvent.Parameters("Effectee") = ""
    End Select
qryEvent.Execute

End Sub

Private Sub SetupForm()
Dim intThisLevel As Integer
Dim strThisPosition As String
Dim intProgLevel As Integer
Dim strProgStatus As String
Dim strUserAuth As String

'Set to default
Me.cmdHistory.SetFocus
Me.cmdCreate.Visible = False
Me.cmdEdit.Visible = False
Me.cmdCancel.Visible = False
Me.cmdForward.Visible = False
Me.cmdReturn.Visible = False
Me.cmbReturn.Visible = False
Me.cmdFinalize.Visible = False
DenyProgressEdits
Me.cmdAddComment.Visible = False
Me.cmdRemoveComment.Visible = False
Me.subCommhistory.Top = 5400
Me.subCommhistory.Height = 8160

'Populating variables
strUserAuth = getVar("varMode")
intThisLevel = getVar("varRoleLevel")
strThisPosition = IIf(intThisLevel = FirstStop("mpr_route"), "First", _
                  IIf(intThisLevel = LastStop("mpr_route"), "Last", "Middle"))
If Me.subProgress.Form.CurrentRecord > 0 Then
    strProgStatus = Me.subProgress.Form!pgh_status
    intProgLevel = Me.subProgress.Form!pgh_level
    End If

'==============================================================================================

If strUserAuth = "editor-s" Or strUserAuth = "approver-s" Then
    'Progress does not exist
    If intProgLevel = 0 And strThisPosition = "First" Then
        Me.cmdCreate.Visible = True
        GoTo Block1_End
        End If
    'Progress exists but not at my level
    If intProgLevel <> intThisLevel Then GoTo Block1_End
    'Progress exists my level
    If Me.subProgress.Form!pgh_underedit = 1 Then
        AllowProgressEdits
        Else
        Me.cmdEdit.Visible = True
        End If
    If strThisPosition = "First" Then Me.cmdCancel.Visible = True
    If Me.subComment.Form.CurrentRecord = 0 Then
        Me.cmdAddComment.Visible = True
        Else
        Me.cmdRemoveComment.Visible = True
        Me.subCommhistory.Top = 7080
        Me.subCommhistory.Height = 6480
        End If
    If strUserAuth = "approver-s" Then
        If strThisPosition <> "First" Then Me.cmdReturn.Visible = True
        If strThisPosition <> "Last" Then Me.cmdForward.Visible = True
        If strThisPosition = "Last" Then Me.cmdFinalize.Visible = True
        End If

Block1_End:
End If

'==============================================================================================

If strUserAuth = "editor-m" Or strUserAuth = "approver-m" Then
    'Progress does not exist
    If intProgLevel = 0 Then GoTo Block2_End
    'Progress exists but not at my level
    If intProgLevel <> intThisLevel Then GoTo Block2_End
    'Progress exists my level
    If Me.subProgress.Form!pgh_underedit = 1 Then
        AllowProgressEdits
        Else
        Me.cmdEdit.Visible = True
        End If
    If Me.subComment.Form.CurrentRecord = 0 Then
        Me.cmdAddComment.Visible = True
        Else
        Me.cmdRemoveComment.Visible = True
        Me.subCommhistory.Top = 7080
        Me.subCommhistory.Height = 6480
        End If
    If strUserAuth = "approver-m" Then
        If strThisPosition <> "First" Then Me.cmdReturn.Visible = True
        If strThisPosition <> "Last" Then Me.cmdForward.Visible = True
        If strThisPosition = "Last" Then Me.cmdFinalize.Visible = True
        End If
Block2_End:
End If

'==============================================================================================

End Sub

Private Sub AllowProgressEdits()
Me.subProgress.Form.AllowEdits = True
Me.subProgress.Form.pgh_progress.Locked = False
Me.subProgress.Form.pgh_progress.BackColor = RGB(&HDA, &HE3, &HF3) '#DAE3F3
End Sub
  
Private Sub DenyProgressEdits()
Me.subProgress.Form.AllowEdits = False
Me.subProgress.Form.pgh_progress.Locked = True
Me.subProgress.Form.pgh_progress.BackColor = -2147483613
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

Private Function IsThisAuthor() As Boolean
Dim strString As String

strString = Me.subProgress.Form!pgh_trail
strString = Mid(strString, 2, InStr(2, strString, "-") - 2)
If strString = getVar("varRoleLevel") Then
    IsThisAuthor = True
    Else
    IsThisAuthor = False
    End If
End Function

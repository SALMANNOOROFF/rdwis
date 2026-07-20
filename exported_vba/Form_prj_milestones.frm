VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_milestones"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_BeforeUpdate(Cancel As Integer)
Dim errorMessage As String

If Me!msn_type = "Milestone" Then
    If Not IsNull(Me!msn_startdt) Then
        errorMessage = "A milestone doesnot have start date."
        GoTo Error_Alert
        End If
    If Not IsNull(Me!msn_comp) Then
        errorMessage = "A milestone doesnot have percentage completion."
        GoTo Error_Alert
        End If
    End If
 
If Me!msn_type = "Activity" Then
    If IsNull(Me!msn_startdt) Then
        errorMessage = "A milestone must have a start date."
        GoTo Error_Alert
        End If
    If IsNull(Me!msn_comp) Then
        errorMessage = "A milestone must have percentage completion."
        GoTo Error_Alert
        End If
    If (Me!msn_comp = 100 And Me!msn_status <> "Completed") Or _
       (Me!msn_comp < 100 And Me!msn_status = "Completed") Or _
       (Me!msn_comp > 0 And Me!msn_status = "Not Started") Then
       errorMessage = "Percentage completed and status do not match"
       GoTo Error_Alert
       End If
    End If
 
If Me!msn_status = "Completed" And IsNull(Me!msn_achvdt) Then
    errorMessage = "Achieved date is missing."
    GoTo Error_Alert
    End If
If Me!msn_status <> "Completed" And Not IsNull(Me!msn_achvdt) Then
    errorMessage = "Achieved date is not valid till completion."
    GoTo Error_Alert
    End If
 
Exit Sub

Error_Alert:
MsgBox errorMessage
Cancel = True

End Sub

Private Sub msn_cc_path_Click()
Dim strTitle As String
strTitle = "Milestone " & Me!msn_id & "  -  " & Me.Parent!prj_code & " Project"
FileResponse "msn", Me!msn_idd, "Milestone Completion Certificate", Me!msn_idd, Nz(Me!msn_cc_path, ""), Me.Parent.Name, strTitle
End Sub

Private Sub cmdMsn_Click()
DoCmd.OpenForm "prj_milestones_details", acNormal, , , , acHidden, Me!msn_status & "~" & Me!msn_idd
End Sub


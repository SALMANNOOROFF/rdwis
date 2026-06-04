VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_milestones_details"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From prj_milestones where msn_idd = " & arrArgs(1)
Select Case arrArgs(0)
    Case "Not started", "Awaited", "Held up", "In progress"
        AllowEditsAdvanced Me.Name, False, False
    Case "Completed"
        Me.cmdReverse.Visible = True
    Case "NewMilestone"
        Me.msn_type.Tag = "l"
        Me.msn_status.Tag = "l"
        Me.msn_achvdt.Visible = False
        AllowEditsAdvanced Me.Name, False, False
        Me.DataEntry = True
        Me.AllowAdditions = True
    Case "NewActivity"
    End Select
    
If Me!msn_type = "Activity" Then
    Me.msn_startdt.Visible = True
    Me.msn_comp.Visible = True
    End If

Me.Visible = True
End Sub

Private Sub Form_BeforeUpdate(Cancel As Integer)
Dim errorMessage As String

If Me!msn_type = "Activity" Then
    If IsNull(Me!msn_startdt) Then
        errorMessage = "An activity must have a start date."
        GoTo Error_Alert
        End If
    If IsNull(Me!msn_comp) Then
        errorMessage = "An activity must have percentage completion."
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

Private Sub Form_AfterInsert()
Dim dbsMsn As Database
Dim rstMsn As Recordset
Dim lngHead As Long

Set dbsMsn = CurrentDb()

'If head has been created, note id, else exit sub
Set rstMsn = dbsMsn.OpenRecordset("Select hed_id From cen_heads Where hed_prj_id = " & Me!msn_xprj_id, dbOpenSnapshot)
If Not rstMsn.EOF Then
    lngHead = rstMsn!hed_id
    Else
    Exit Sub
    End If

'Add milestone cost entry
Set rstMsn = dbsMsn.OpenRecordset("Select * From fin_msncosts Where mct_prj_id = " & Me!msn_xprj_id & " And mct_msn_id = " & Me!msn_id, dbOpenDynaset)
With rstMsn
    .AddNew
    !mct_prj_id = Me!msn_xprj_id
    !mct_hed_id = lngHead
    !mct_msn_id = Me!msn_id
    !mct_msn_idd = Me!msn_idd
    !mct_cost = 0
    .Update
    End With

End Sub

Private Sub msn_status_AfterUpdate()
If Me.msn_status = "Completed" Then
    If Me.msn_type = "Activity" Then Me.msn_comp = 100
    Else
    Me.msn_achvdt = Null
    End If
End Sub

Private Sub msn_type_AfterUpdate()
Select Case Me.msn_type
    Case "Milestone"
        Me!msn_startdt = Null
        Me!msn_comp = Null
        Me.msn_startdt.Visible = False
        Me.msn_comp.Visible = False
    Case "Activity"
        Me.msn_startdt.Visible = True
        Me.msn_comp.Visible = True
    End Select
End Sub

Private Sub msn_cc_path_Click()
Dim strTitle As String
strTitle = "Milestone " & Me!msn_id & "  -  " & Me!msn_xprj_id & " Project"
FileResponse "msn", Me!msn_idd, "Milestone Completion Certificate", Me!msn_idd, Nz(Me!msn_cc_path, ""), Me.Name, strTitle
End Sub

Private Sub cmdReverse_Click()
DoCmd.OpenForm "prj_milestones_rev", acNormal, "", "", , acHidden
End Sub

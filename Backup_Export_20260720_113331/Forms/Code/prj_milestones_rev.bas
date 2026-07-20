VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_milestones_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)

Me.object_id = Forms!prj_milestones_details!msn_idd
Me.unit_id = Forms!prj_projects_detail!prj_unt_id
Me.object_code = Forms!prj_projects_detail!prj_code
Me.msn_id = Forms!prj_milestones_details!msn_id

Me.msn_type = Forms!prj_milestones_details!msn_type
Me.msn_type_old = Forms!prj_milestones_details!msn_type

Me.msn_desc = Forms!prj_milestones_details!msn_desc
Me.msn_desc_old = Forms!prj_milestones_details!msn_desc

Me.msn_startdt = Forms!prj_milestones_details!msn_startdt
Me.msn_startdt_old = Forms!prj_milestones_details!msn_startdt

Me.msn_targetdt = Forms!prj_milestones_details!msn_targetdt
Me.msn_targetdt_old = Forms!prj_milestones_details!msn_targetdt

Me.msn_achvdt = Forms!prj_milestones_details!msn_achvdt
Me.msn_achvdt_old = Forms!prj_milestones_details!msn_achvdt

Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intresponse As Integer
Dim lngRevId As Long
Dim intRevType As Integer

Me.Dirty = False
Select Case Me.frmOption
    Case 1
        If AnyChangeOnForm = False Then
            MsgBox "No change in data requested.", vbCritical
            Exit Sub
            End If
        intRevType = 2
    Case 2
        intRevType = 1
    End Select
    
intresponse = MsgBox("A Data Revision Request for task " & Me.msn_id & " of project " & Me.object_code & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Task", Me.object_id, Me.unit_id, intRevType, Me.object_code, Me.msn_id)
DoCmd.Close acForm, "prj_milestones_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "prj_milestones_details"
DoCmd.Close acForm, "prj_projects_detail"
DoCmd.Close acForm, "prj_projects"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

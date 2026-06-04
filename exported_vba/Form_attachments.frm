VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_attachments"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Select Case Me.Parent.Name
    Case "prj_projects_detail", "prj_projects_one"
        Me.RecordSource = "prj_prjattachments"
        Me.objid.ControlSource = "jat_objid"
        Me.objtype.ControlSource = "jat_objtype"
        Me.id.ControlSource = "jat_id"
        Me.type.ControlSource = "jat_type"
        Me.path.ControlSource = "jat_path"
        Me.Parent.subAttachments.LinkMasterFields = "prj_id"
        Me.Parent.subAttachments.LinkChildFields = "jat_objid"
        Me.OrderBy = "jat_type"
        Me.OrderByOn = True
    Case "hr_emps_detail"
        Me.RecordSource = "hr_empattachments"
        Me.objid.ControlSource = "eat_objid"
        Me.objtype.ControlSource = "eat_objtype"
        Me.id.ControlSource = "eat_id"
        Me.type.ControlSource = "eat_type"
        Me.path.ControlSource = "eat_path"
        Me.Parent.subAttachments.LinkMasterFields = "emp_id"
        Me.Parent.subAttachments.LinkChildFields = "eat_objid"
        Me.OrderBy = "eat_type"
        Me.OrderByOn = True
    Case "pur_purcases_detail", "pur_purcasespetty_detail", "pur_purcasestada_detail"
        Me.RecordSource = "pur_purattachments"
        Me.objid.ControlSource = "pat_objid"
        Me.objtype.ControlSource = "pat_objtype"
        Me.id.ControlSource = "pat_id"
        Me.type.ControlSource = "pat_type"
        Me.path.ControlSource = "pat_path"
        Me.Parent.subAttachments.LinkMasterFields = "pcs_id"
        Me.Parent.subAttachments.LinkChildFields = "pat_objid"
        Me.OrderBy = "pat_type"
        Me.OrderByOn = True
    Case "hr_ctrcase_detail", "hr_ctrcaseext_detail", "hr_ctrcasehg_detail"
        Me.RecordSource = "hr_ctrcaseattachments"
        Me.objid.ControlSource = "cat_objid"
        Me.objtype.ControlSource = "cat_objtype"
        Me.id.ControlSource = "cat_id"
        Me.type.ControlSource = "cat_type"
        Me.path.ControlSource = "cat_path"
        Me.Parent.subAttachments.LinkMasterFields = "ctc_id"
        Me.Parent.subAttachments.LinkChildFields = "cat_objid"
        Me.OrderBy = "cat_type"
        Me.OrderByOn = True
    Case "aud_revs_detail"
        Me.RecordSource = "aud_audattachments"
        Me.objid.ControlSource = "aat_objid"
        Me.objtype.ControlSource = "aat_objtype"
        Me.id.ControlSource = "aat_id"
        Me.type.ControlSource = "aat_type"
        Me.path.ControlSource = "aat_path"
        Me.Parent.subAttachments.LinkMasterFields = "rev_id"
        Me.Parent.subAttachments.LinkChildFields = "aat_objid"
        Me.OrderBy = "aat_type"
        Me.OrderByOn = True
    End Select
        
End Sub

Private Sub cmdPath_Click()
Dim strTitle As String

Select Case Me.Parent.Name
    Case "prj_projects_detail"
        strTitle = Me.Parent!prj_code & " - " & Me.Parent!prj_title
    Case "hr_emps_detail"
        strTitle = Me.Parent!emp_id & "  -  " & NameComplete(Me.Parent!emp_name, Me.Parent!emp_title, Me.Parent!emp_rank)
    Case "pur_purcases_detail", "pur_purcasespetty_detail", "pur_purcasestada_detail"
        strTitle = "Purchase Case " & Me.Parent!pcs_id & " - " & Me.Parent!pcs_title
    Case "hr_ctrcase_detail", "hr_ctrcaseext_detail", "hr_ctrcasehg_detail"
        strTitle = "Contract Case " & Me.Parent!ctc_id & " - " & Me.Parent!ctc_empnamecomp
    Case "aud_revs_detail"
        strTitle = "Revision " & Me.Parent!rev_id & " - " & "For " & Me.Parent!rev_obj
    End Select

FileResponse Me.objtype, Me.objid, Me.type, Me.id, Nz(Me.path, ""), Me.Parent.Name, strTitle
End Sub

Private Sub cmdAddAttach_Click()
Dim strTitle As String
Dim strObjType As String
Dim strObjId As String
On Error GoTo cmdAttach_Click_Err

Select Case Me.Parent.Name
    Case "prj_projects_detail"
        strTitle = Me.Parent!prj_code & " - " & Me.Parent!prj_title
        strObjType = "prj"
        strObjId = Me.Parent!prj_id
    Case "hr_emps_detail"
        strTitle = Me.Parent!emp_id & "  -  " & NameComplete(Me.Parent!emp_name, Me.Parent!emp_title, Me.Parent!emp_rank)
        strObjType = "emp"
        strObjId = Me.Parent!emp_id
    Case "pur_purcases_detail", "pur_purcasespetty_detail", "pur_purcasestada_detail"
        strTitle = "Purchase Case " & Me.Parent!pcs_id & " - " & Me.Parent!pcs_title
        strObjType = "pcs"
        strObjId = Me.Parent!pcs_id
    Case "hr_ctrcase_detail", "hr_ctrcaseext_detail", "hr_ctrcasehg_detail"
        strTitle = "Contract Case " & Me.Parent!ctc_id & " - " & Me.Parent!ctc_empnamecomp
        strObjType = "ctc"
        strObjId = Me.Parent!ctc_id
    Case "aud_revs_detail"
        strTitle = "Revision " & Me.Parent!rev_id & " - " & "For " & Me.Parent!rev_obj
        strObjType = "aud"
        strObjId = Me.Parent!rev_id
    End Select
    
FileResponse strObjType, strObjId, "", "", "", Me.Parent.Name, strTitle

cmdAttach_Click_Exit:
    Exit Sub

cmdAttach_Click_Err:
    MsgBox Error$
    Resume cmdAttach_Click_Exit

End Sub

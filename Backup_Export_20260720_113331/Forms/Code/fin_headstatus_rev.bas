VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_headstatus_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsRev As Database
Dim rstRev As Recordset
Dim frmStat As Form
Dim n As Integer
Set dbsRev = CurrentDb()

Set frmStat = Screen.ActiveForm
Set rstRev = dbsRev.OpenRecordset("Select * From fin_sharesalloc Where sha_hed_id = " & frmStat.cmbAccount, dbOpenSnapshot)
Me.object_id = rstRev!sha_id
Me.head_id = rstRev!sha_hed_id
Me.head_name = frmStat.cmbAccount.Column(1)
Me.unit_id = UnitFromHead(Me.head_id)
rstRev.Close

Me.trf_amount_x1 = frmStat!acc_allocation
Me.trf_amount_x1_old = frmStat!acc_allocation

Me.trf_amount_x2 = frmStat!mtss_share
Me.trf_amount_x2_old = frmStat!mtss_share

Me.rdw_share = frmStat!rdw_share
Me.rdw_share_old = frmStat!rdw_share

Me.sha_pcc = frmStat!pcc_share
Me.sha_pcc_old = frmStat!pcc_share

Me.sha_cf = frmStat!cf_share
Me.sha_cf_old = frmStat!cf_share

Me.sha_prj = frmStat!prj_share
Me.sha_prj_old = frmStat!prj_share
If Me.sha_prj <> Me.sha_pcc Then
    Me.sha_prj.Visible = True
    Me.lbl_sha_prj.Visible = True
    End If
    
Me.cmt_amount_x1 = frmStat!acc_allocation
Me.cmt_amount_x1_old = frmStat!acc_allocation

Me.cmt_amount_x2 = frmStat!mtss_share
Me.cmt_amount_x2_old = frmStat!mtss_share

For n = 1 To 5
    If frmStat.Controls("prj_name_" & Chr(96 + n)) <> "" Then
        Me.Controls("sbh_alloc_x" & n & "_lbl") = frmStat.Controls("prj_name_" & Chr(96 + n))
        Me.Controls("sbh_alloc_x" & n) = frmStat.Controls("prj_alloc_" & Chr(96 + n))
        Me.Controls("sbh_alloc_x" & n & "_old") = frmStat.Controls("prj_alloc_" & Chr(96 + n))
        Set rstRev = dbsRev.OpenRecordset("Select sbh_id From fin_subheads Where " & _
                                          "sbh_hed_id = " & Me.head_id & " And " & _
                                          "sbh_name = '" & Me.Controls("sbh_alloc_x" & n & "_lbl") & "'", _
                                          dbOpenSnapshot)
        'Me.Controls("sbh_alloc_x" & n & "_rowid") = rstRev!sbh_id
        Me.Controls("sbh_alloc_x" & n & "_lbl").Visible = True
        Me.Controls("sbh_alloc_x" & n).Visible = True
        End If
    Next n
    

Me.Visible = True

End Sub

Private Sub sha_pcc_AfterUpdate()
Me.sha_prj = Me.sha_prj_old + (Me.sha_pcc - Me.sha_pcc_old)
End Sub

Private Sub trf_amount_x1_AfterUpdate()
Me.cmt_amount_x1 = Me.trf_amount_x1
End Sub

Private Sub trf_amount_x2_AfterUpdate()
Me.cmt_amount_x2 = Me.trf_amount_x2
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

        If Me.txtCheck1 <> 0 Then
            MsgBox "Allocation must be equal to MTSS share plus RDW share.", vbCritical
            Exit Sub
            End If
        If Me.txtCheck2 <> 0 Then
            MsgBox "RDW share must be equal to sum of project share and CSRF.", vbCritical
            Exit Sub
            End If
        If Me.txtCheck3 <> 0 Then
            MsgBox "Sum of subhead allocation must be equal to the actual project share.", vbCritical
            Exit Sub
            End If
        intRevType = 2

'    Case 2
'        'Check for salary requisition
'        'check for purchase case
'        intRevType = 3
    End Select
    
intresponse = MsgBox("A Data Revision Request for allocation of " & Me.head_name & " head will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Allocation", Me.object_id, Me.unit_id, intRevType, Me.head_name)
DoCmd.Close acForm, "fin_headstatus_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_headstatus"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub




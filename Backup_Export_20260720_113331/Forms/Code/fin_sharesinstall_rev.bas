VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_sharesinstall_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)

Me.object_id = Forms!fin_sharesinstall_add.subInstalls!shi_id
Me.head_id = Forms!fin_sharesinstall_add!sha_hed_id
Me.head_name = Forms!fin_sharesinstall_add!sha_hed_id.Column(1)
Me.unit_id = UnitFromHead(Me.head_id)

Me.trn_amount1_x1 = Forms!fin_sharesinstall_add.subInstalls!inward
Me.trn_amount1_x1_old = Forms!fin_sharesinstall_add.subInstalls!inward
'Me.trn_amount1_x1_rowid = Forms!fin_sharesinstall_add.subInstalls!shi_fitrn_id

Me.trn_amount1_x2 = Forms!fin_sharesinstall_add.subInstalls!mtss_share
Me.trn_amount1_x2_old = Forms!fin_sharesinstall_add.subInstalls!mtss_share
'Me.trn_amount1_x2_rowid = Forms!fin_sharesinstall_add.subInstalls!shi_fotrn_id

Me.rdw_share = trn_amount1_x1 - trn_amount1_x2
Me.rdw_share_old = trn_amount1_x1 - trn_amount1_x2

Me.shi_pcc = Forms!fin_sharesinstall_add.subInstalls!shi_pcc
Me.shi_pcc_old = Forms!fin_sharesinstall_add.subInstalls!shi_pcc

Me.shi_cf = Forms!fin_sharesinstall_add.subInstalls!shi_cf
Me.shi_cf_old = Forms!fin_sharesinstall_add.subInstalls!shi_cf

Me.trn_amount2_x1 = Forms!fin_sharesinstall_add.subInstalls!inward
Me.trn_amount2_x1_old = Forms!fin_sharesinstall_add.subInstalls!inward
'Me.trn_amount2_x1_rowid = Forms!fin_sharesinstall_add.subInstalls!shi_fitrn_id

Me.trn_amount2_x2 = Forms!fin_sharesinstall_add.subInstalls!mtss_share
Me.trn_amount2_x2_old = Forms!fin_sharesinstall_add.subInstalls!mtss_share
'Me.trn_amount2_x2_rowid = Forms!fin_sharesinstall_add.subInstalls!shi_fotrn_id

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
'        If Me.trn_amount1_x1 = Me.trn_amount1_x1_old And _
'           Me.trn_amount1_x2 = Me.trn_amount1_x2_old And _
'           Me.rdw_share = Me.rdw_share_old Then
'            MsgBox "No change in allocation, MTSS share or RDW share.", vbCritical
'            Exit Sub
'            End If
        If Me.trn_amount1_x1 <> Me.trn_amount1_x2 + Me.rdw_share Then
            MsgBox "Total fund must be equal to MTSS share plus RDW share.", vbCritical
            Exit Sub
            End If
        If Me.rdw_share <> Me.shi_pcc + Me.shi_cf Then
            MsgBox "RDW share must be equal to sum of project share and CSRF.", vbCritical
            Exit Sub
            End If
        intRevType = 2

    Case 2
        intRevType = 3
    
    End Select
    
intresponse = MsgBox("A Data Revision Request for funding of " & Me.head_name & " head will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Funding", Me.object_id, Me.unit_id, intRevType, Me.head_name)
DoCmd.Close acForm, "fin_sharesinstall_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_sharesalloc_plus"
End Sub

Private Sub trn_amount1_x1_AfterUpdate()
Me.trn_amount2_x1 = Me.trn_amount1_x1
End Sub

Private Sub trn_amount1_x2_AfterUpdate()
Me.trn_amount2_x2 = Me.trn_amount1_x2
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub




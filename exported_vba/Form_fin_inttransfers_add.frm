VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_inttransfers_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub trf_fromhed_AfterUpdate()
Me.trf_title = "Amount transferred from " & Me.trf_fromhed.Column(1) & " to " & Me.trf_tohed.Column(1)
End Sub

Private Sub trf_tohed_GotFocus()
If Nz(Me!trf_fromhed, "") = "" Then Me.trf_fromhed.SetFocus
End Sub

Private Sub trf_tohed_AfterUpdate()
Me.trf_title = "Amount transferred from " & Me.trf_fromhed.Column(1) & " to " & Me.trf_tohed.Column(1)
End Sub

Private Sub cmdSave_Click()
Dim wrkITrans As Workspace
Dim dbsITrans As Database
Dim rstITrans As Recordset
Dim strCommit As String
Dim lngTransferId As Long
Dim lngCommitId As Long
On Error GoTo Error_Handler

If Nz(Me.trf_date, "") = "" Or Nz(Me.trf_fromhed, "") = "" Or Nz(Me.trf_tohed, "") = "" Or Nz(Me.trf_amount, "") = "" Then
    MsgBox "Please enter all data.", vbCritical
    Exit Sub
    End If

Set wrkITrans = DBEngine.Workspaces(0)
Set dbsITrans = CurrentDb()

wrkITrans.BeginTrans
strCommit = "Begun"

'Add transfer
Set rstITrans = dbsITrans.OpenRecordset("fin_transfers", dbOpenDynaset, dbSeeChanges)
With rstITrans
    .AddNew
    !trf_date = Me.trf_date
    !trf_type = "T"
    !trf_title = Me.trf_title
    !trf_amount = Me.trf_amount
    !trf_fromhed = Me.trf_fromhed
    !trf_fromunt = UnitFromHead(Me.trf_fromhed)
    !trf_tohed = Me.trf_tohed
    !trf_tount = UnitFromHead(Me.trf_tohed)
    !trf_status = "Paid"
    .Update
    .Bookmark = .LastModified
    End With
lngTransferId = rstITrans!trf_id

'Add commitment out
Set rstITrans = dbsITrans.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstITrans
    .AddNew
    !cmt_type = "TO"
    !cmt_docid = lngTransferId
    !cmt_date = Me.trf_date
    !cmt_amount = -1 * Me.trf_amount
    !cmt_effhed_id = Me.trf_fromhed
    !cmt_effunt_id = UnitFromHead(Me.trf_fromhed)
    !cmt_hed_id = Me.trf_tohed
    !cmt_unt_id = UnitFromHead(Me.trf_tohed)
    !cmt_status = "Paid"
    .Update
    .Bookmark = .LastModified
    End With
lngCommitId = rstITrans!cmt_id

'Add payment out
Set rstITrans = dbsITrans.OpenRecordset("fin_transactions", dbOpenDynaset)
With rstITrans
    .AddNew
    !trn_cmt_id = lngCommitId
    !trn_date = Me.trf_date
    !trn_amount1 = -1 * Me.trf_amount
    !trn_tax1 = 0
    !trn_amount2 = -1 * Me.trf_amount
    !trn_transtype = 2
    !trn_noloan = "0"
    !trn_seq = 1
    !trn_balance = 0
    .Update
    End With
    
'Add commitment in
Set rstITrans = dbsITrans.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstITrans
    .AddNew
    !cmt_type = "TI"
    !cmt_docid = lngTransferId
    !cmt_date = Me.trf_date
    !cmt_amount = Me.trf_amount
    !cmt_effhed_id = Me.trf_tohed
    !cmt_effunt_id = UnitFromHead(Me.trf_tohed)
    !cmt_hed_id = Me.trf_fromhed
    !cmt_unt_id = UnitFromHead(Me.trf_fromhed)
    !cmt_status = "Paid"
    .Update
    .Bookmark = .LastModified
    End With
lngCommitId = rstITrans!cmt_id

'Add payment in
Set rstITrans = dbsITrans.OpenRecordset("fin_transactions", dbOpenDynaset)
With rstITrans
    .AddNew
    !trn_cmt_id = lngCommitId
    !trn_date = Me.trf_date
    !trn_amount1 = Me.trf_amount
    !trn_tax1 = 0
    !trn_amount2 = Me.trf_amount
    !trn_transtype = 2
    !trn_noloan = "0"
    !trn_seq = 1
    !trn_balance = 0
    .Update
    End With

wrkITrans.CommitTrans
strCommit = "Committed"
DoCmd.Close

Exit Sub
Error_Handler:
If strCommit = "Begun" Then wrkITrans.Rollback
MsgBox Err.Number & " - " & Err.Description
End Sub



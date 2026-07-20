VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_heads_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
CurrentDb.Execute "Delete From fin_subheads_temp"
Me.subSubheads.Requery
End Sub

Private Sub hed_unt_id_AfterUpdate()
Dim dbsAct As Database
Dim rstAct As Recordset

Set dbsAct = CurrentDb()
Set rstAct = dbsAct.OpenRecordset("Select  Max(cen_heads.hed_id) AS max_hed_id From cen_heads " & _
         "Where hed_unt = " & Me.hed_unt_id & " And hed_type = 'Project'")
If IsNull(rstAct!max_hed_id) Then
    Me.hed_id = Me.hed_unt_id + 1
    Else
    Me.hed_id = rstAct!max_hed_id + 1
    End If
Set rstAct = Nothing

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub cmdCreate_Click()
Dim wksAccount As Workspace
Dim dbsAccount As Database
Dim rstAccount As Recordset
Dim rstSource As Recordset
Dim lngTransferAllocId As Long
Dim lngTransferMtssId As Long
Dim lngCommitAllocId As Long
Dim lngCommitMtssId As Long
Dim strCommit As String
On Error GoTo Error_Part

Me.Dirty = False

'Check Data -----------------------------------
If Nz(Me.project_id, "") = "" Or _
    Nz(Me.hed_id, "") = "" Or _
    Nz(Me.hed_opendt, "") = "" Or _
    Nz(Me.hed_transtype, "") = "" Or _
    Nz(Me.alloc, "") = "" Or _
    Nz(Me.mtss_share, "") = "" Or _
    Nz(Me.sha_cf, "") = "" Then
    MsgBox "Incomplete data.", vbCritical
    Exit Sub
    End If

If Me.total_alloc <> Me.sha_pcc Then
    MsgBox "Project share does not match sum of subhead allocations.", vbCritical
    Exit Sub
    End If
    
If Me.hed_code Like "*-" Or Me.hed_code Like "*_" Then
    MsgBox "Account code is incorrect.", vbCritical
    Exit Sub
    End If

'Begin transaction ------------------------------
Set dbsAccount = CurrentDb()
Set wksAccount = DBEngine.Workspaces(0)
wksAccount.BeginTrans
strCommit = "Begun"

'Add account
Set rstAccount = dbsAccount.OpenRecordset("cen_heads")
With rstAccount
    .AddNew
    !hed_id = Me.hed_id
    !hed_name = "xxx"
    !hed_type = "Project"
    !hed_code = Me.hed_code
    !hed_opendt = Me.hed_opendt
    !hed_transtype = Me.hed_transtype
    !hed_unt_id = Me.hed_unt_id
    !hed_prj_id = Me.project_id
    .Update
    End With
Set rstAccount = Nothing

'Add transfers
Set rstAccount = dbsAccount.OpenRecordset("fin_transfers", dbOpenDynaset, dbSeeChanges)
With rstAccount
    .AddNew
    !trf_date = Me.hed_opendt
    !trf_type = "FI"
    !trf_title = "Project Funding"
    !trf_amount = Me.alloc
    !trf_fromhed = 990001
    !trf_fromunt = 990000
    !trf_tohed = Me.hed_id
    !trf_tount = Me.hed_unt_id
    !trf_status = "Awaited"
    .Update
    .Bookmark = .LastModified
    End With
lngTransferAllocId = rstAccount!trf_id
Set rstAccount = Nothing
    
Set rstAccount = dbsAccount.OpenRecordset("fin_transfers", dbOpenDynaset, dbSeeChanges)
With rstAccount
    .AddNew
    !trf_date = Me.hed_opendt
    !trf_type = "FO"
    !trf_title = "MTSS Share"
    !trf_amount = Me.mtss_share
    !trf_fromhed = Me.hed_id
    !trf_fromunt = Me.hed_unt_id
    !trf_tohed = 990011
    !trf_tount = 990000
    !trf_status = "Awaited"
    .Update
    .Bookmark = .LastModified
    End With
lngTransferMtssId = rstAccount!trf_id
Set rstAccount = Nothing

'Add commitments
Set rstAccount = dbsAccount.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstAccount
    .AddNew
    !cmt_docid = lngTransferAllocId
    !cmt_type = "FI"
    !cmt_date = Me.hed_opendt
    !cmt_amount = Me.alloc
    !cmt_status = "Awaited"
    !cmt_effhed_id = Me.hed_id
    !cmt_effunt_id = Me.hed_unt_id
    !cmt_hed_id = 990001
    !cmt_unt_id = 990000
    .Update
    .Bookmark = .LastModified
    End With
lngCommitAllocId = rstAccount!cmt_id
Set rstAccount = Nothing

Set rstAccount = dbsAccount.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstAccount
    .AddNew
    !cmt_docid = lngTransferMtssId
    !cmt_type = "FO"
    !cmt_date = Me.hed_opendt
    !cmt_amount = -1 * Me.mtss_share
    !cmt_status = "Awaited"
    !cmt_effhed_id = Me.hed_id
    !cmt_effunt_id = Me.hed_unt_id
    !cmt_hed_id = 990011
    !cmt_unt_id = 990000
    .Update
    .Bookmark = .LastModified
    End With
lngCommitMtssId = rstAccount!cmt_id
Set rstAccount = Nothing

'Add shares
Set rstAccount = dbsAccount.OpenRecordset("fin_sharesalloc")
With rstAccount
    .AddNew
    !sha_hed_id = Me.hed_id
    !sha_ficmt_id = lngCommitAllocId
    !sha_focmt_id = lngCommitMtssId
    !sha_pcc = Me.sha_pcc
    !sha_prj = Me.sha_pcc
    !sha_cf = Me.sha_cf
    !sha_transtype = Me.hed_transtype
    .Update
    End With
Set rstAccount = Nothing
        
'Add subhead allocations
Set rstAccount = dbsAccount.OpenRecordset("fin_subheads")
Set rstSource = Me.subSubheads.Form.RecordsetClone
rstSource.MoveFirst
Do While Not rstSource.EOF
    With rstAccount
        .AddNew
        !sbh_hed_id = Me.hed_id
        !sbh_name = rstSource!sbh_name
        !sbh_alloc = rstSource!sbh_alloc
        .Update
        End With
    rstSource.MoveNext
    Loop
Set rstAccount = Nothing

'Add milestone cost entries
Set rstAccount = dbsAccount.OpenRecordset("fin_msncosts")
Set rstSource = dbsAccount.OpenRecordset("Select * From prj_milestones Where msn_xprj_id = " & Me.project_id & _
                                         " Order By msn_id", dbOpenSnapshot)
rstSource.MoveFirst
Do While Not rstSource.EOF
    With rstAccount
        .AddNew
        !mct_prj_id = Me.project_id
        !mct_hed_id = Me.hed_id
        !mct_msn_id = rstSource!msn_id
        !mct_msn_idd = rstSource!msn_idd
        !mct_cost = 0
        .Update
        End With
    rstSource.MoveNext
    Loop
Set rstAccount = Nothing


wksAccount.CommitTrans
strCommit = "Committed"
MsgBox "Account " & Me.hed_code & " created", vbInformation
DoCmd.Close
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksAccount.Rollback
    MsgBox "Project account cannot be opened." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub

Private Sub project_id_AfterUpdate()
Dim dbsCode As Database
Dim rstCode As Recordset

Set dbsCode = CurrentDb()
Set rstCode = dbsCode.OpenRecordset("Select * From prj_projects Where prj_id = " & Me.project_id, dbOpenSnapshot)
Me.project_title = rstCode!prj_title
Me.hed_unt_id = rstCode!prj_unt_id
Me.hed_code = rstCode!prj_code
Me.hed_id = rstCode!prj_id
rstCode.Close

Set rstCode = dbsCode.OpenRecordset("Select * From cen_heads Where hed_id = " & Me.hed_id, dbOpenSnapshot)
If Not rstCode.EOF Then Me.hed_id = NewIdForProjectOrAccount(Me.hed_unt_id)
rstCode.Close

Set rstCode = dbsCode.OpenRecordset("Select * From cen_heads Where hed_code = '" & Me.hed_code & "'", dbOpenSnapshot)
If Not rstCode.EOF Then Me.hed_code = Me.hed_code & "-"
rstCode.Close

Me.project_id.Locked = True
Me.project_id.BorderStyle = 0
Me.box_project_id.Visible = True
Me.alloc.Enabled = True
Me.mtss_share.Enabled = True
Me.sha_cf.Enabled = True
Me.subSubheads.Enabled = True
Me.subSubheads.Requery
Me.cmdCreate.Enabled = True
Me.hed_opendt.SetFocus

End Sub

Private Sub total_alloc_AfterUpdate()
MsgBox "ooo"
End Sub

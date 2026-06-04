VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_sharesinstall_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsFunding  As Database
Dim rstFunding As Recordset
On Error GoTo The_End

Set dbsFunding = CurrentDb()
Set rstFunding = dbsFunding.OpenRecordset("SELECT Sum(mct_cost) AS cost_total From fin_msncosts " & _
                                           "WHERE fin_msncosts.mct_hed_id = " & Me.sha_hed_id, dbOpenSnapshot)
Me.cost_total = rstFunding!cost_total

The_End:
Me.Visible = True

End Sub

Private Sub Form_Activate()
Me.Refresh
End Sub

Private Sub cmdSettle_Click()
Dim dbsTrans As Database
Dim wksTrans As Workspace
Dim rstTrans As Recordset
Dim lngFundingId As Long
Dim lngMtssId As Long
Dim strCommit As String
Dim intResponse As Integer
On Error GoTo Error_Part

'Nulls Not Allowed
If Nz(Me.ovr_amount, "") = "" Or Nz(Me.acc_amount, "") = "" Then
    MsgBox "Received amount and RDW shares are required.", vbCritical
    Exit Sub
    End If

'No Date is not allowed
If IsNull(Me.txtdate) Then
    MsgBox "Please enter date.", vbCritical
    Exit Sub
    End If

'Negative receipts not allowed
If Me.ovr_amount < 0 Or _
   Me.acc_amount < 0 Or _
   Me.pcc_amount < 0 Or _
   Me.cf_amount < 0 Then
    MsgBox "Negative numbers not allowed.", vbCritical
    Exit Sub
    End If

'Receipts exceeding total not allowed
If Me.ovr_amount > Me.ovr_ytBr Or _
   Me.acc_amount > Me.acc_ytbr Or _
   Me.pcc_amount > Me.pcc_ytbr Or _
   Me.cf_amount > Me.cf_ytbr Then
    MsgBox "Received amounts cannot be greater than yet to be received amounts.", vbCritical
    Exit Sub
    End If

'Amount greater tha milestone amount is not required
If Nz(Me.milestone, "") = "" Then
    If Me.ovr_amount > Me.msncost Then
        MsgBox "Received amount is greater than milestone cost.", vbCritical
        Exit Sub
        End If
    End If

''No Milestone is not allowed
'If Nz(Me.milestone, "") = "" Then
'    MsgBox "Please enter project milestone against which the amount is received."
'    Exit Sub
'    End If

'Milestone warning
If Nz(Me.milestone, "") = "" Then
    intResponse = MsgBox("No milestone has been entered. Payment will be added without milestone. Do you want to continue.", _
                         vbExclamation + vbYesNo, "Confirmation")
    If intResponse <> 6 Then Exit Sub
    End If


'*************************************************************************************************************

Set dbsTrans = CurrentDb()
Set wksTrans = DBEngine.Workspaces(0)
wksTrans.BeginTrans
strCommit = "Begun"

'Add funding transaction
Set rstTrans = dbsTrans.OpenRecordset("fin_transactions", dbOpenDynaset, dbSeeChanges)
With rstTrans
    .AddNew
    !trn_cmt_id = Me!sha_ficmt_id
    !trn_date = Me.txtdate
    !trn_amount1 = Me.ovr_amount
    !trn_tax1 = 0
    !trn_amount2 = Me.ovr_amount
    !trn_balance = 0
    !trn_seq = 1
    !trn_transtype = Me!sha_transtype
    !trn_noloan = 0
    .Update
    .Bookmark = .LastModified
    End With
lngFundingId = rstTrans!trn_id
rstTrans.Close

'Add mtss share transaction
Set rstTrans = dbsTrans.OpenRecordset("fin_transactions", dbOpenDynaset, dbSeeChanges)
With rstTrans
    .AddNew
    !trn_cmt_id = Me.sha_focmt_id
    !trn_date = Me.txtdate
    !trn_amount1 = -1 * Me.mtss_amount
    !trn_tax1 = 0
    !trn_amount2 = -1 * Me.mtss_amount
    !trn_balance = 0
    !trn_seq = 1
    !trn_transtype = Me!sha_transtype
    !trn_noloan = 0
    .Update
    .Bookmark = .LastModified
    End With
lngMtssId = rstTrans!trn_id
rstTrans.Close

'Add installment
Set rstTrans = dbsTrans.OpenRecordset("fin_sharesinstall", dbOpenDynaset)
With rstTrans
    .AddNew
    !shi_hed_id = Me!sha_hed_id
    !shi_fitrn_id = lngFundingId
    !shi_fotrn_id = lngMtssId
    !shi_pcc = Me.pcc_amount
    !shi_cf = Me.cf_amount
    If Nz(Me.milestone, "") <> "" Then !shi_msn_idd = Me.milestone
    .Update
    .Bookmark = .LastModified
    End With

wksTrans.CommitTrans
strCommit = "Committed"

Me.txtdate = Null
Me.msncost = Null
Me.milestone = Null
Me.ovr_amount = Null
Me.acc_amount = Null
Me.cf_amount = Null
Me.lblWarning.Visible = False
Me.Refresh

Exit Sub
Error_Part:
If strCommit = "Begun" Then
    wksTrans.Rollback
    MsgBox "The funding could not be added." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

Private Sub milestone_AfterUpdate()
If Nz(Me.milestone, "") = "" Then
    Me.lblWarning.Visible = True
    Me.msncost = Null
    Else
    Me.lblWarning.Visible = False
    Dim dbsMsn As Database
    Dim rstMsn As Recordset
    Set dbsMsn = CurrentDb()
    Set rstMsn = dbsMsn.OpenRecordset("Select mct_cost from fin_msncosts Where mct_msn_idd = " & Me.milestone, dbOpenSnapshot)
    If rstMsn.EOF Then Exit Sub
    Me.msncost = rstMsn!mct_cost
    End If
End Sub

Private Sub ovr_amount_AfterUpdate()
Me.acc_amount = Round(Me.ovr_amount * Me.acc_share / Me.alloc, 0)
Me.cf_amount = CalculateCfAmount()
If Nz(Me.milestone, "") = "" Then
    Me.lblWarning.Visible = True
    Else
    Me.lblWarning.Visible = False
    End If
End Sub

Private Sub acc_amount_AfterUpdate()
Me.cf_amount = CalculateCfAmount()
End Sub

Private Function CalculateCfAmount() As Long

If Me.cf_ytbr = 0 Then
    CalculateCfAmount = 0
    Exit Function
    End If

If Me.pcc_ytbr = 0 Then
    CalculateCfAmount = Me.acc_amount
    Exit Function
    End If
    
If Me.cf_share / Me.cf_ytbr = Me.pcc_share / Me.pcc_ytbr And Me.cf_ytbr > 0.3 * Me.ovr_amount And [acc_amount] - Round(0.3 * Me.ovr_amount, 0) <= pcc_ytbr Then
    CalculateCfAmount = Round(0.3 * Me.ovr_amount, 0)
    Else
    CalculateCfAmount = Me.cf_ytbr
    End If

End Function


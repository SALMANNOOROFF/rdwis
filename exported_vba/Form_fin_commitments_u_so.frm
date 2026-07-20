VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_commitments_u_so"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Open"
        Me.lblHeader.Caption = "Open Commitments - Salary Orders"
        Me.RecordSource = "fin_commitments_u_so_open"
        Me.txtdate.Visible = True
        Me.cmdPaid.Visible = True
    Case "Closed"
        Me.lblHeader.Caption = "Closed Commitments - Salary Orders"
        Me.RecordSource = "fin_commitments_u_so_closed"
        Me.cmdReverse.Visible = True
    End Select
Me.Visible = True


End Sub

Private Sub cmdPaid_Click()
Dim dbsTrans As Database
Dim rstTrans As Recordset
Dim strSrqId As String
Dim wksTrans As Workspace
Dim strCommit As String
On Error GoTo Error_Part

If IsNull(Me.txtdate) Then
    MsgBox "Please enter date of payment.", vbCritical
    Me.txtdate.SetFocus
    Exit Sub
    End If

Set wksTrans = DBEngine.Workspaces(0)
wksTrans.BeginTrans
strCommit = "Begun"

'Add payment
Set dbsTrans = CurrentDb()
Set rstTrans = dbsTrans.OpenRecordset("fin_transactions", dbOpenDynaset)
With rstTrans
    .AddNew
    !trn_cmt_id = Me!cmt_id
    !trn_date = Me.txtdate
    !trn_amount1 = -1 * Me!sor_salary
    !trn_tax1 = 0
    !trn_amount2 = -1 * Me!sor_salary
    !trn_balance = 0
    !trn_seq = 1
    !trn_transtype = Me!sor_transtype
    !trn_noloan = Me!sor_noloan
    .Update
    End With
rstTrans.Close

'Close salary order
Set rstTrans = dbsTrans.OpenRecordset("Select sor_srq_id,sor_status,sor_closedtg from fin_salorders Where sor_id = " & Me!sor_id)
rstTrans.Edit
strSrqId = rstTrans!sor_srq_id
rstTrans!sor_status = "Fulfilled"
rstTrans!sor_closedtg = GetNow()
rstTrans.Update
rstTrans.Close

'Close salary requisition
Set rstTrans = dbsTrans.OpenRecordset("Select srq_fulfilment,srq_status,srq_closedtg from hr_salreqs Where srq_id = " & strSrqId)
rstTrans.Edit
rstTrans!srq_fulfilment = Me!sor_salary
rstTrans!srq_status = "Fulfilled"
rstTrans!srq_closedtg = GetNow()
rstTrans.Update
rstTrans.Close

'Close commitment       'Me!sor_id is giving error - "Data has been changed"
Set rstTrans = dbsTrans.OpenRecordset("Select cmt_status from fin_commitments Where cmt_id = " & Me!cmt_id)
rstTrans.Edit
rstTrans!cmt_status = "Paid"
rstTrans.Update
rstTrans.Close

wksTrans.CommitTrans
strCommit = "Committed"

Me.Requery
Exit Sub


Error_Part:
If strCommit = "Begun" Then
    wksTrans.Rollback
    MsgBox "Commitment status cannot be changed." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub

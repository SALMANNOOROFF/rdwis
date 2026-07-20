VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_commitments_u_pcs_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From fin_commitments_u_pcs Where pcs_id = " & arrArgs(1)
Select Case arrArgs(0)
    Case "Awaited"
        MakeControlsVisible
        Me.cmt_remarks.Locked = False
        Me.cmt_remarks.BorderStyle = 1
    Case Else
        Me.cmdReverse.Visible = True
    End Select

Me.Visible = True
End Sub

Private Sub Form_Close()
Me.Dirty = False
End Sub

Private Sub chkComplete_AfterUpdate()
If Me.chkComplete = -1 Then
    Me.lblWarning.Visible = True
    Else
    Me.lblWarning.Visible = False
    End If
End Sub

Private Sub cmdSettle_Click()
Dim dbsTrans As Database
Dim rstTrans As Recordset
Dim lngPcsID As Long
Dim strCmtStatus As String

Me.Dirty = False
Set dbsTrans = CurrentDb()
'***For only status update
If Nz(Me.na, "") = "" And Nz(Me.nt, "") = "" And Me.chkComplete = True Then GoTo Update_Status
   
 
'***For payment addition with or without status update
'Date is required
If IsNull(Me.txtdate) Then
    MsgBox "Please enter date.", vbCritical
    Exit Sub
    End If

'Incomplete data is not allowed
If Nz(Me.na, "") = "" Or Nz(Me.nt, "") = "" Then
    MsgBox "Please enter complete data.", vbCritical
    Exit Sub
    End If

'Zero amount transaction is not allowed
If Me.nat = 0 Then
    MsgBox "Zero amount is not allowed. Please enter some amount.", vbCritical
    Exit Sub
    End If

'Add payment
Set rstTrans = dbsTrans.OpenRecordset("fin_transactions", dbOpenDynaset)
With rstTrans
    .AddNew
    !trn_cmt_id = Me!cmt_id
    !trn_date = Me.txtdate
    !trn_amount1 = -1 * Me.na
    !trn_tax1 = -1 * Me.nt
    !trn_amount2 = -1 * Me.nat
    !trn_balance = 0
    !trn_seq = 1
    !trn_transtype = Me!pcs_transtype
    !trn_noloan = Me!pcs_noloan
    .Update
    End With
rstTrans.Close

'Update status and re
Update_Status:
If Me.chkComplete = True Then
    Set rstTrans = dbsTrans.OpenRecordset("Select cmt_status From fin_commitments Where cmt_id = " & Me!cmt_id, dbOpenDynaset)
    rstTrans.Edit
    rstTrans!cmt_status = "Paid"
    rstTrans.Update
    rstTrans.Close
    End If
Me.Refresh

Application.Echo False
lngPcsID = Me!pcs_id
strCmtStatus = Me!cmt_status
DoCmd.Close
DoCmd.OpenForm "fin_commitments_u_pcs_detail", acNormal, , , , , strCmtStatus & "~" & lngPcsID
Application.Echo True

End Sub

Private Sub cmdReverse_Click()
Dim intResponse  As Integer
Dim lngRevId As Long

intResponse = MsgBox("A Data Revision Request to reverse the status of this commitment will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Commitment", Me!cmt_id, Me.pcs_intunt_id, 1)
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_commitments_u_pcs_detail"
DoCmd.Close acForm, "fin_commitments_u_pcs"

End Sub


Sub MakeControlsVisible()
'Visibility
Me.lblAddPayment.Visible = True
Me.txtdate.Visible = True
Me.chkComplete.Visible = True
Me.cmdSettle.Visible = True

'Visibility
Me.lblCorner.Visible = True
Me.lbla.Visible = True
Me.lblt.Visible = True
Me.lblat.Visible = True

Me.lblpcs.Visible = True
Me.pa.Visible = True
Me.pt.Visible = True
Me.pat.Visible = True

Me.lblalp.Visible = True
Me.aa.Visible = True
Me.at.Visible = True
Me.aat.Visible = True

Me.lblrem1.Visible = True
Me.ra1.Visible = True
Me.rt1.Visible = True
Me.rat1.Visible = True

Me.lblnwp.Visible = True
Me.na.Visible = True
Me.nt.Visible = True
Me.nat.Visible = True

Me.lblrem2.Visible = True
Me.ra2.Visible = True
Me.rt2.Visible = True
Me.rat2.Visible = True


















End Sub

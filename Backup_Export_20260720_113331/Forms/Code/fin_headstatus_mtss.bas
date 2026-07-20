VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_headstatus_mtss"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrInput() As Variant
Dim n As Long
Dim dcnStat As Scripting.Dictionary

Me.cmbAccount = Forms!vars.Parameter1
Me.hed_id = Forms!vars.Parameter1
Set dcnStat = GetHeadStatus(Me.cmbAccount, "ga")

'Basic Shares
Me.acc_allocation = dcnStat("AccAllocation")
Me.mtss_share = dcnStat("MtssShare")
Me.acc_share = dcnStat("AccShare")
Me.lblTax.Caption = IIf(dcnStat("TransType") = 1, "(Million Rupees without GST)", "(Million Rupees with GST)")
If Me.acc_allocation = 0 Then MsgBox "Project allocation not defined."
If Me.mtss_share = 0 Then MsgBox "MTSS allocation not defined."

'--------------------------------------------------------------------------

'Acc figures
Me.acc_received = dcnStat("AccReceived")
Me.acc_exp = dcnStat("AccExpenditure")
Me.acc_commit = dcnStat("AccCommitment")
Me.acc_ipc = dcnStat("AccInProcess")
Me.acc_balance = dcnStat("AccBalance")
Me.acc_avlbl = dcnStat("AccAvailable")
Me.acc_yettoberec = dcnStat("AccYetToBeRec")
Me.acc_canbespent = dcnStat("AccCanBeSpent")
Me.Visible = True

End Sub

Private Sub cmdExact_Click()
Dim objCtl As Control
For Each objCtl In Me.Controls
    If TypeOf objCtl Is TextBox Then
        objCtl.Format = "Standard"
        objCtl.DecimalPlaces = 2
        End If
    Next objCtl
    Me.lblTax.Caption = "(" & Right(Me.lblTax.Caption, Len(Me.lblTax.Caption) - 9)
    Me.txtBlank.SetFocus
    Me.cmdExact.Visible = False
End Sub

'---------------------------------------------------------------------------------

Private Sub btn_acc_received_Click()
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesinstall", acNormal, , , , acHidden
End Sub

Private Sub btn_acc_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_acc_exp1"
End Sub

Private Sub btn_acc_commits_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_acc_commitsoutst1"
End Sub

'---------------------------------------------------------------------------------

Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err

DoCmd.OpenReport "fin_headstatus_mtss", acViewReport, "", "", acNormal, IIf(Me.cmdExact.Visible = True, "Rounded", "Exact")


cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub


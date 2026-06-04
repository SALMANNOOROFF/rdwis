VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_headstatus"
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
Set dcnStat = GetHeadStatus(Me.cmbAccount, "gvpclj")

'Basic Shares
Me.acc_allocation = dcnStat("AccAllocation")
Me.mtss_share = dcnStat("MtssShare")
Me.rdw_share = dcnStat("RdwShare")
Me.pcc_share = dcnStat("PccShare")
Me.cf_share = dcnStat("CfShare")
Me.prj_share = dcnStat("PrjShare")
Me.lblTax.Caption = IIf(dcnStat("TransType") = 1, "(Million Rupees without GST)", "(Million Rupees with GST)")
If Me.acc_allocation = 0 Then MsgBox "Project allocation not defined."
If Me.mtss_share = 0 Then MsgBox "MTSS allocation not defined."
'--------------------------------------------------------------------------

'Pcc figures
Me.pcc_received = dcnStat("PccReceived")
Me.pcc_exp = dcnStat("PccExpenditure")
Me.pcc_commit = dcnStat("PccCommitment")
Me.pcc_ipc = dcnStat("PccInProcess")
Me.pcc_balance = dcnStat("PccBalance")
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.pcc_yettoberec = dcnStat("PccYetToBeRec")
Me.pcc_canbespent = dcnStat("PccCanBeSpent")

'CHRF figures
Me.cf_received = dcnStat("CfReceived")
Me.cf_exp = dcnStat("CfExpenditure")
Me.cf_commit = dcnStat("CfCommitment")
Me.cf_ipc = dcnStat("CfInProcess")
Me.cf_balance = dcnStat("CfBalance")
Me.cf_avlbl = dcnStat("CfAvailable")
Me.cf_yettoberec = dcnStat("CfYetToBeRec")
Me.cf_canbespent = dcnStat("CfCanBeSpent")

'Loans
Me.pcc_ownexp = dcnStat("PccOwnExp")
Me.others_loanstaken = dcnStat("OthersLoansTaken")
Me.pcc_loansgiven = dcnStat("PccLoansGiven")

'Receivables
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.acc_rcvmsncompleted = dcnStat("AccReceivableMsnCompleted")
Me.acc_rcvmsncurrent = dcnStat("AccReceivableMsnCurrent")

'Project figures
Me.prj_exp = dcnStat("PrjExpenditure")
Me.prj_commit = dcnStat("PrjCommitment")
Me.prj_ipc = dcnStat("PrjInProcess")
Me.prj_canbespent = dcnStat("PrjCanBeSpent")

'Visibilities
If Me.prj_share = Me.pcc_share Then Me.prj_share.Visible = False
If Me.pcc_exp = Me.pcc_ownexp And Me.others_loanstaken = 0 Then
    Me.txtWarnCat.Visible = False
    Me.lblGreen.Visible = False
    Me.boxGreen.Visible = False
    Me.prj_exp.Visible = False
    Me.prj_commit.Visible = False
    Me.prj_ipc.Visible = False
    Me.prj_canbespent.Visible = False
    Me.cmd_prj_exp.Visible = False
    Me.cmd_prj_commit.Visible = False
    Me.cmd_prj_ipc.Visible = False
    Me.txtg1.Visible = False
    Me.txtg2.Visible = False
    Me.boxExpbd.Visible = False
    Me.pcc_ownexp.Visible = False
    Me.others_loanstaken.Visible = False
    Me.pcc_loansgiven.Visible = False
    Me.cmd_pcc_ownexp.Visible = False
    Me.cmd_others_loanstaken.Visible = False
    Me.cmd_pcc_loansgiven.Visible = False
    End If

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

Private Sub cmd_pcc_received_Click()
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesinstall", acNormal, , , , acHidden
End Sub

Private Sub cmd_pcc_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_exp1"
End Sub

Private Sub cmd_pcc_commit_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_pcc_commitsoutst1"
End Sub

Private Sub cmd_pcc_ipc_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_ipc1"
End Sub

'---------------------------------------------------------------------------------

Private Sub cmd_cf_received_Click()
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesinstall", acNormal, , , , acHidden
End Sub

Private Sub cmd_cf_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_cf_exp1"
End Sub

Private Sub cmd_cf_commit_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_cf_commitsoutst1"
End Sub

Private Sub cmd_cf_ipc_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_cf_ipc1"
End Sub

'---------------------------------------------------------------------------------

Private Sub cmd_pcc_ownexp_Click()

End Sub

'Private Sub cmd_pcc_loansgiven_Click()
'DoCmd.OpenForm "wait"
'DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_loansgiven1"
'End Sub

Private Sub cmd_others_loanstaken_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_others_loanstaken1"
End Sub

'---------------------------------------------------------------------------------

Private Sub cmd_prj_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_prj_exp1"
End Sub

Private Sub cmd_prj_commit_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_prj_commitsoutst1"
End Sub

'---------------------------------------------------------------------------------


Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err

DoCmd.OpenReport "fin_headstatus", acViewReport, "", "", acNormal, IIf(Me.cmdExact.Visible = True, "Rounded", "Exact")

cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub



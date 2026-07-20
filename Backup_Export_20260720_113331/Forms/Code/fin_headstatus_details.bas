VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_headstatus_details"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Detail_DblClick(Cancel As Integer)
Dim var1
Dim var2
var1 = Me.prj_commit
var2 = Nz(Me.prj_commit_a) + Nz(Me.prj_commit_b) + Nz(Me.prj_commit_c) + Nz(Me.prj_commit_d) + Nz(Me.prj_commit_e)
MsgBox var1 & vbCrLf & var2 & vbCrLf & (var1 = var2)
End Sub

Private Sub Form_Open(Cancel As Integer)
Dim dbsShd As Database
Dim rstShd As Recordset
Dim dcnStat As Scripting.Dictionary
Dim n As Integer
Dim c As Integer

Me.cmbAccount = Forms!vars.Parameter1
Set dcnStat = GetHeadStatus(Me.cmbAccount, "gvpljs")

'Basic Shares
Me.acc_allocation = dcnStat("AccAllocation")
Me.mtss_share = dcnStat("MtssShare")
Me.rdw_share = dcnStat("RdwShare")
Me.cf_share = dcnStat("CfShare")
Me.pcc_share = dcnStat("PccShare")
Me.prj_share = dcnStat("PrjShare")
Me.lblTax.Caption = IIf(dcnStat("TransType") = 1, "(Million Rupees without GST)", "(Million Rupees with GST)")
If Me.acc_allocation = 0 Then MsgBox "Project allocation not defined."
If Me.mtss_share = 0 Then MsgBox "MTSS allocation not defined."

'Pcc figures
Me.pcc_received = dcnStat("PccReceived")
Me.pcc_exp = dcnStat("PccExpenditure")
Me.pcc_commit = dcnStat("PccCommitment")
Me.pcc_ipc = dcnStat("PccInProcess")
Me.pcc_balance = dcnStat("PccBalance")
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.pcc_yettoberec = dcnStat("PccYetToBeRec")
Me.pcc_canbespent = dcnStat("PccCanBeSpent")

'Receivables
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.acc_rcvmsncompleted = dcnStat("AccReceivableMsnCompleted")
Me.acc_rcvmsncurrent = dcnStat("AccReceivableMsnCurrent")

'Loans
Me.pcc_ownexp = dcnStat("PccOwnExp")
Me.others_loanstaken = dcnStat("OthersLoansTaken")
Me.pcc_loansgiven = dcnStat("PccLoansGiven")

'Project
Me.prj_exp = dcnStat("PrjExpenditure")
Me.prj_commit = dcnStat("PrjCommitment")
Me.prj_ipc = dcnStat("PrjInProcess")
Me.prj_canbespent = dcnStat("PrjCanBeSpent")

'Project subhead and graph source (fin_stateshd_temp)
Set dbsShd = CurrentDb()
dbsShd.Execute "Delete From fin_stateshd_temp"
Set rstShd = dbsShd.OpenRecordset("fin_stateshd_temp")
For n = 1 To 5
    If dcnStat.Exists("PrjShdName" & Chr(64 + n)) Then
        Me.Controls("prj_name_" & Chr(96 + n)) = dcnStat("PrjShdName" & Chr(64 + n))
        Me.Controls("prj_alloc_" & Chr(96 + n)) = dcnStat("PrjShdAllocation" & Chr(64 + n))
        Me.Controls("prj_exp_" & Chr(96 + n)) = dcnStat("PrjShdExpenditure" & Chr(64 + n))
        Me.Controls("prj_commit_" & Chr(96 + n)) = dcnStat("PrjShdCommitment" & Chr(64 + n))
        Me.Controls("prj_ipc_" & Chr(96 + n)) = dcnStat("PrjShdInProcess" & Chr(64 + n))
        Me.Controls("prj_canbespent_" & Chr(96 + n)) = dcnStat("PrjShdCanBeSpent" & Chr(64 + n))
        Me.Controls("prj_name_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_alloc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_ipc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_canbespent_" & Chr(96 + n)).Visible = True
        Me.Controls("box_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_ipc_" & Chr(96 + n)).Visible = True
        Me.Controls("graph_" & Chr(96 + n)).Visible = True
        With rstShd
            .AddNew
            !alphabet = Chr(96 + n)
            !Type = dcnStat("PrjShdName" & Chr(64 + n))
            !allocation = dcnStat("PrjShdAllocation" & Chr(64 + n))
            !expenditure = dcnStat("PrjShdExpenditure" & Chr(64 + n))
            !Commitment = dcnStat("PrjShdCommitment" & Chr(64 + n))
            ![In Process] = dcnStat("PrjShdInProcess" & Chr(64 + n))
            ![Can be Spent] = dcnStat("PrjShdCanBeSpent" & Chr(64 + n))
            !Sorter = IIf(dcnStat("PrjShdName" & Chr(64 + n)) = "Misc", "zzz", dcnStat("PrjShdName" & Chr(64 + n)))
            .Update
            End With
        If Me.Controls("prj_name_" & Chr(96 + n)) = "HR" Then Me.txtHrSubhead = Chr(96 + n)
        c = c + 1
        End If
    Next n

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
    Me.graph.Left = Me.graph.Left - 300
    Me.lblProject.Left = Me.lblProject.Left + 1500
    Me.pcc_share.Left = Me.pcc_share.Left + 1500
    Me.boxBlue.Left = Me.boxBlue.Left + 1500
    Me.pcc_received.Left = Me.pcc_received.Left + 1500
    Me.pcc_exp.Left = Me.pcc_exp.Left + 1500
    Me.pcc_balance.Left = Me.pcc_balance.Left + 1500
    Me.pcc_commit.Left = Me.pcc_commit.Left + 1500
    Me.pcc_ipc.Left = Me.pcc_ipc.Left + 1500
    Me.pcc_avlbl.Left = Me.pcc_avlbl.Left + 1500
    Me.pcc_yettoberec.Left = Me.pcc_yettoberec.Left + 1500
    Me.pcc_canbespent.Left = Me.pcc_canbespent.Left + 1500
    Me.cmd_pcc_received.Left = Me.cmd_pcc_received.Left + 1500
    Me.cmd_pcc_exp.Left = Me.cmd_pcc_exp.Left + 1500
    Me.cmd_pcc_commit.Left = Me.cmd_pcc_commit.Left + 1500
    Me.cmd_pcc_ipc.Left = Me.cmd_pcc_ipc.Left + 1500
    Me.boxRcv.Left = Me.boxRcv.Left + 1500
    Me.acc_rcvmsncompleted.Left = Me.acc_rcvmsncompleted.Left + 1500
    Me.acc_rcvmsncurrent.Left = Me.acc_rcvmsncurrent.Left + 1500
    Me.acc_availableafter.Left = Me.acc_availableafter.Left + 1500
    Me.lbl_received.Left = Me.lbl_received.Left + 1500
    Me.lbl_exp.Left = Me.lbl_exp.Left + 1500
    Me.lbl_balance.Left = Me.lbl_balance.Left + 1500
    Me.lbl_commit.Left = Me.lbl_commit.Left + 1500
    Me.lbl_ipc.Left = Me.lbl_ipc.Left + 1500
    Me.lbl_avlbl.Left = Me.lbl_avlbl.Left + 1500
    Me.lbl_yettoberec.Left = Me.lbl_yettoberec.Left + 1500
    Me.lbl_canbespent.Left = Me.lbl_canbespent.Left + 1500
    Me.lbl_rcvmsncompleted.Left = Me.lbl_rcvmsncompleted.Left + 1500
    Me.lbl_loansnet.Left = Me.lbl_loansnet.Left + 1500
    Me.lbl_availableafter.Left = Me.lbl_availableafter.Left + 1500
    End If

'If c > 1 Then
'    Me.graphSubheads.Width = Me.graphSubheads.Width + (c - 1) * 3.148 * 567
'    Me.cmdECBreakdown.Left = Me.cmdECBreakdown.Left + (c - 1) * 3.148 * 567
'    Me.cmdECTimeline.Left = Me.cmdECTimeline.Left + (c - 1) * 3.148 * 567
'    Me.cmdSalForecast.Left = Me.cmdSalForecast.Left + (c - 1) * 3.148 * 567
'    Me.cmdTimelineCtr.Left = Me.cmdTimelineCtr.Left + (c - 1) * 3.148 * 567
'    End If

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

Private Sub cmdECBreakdown_Click()
Dim dbsEcb As Database
Dim qdfEcb As QueryDef
On Error GoTo cmdECBreakdown_Click_Err

DoCmd.Hourglass True
Set dbsEcb = CurrentDb()
dbsEcb.Execute "Delete From fin_ecbreakdown_temp"
Set qdfEcb = dbsEcb.QueryDefs("fin_ecbreakdown_so_tempadder")
qdfEcb.Parameters(0) = Forms!vars!Parameter1
qdfEcb.Execute
Set qdfEcb = dbsEcb.QueryDefs("fin_ecbreakdown_pc_tempadder")
qdfEcb.Parameters(0) = Forms!vars!Parameter1
qdfEcb.Execute
DoCmd.Hourglass False

DoCmd.OpenForm "fin_ecbreakdown_chart", acNormal, "", "", , acNormal

cmdECBreakdown_Click_Exit:
    Exit Sub

cmdECBreakdown_Click_Err:
    MsgBox Error$
    Resume cmdECBreakdown_Click_Exit
End Sub

Private Sub cmdECTimeline_Click()
Dim dbsEct As Database
Dim qdfEct As QueryDef
On Error GoTo cmdECTimeline_Click_Err

DoCmd.Hourglass True
Set dbsEct = CurrentDb()
dbsEct.Execute "Delete From fin_ectimeline_temp"
Set qdfEct = dbsEct.QueryDefs("fin_ectimeline_so_tempadder")
qdfEct.Parameters(0) = Forms!vars!Parameter1
qdfEct.Execute
Set qdfEct = dbsEct.QueryDefs("fin_ectimeline_pc_tempadder")
qdfEct.Parameters(0) = Forms!vars!Parameter1
qdfEct.Execute
DoCmd.Hourglass False

DoCmd.OpenForm "fin_ectimeline_chart", acNormal, "", "", , acNormal

cmdECTimeline_Click_Exit:
    Exit Sub

cmdECTimeline_Click_Err:
    MsgBox Error$
    Resume cmdECTimeline_Click_Exit

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
Private Sub cmd_pcc_loansgiven_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_loansgiven1"
End Sub

Private Sub cmd_pcc_ownexp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_ownexp1"
End Sub

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

Private Sub cmd_prj_ipc_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_prj_ipc1"
End Sub
'---------------------------------------------------------------------------------

Private Sub btn_prj_exp_a_Click()
ShowSubheadExp "a"
End Sub

Private Sub btn_prj_exp_b_Click()
ShowSubheadExp "b"
End Sub

Private Sub btn_prj_exp_c_Click()
ShowSubheadExp "c"
End Sub

Private Sub btn_prj_exp_d_Click()
ShowSubheadExp "d"
End Sub

Private Sub btn_prj_exp_e_Click()
ShowSubheadExp "e"
End Sub

Sub ShowSubheadExp(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_exp1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_exp1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sts_prj_exp1_subhead"
End Sub

Private Sub btn_prj_ipc_a_Click()
ShowSubheadIPC "a"
End Sub

Private Sub btn_prj_ipc_b_Click()
ShowSubheadIPC "b"
End Sub

Private Sub btn_prj_ipc_c_Click()
ShowSubheadIPC "c"
End Sub

Private Sub btn_prj_ipc_d_Click()
ShowSubheadIPC "d"
End Sub

Private Sub btn_prj_ipc_e_Click()
ShowSubheadIPC "e"
End Sub

Sub ShowSubheadIPC(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_ipc1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_ipc1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sts_prj_ipc1_subhead"
End Sub

Private Sub btn_prj_commit_a_Click()
ShowSubheadCommits "a"
End Sub

Private Sub btn_prj_commit_b_Click()
ShowSubheadCommits "b"
End Sub

Private Sub btn_prj_commit_c_Click()
ShowSubheadCommits "c"
End Sub

Private Sub btn_prj_commit_d_Click()
ShowSubheadCommits "d"
End Sub

Private Sub btn_prj_commit_e_Click()
ShowSubheadCommits "e"
End Sub

Sub ShowSubheadCommits(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_commitsoutst1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_commitsoutst1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sts_prj_commitsoutst1_subhead"
End Sub

'---------------------------------------------------------------------------------
'---------------------------------------------------------------------------------

Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err

DoCmd.OpenReport "fin_headstatus_details", acViewReport, "", "", acNormal, IIf(Me.cmdExact.Visible = True, "Rounded", "Exact")

cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub

Private Sub cmdReverse_Click()
DoCmd.OpenForm "fin_headstatus_rev", acNormal, "", "", , acHidden
End Sub


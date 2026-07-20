VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_mtss_multiple"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
StartupSteps Me.Name
Me.Visible = True
End Sub

Private Sub Form_Current()
SetWarningsGatePass
SetWarningsContract
SetWarningsPersonalData
End Sub

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub

Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
End Sub

Private Sub cmdMyUnit_Click()
On Error GoTo cmdMyUnit_Click_Err

DoCmd.OpenForm "start_all_single", acNormal, "", "", , acNormal

cmdMyUnit_Click_Exit:
    Exit Sub

cmdMyUnit_Click_Err:
    MsgBox Error$
    Resume cmdMyUnit_Click_Exit

End Sub

Private Sub cmdSalHeads_Click()
On Error GoTo cmdSalHeads_Click_Err

    DoCmd.OpenForm "fin_empeffheads_u_active", acNormal, "", "", , acNormal


cmdSalHeads_Click_Exit:
    Exit Sub

cmdSalHeads_Click_Err:
    MsgBox Error$
    Resume cmdSalHeads_Click_Exit

End Sub

Private Sub cmdContractsVerif_Click()
On Error GoTo cmdContractsVerif_Click_Err

DoCmd.OpenForm "fin_contractsverif", acNormal, "", "", , acNormal


cmdContractsVerif_Click_Exit:
    Exit Sub

cmdContractsVerif_Click_Err:
    MsgBox Error$
    Resume cmdContractsVerif_Click_Exit

End Sub

Private Sub cmdSalOrdersDraft_Click()
On Error GoTo cmdSalOrdersDraft_Click_Err

DoCmd.OpenForm "fin_salorders_u", acNormal, "", "", , acHidden, "Draft"


cmdSalOrdersDraft_Click_Exit:
    Exit Sub

cmdSalOrdersDraft_Click_Err:
    MsgBox Error$
    Resume cmdSalOrdersDraft_Click_Exit

End Sub

Private Sub cmdSalOrdersClosed_Click()
On Error GoTo cmdSalOrdersClosed_Click_Err

DoCmd.OpenForm "fin_salorders_u", acNormal, "", "", , acHidden, "Closed"


cmdSalOrdersClosed_Click_Exit:
    Exit Sub

cmdSalOrdersClosed_Click_Err:
    MsgBox Error$
    Resume cmdSalOrdersClosed_Click_Exit

End Sub


Private Sub cmdSalReqsWaiting_Click()
On Error GoTo cmdSalReqsWaiting_Click_Err

DoCmd.OpenForm "hr_salreqs_u", acNormal, "", "", , acHidden, "Waiting"


cmdSalReqsWaiting_Click_Exit:
    Exit Sub

cmdSalReqsWaiting_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsWaiting_Click_Exit

End Sub


Private Sub cmdEmpCurrent_Click()
On Error GoTo cmdEmpCurrent_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"

cmdEmpCurrent_Click_Exit:
    Exit Sub

cmdEmpCurrent_Click_Err:
    MsgBox Error$
    Resume cmdEmpCurrent_Click_Exit
End Sub

Private Sub cmdEmpPrev_Click()
On Error GoTo cmdEmpPrev_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Previous~"

cmdEmpPrev_Click_Exit:
    Exit Sub

cmdEmpPrev_Click_Err:
    MsgBox Error$
    Resume cmdEmpPrev_Click_Exit
End Sub

Private Sub cmdActiveEmp_Click()
On Error GoTo cmdActiveEmp_Click_Err

DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal


cmdActiveEmp_Click_Exit:
    Exit Sub

cmdActiveEmp_Click_Err:
    MsgBox Error$
    Resume cmdActiveEmp_Click_Exit

End Sub


Private Sub cmdExServicemen_Click()
On Error GoTo cmdExServicemen_Click_Err

    DoCmd.OpenReport "hr_emps_exservicemen_doc", acViewReport, "", "", acNormal


cmdExServicemen_Click_Exit:
    Exit Sub

cmdExServicemen_Click_Err:
    MsgBox Error$
    Resume cmdExServicemen_Click_Exit

End Sub

Private Sub cmdPurReqOpen_Click()
On Error GoTo cmdPurReqOpen_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Open"


cmdPurReqOpen_Click_Exit:
    Exit Sub

cmdPurReqOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurReqOpen_Click_Exit

End Sub

Private Sub cmdPurReqClosed_Click()
On Error GoTo cmdPurReqClosed_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Closed"


cmdPurReqClosed_Click_Exit:
    Exit Sub

cmdPurReqClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurReqClosed_Click_Exit

End Sub


Private Sub cmdPurCaseOpen_Click()
On Error GoTo cmdPurCaseOpen_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Open"

cmdPurCaseOpen_Click_Exit:
    Exit Sub

cmdPurCaseOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseOpen_Click_Exit

End Sub


Private Sub cmdPurCaseClosed_Click()
On Error GoTo cmdPurCaseClosed_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Closed"

cmdPurCaseClosed_Click_Exit:
    Exit Sub

cmdPurCaseClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseClosed_Click_Exit

End Sub

Private Sub cmdPurRecClosed_Click()
On Error GoTo cmdPurRecClosed_Click_Err

    DoCmd.OpenForm "pur_purreceipts_u", acNormal, "", "", , acNormal, "Closed"


cmdPurRecClosed_Click_Exit:
    Exit Sub

cmdPurRecClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurRecClosed_Click_Exit

End Sub

Private Sub cmdAccStatus_Click()
On Error GoTo cmdAccStatus_Click_Err

If IsNull(Me.cmbAccount) Then Exit Sub
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_headstatus", acNormal, "", "", , acHidden

cmdAccStatus_Click_Exit:
    Exit Sub

cmdAccStatus_Click_Err:
    MsgBox Error$
    Resume cmdAccStatus_Click_Exit

End Sub

Private Sub cmdLoans_Click()
OpenLoansForm
End Sub

Private Sub cmdProjectShares_Click()

If IsNull(Me.cmbAccount) Then Exit Sub
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesalloc_plus", acNormal, "", "", , acNormal

End Sub


Private Sub cmdSettlePcs_Click()
On Error GoTo cmdSettlePcs_Click_Err

DoCmd.OpenForm "fin_purcases_u_unpaid", acNormal, "", "", , acNormal


cmdSettlePcs_Click_Exit:
    Exit Sub

cmdSettlePcs_Click_Err:
    MsgBox Error$
    Resume cmdSettlePcs_Click_Exit

End Sub


Private Sub cmdSettleSo_Click()
On Error GoTo cmdSettleSo_Click_Err

DoCmd.OpenForm "fin_salorders_u_unpaid", acNormal, "", "", , acNormal


cmdSettleSo_Click_Exit:
    Exit Sub

cmdSettleSo_Click_Err:
    MsgBox Error$
    Resume cmdSettleSo_Click_Exit

End Sub


Private Sub cmdMReturn_Click()
On Error GoTo cmdMReturn_Click_Err

Forms!vars!Parameter1 = FirstDatePrevMonth(Date)
Forms!vars!Parameter2 = LastDatePrevMonth(Date)
DoCmd.OpenReport "fin_headstatusall_mreturn", acViewReport, "", "", acNormal


cmdMReturn_Click_Exit:
    Exit Sub

cmdMReturn_Click_Err:
    MsgBox Error$
    Resume cmdMReturn_Click_Exit

End Sub


'------------------------------------------------------------
' cmdPurchaseFirms_Click
'
'------------------------------------------------------------
Private Sub cmdPurchaseFirms_Click()
On Error GoTo cmdPurchaseFirms_Click_Err

    DoCmd.OpenReport "fin_firms_overall", acViewReport, "", "", acNormal


cmdPurchaseFirms_Click_Exit:
    Exit Sub

cmdPurchaseFirms_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirms_Click_Exit

End Sub


'------------------------------------------------------------
' cmdPurchaseFirmsDetailed_Click
'
'------------------------------------------------------------
Private Sub cmdPurchaseFirmsDetailed_Click()
On Error GoTo cmdPurchaseFirmsDetailed_Click_Err

    DoCmd.OpenReport "fin_firms_projectwise", acViewReport, "", "", acNormal


cmdPurchaseFirmsDetailed_Click_Exit:
    Exit Sub

cmdPurchaseFirmsDetailed_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirmsDetailed_Click_Exit

End Sub


'------------------------------------------------------------
' cmdPurchaseFirmSingle_Click
'
'------------------------------------------------------------
Private Sub cmdPurchaseFirmSingle_Click()
On Error GoTo cmdPurchaseFirmSingle_Click_Err

    DoCmd.OpenForm "frm_firm_choose", acNormal, "", ""


cmdPurchaseFirmSingle_Click_Exit:
    Exit Sub

cmdPurchaseFirmSingle_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirmSingle_Click_Exit

End Sub

Private Sub cmdAccountsStatus_Click()
On Error GoTo cmdAccountsStatus_Click_Err

    DoCmd.OpenForm "fin_headstatusall", acNormal, "", "", , acNormal


cmdAccountsStatus_Click_Exit:
    Exit Sub

cmdAccountsStatus_Click_Err:
    MsgBox Error$
    Resume cmdAccountsStatus_Click_Exit

End Sub

Private Sub cmdPCAwaited_Click()
On Error GoTo cmdPCAwaited_Click_Err

    DoCmd.OpenReport "fin_purcases_awaited", acViewReport, "", "", acNormal


cmdPCAwaited_Click_Exit:
    Exit Sub

cmdPCAwaited_Click_Err:
    MsgBox Error$
    Resume cmdPCAwaited_Click_Exit

End Sub

Private Sub cmdChrf_Click()
On Error GoTo cmdChrf_Click_Err
Dim dbsChrf As Database
Dim qryChrf As QueryDef

DoCmd.Hourglass True
Set dbsChrf = CurrentDb()
dbsChrf.Execute "Delete From fin_chrfstatus_open_temp"
Set qryChrf = dbsChrf.QueryDefs("fin_chrfstatus_open_temp_adder")
qryChrf.Execute
DoCmd.Hourglass False

DoCmd.OpenReport "fin_chrfstatusall", acViewReport, "", "", acNormal

cmdChrf_Click_Exit:
    Exit Sub

cmdChrf_Click_Err:
    MsgBox Error$
    Resume cmdChrf_Click_Exit

End Sub

Private Sub cmdAddAccount_Click()
On Error GoTo cmdAddAccount_Click_Err

    DoCmd.OpenForm "fin_headstatusall", acNormal, "", "", , acNormal


cmdAddAccount_Click_Exit:
    Exit Sub

cmdAddAccount_Click_Err:
    MsgBox Error$
    Resume cmdAddAccount_Click_Exit

End Sub

Private Sub cmdTransfersFund_Click()
On Error GoTo cmdTransfersFund_Click_Err

    DoCmd.OpenForm "fin_transfersfund_u_unpaid", acNormal, "", "", , acNormal


cmdTransfersFund_Click_Exit:
    Exit Sub

cmdTransfersFund_Click_Err:
    MsgBox Error$
    Resume cmdTransfersFund_Click_Exit

End Sub



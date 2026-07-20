VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_headstatus"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
Dim objCtl As Control

Me.lblTax.Caption = Forms!fin_headstatus.lblTax.Caption

'Exactness
If Me.OpenArgs = "Exact" Then
    For Each objCtl In Me.Controls
        If TypeOf objCtl Is TextBox Then
            objCtl.Format = "Standard"
            objCtl.DecimalPlaces = 0
            End If
        Next objCtl
        End If

'Visibilities
If Forms!fin_headstatus.prj_share.Visible = False Then Me.prj_share.Visible = False
If Forms!fin_headstatus.prj_exp.Visible = False Then
    Me.txtWarnCat.Visible = False
    Me.lblGreen.Visible = False
    Me.boxGreen.Visible = False
    Me.prj_exp.Visible = False
    Me.prj_commit.Visible = False
    Me.prj_ipc.Visible = False
    Me.prj_canbespent.Visible = False
    Me.boxExpbd.Visible = False
    Me.pcc_ownexp.Visible = False
    Me.others_loanstaken.Visible = False
    Me.pcc_loansgiven.Visible = False
    
'    Me.lblProject.Left = 4125       '(+1370)
'    Me.pcc_share.Left = 4125
'    Me.boxBlue.Left = 4125
'    Me.pcc_received.Left = 4172
'    Me.pcc_exp.Left = 4172
'    Me.pcc_balance.Left = 4172
'    Me.pcc_commit.Left = 4172
'    Me.pcc_avlbl.Left = 4172
'    Me.pcc_yettoberec.Left = 4172
'    Me.pcc_canbespent.Left = 4172
'    Me.lneBal1.Left = 4125
'    Me.lneBal2.Left = 4125
    Me.lneBal1.Width = 1425
    Me.lneBal2.Width = 1425
'    Me.lneAvl1.Left = 4125
'    Me.lneAvl2.Left = 4125
    Me.lneAvl1.Width = 1425
    Me.lneAvl2.Width = 1425
'
'    Me.boxRcv.Left = 4125
'    Me.acc_rcvmsncompleted.Left = 4172
'    Me.acc_rcvmsncurrent.Left = 4172
'    Me.acc_availableafter.Left = 4172
'
'    Me.lbl_received.Left = 1430
'    Me.lbl_exp.Left = 1430
'    Me.lbl_balance.Left = 1430
'    Me.lbl_commit.Left = 1430
'    Me.lbl_avlbl.Left = 1430
'    Me.lbl_yettoberec.Left = 1430
'    Me.lbl_canbespent.Left = 1430
'    Me.lbl_rcvmsncompleted.Left = 1430
'    Me.lbl_loansnet.Left = 1430
'    Me.lbl_availableafter.Left = 1430
    End If

End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_quotes_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
arrArgs = Split(Me.OpenArgs, "~")

Me.RecordSource = "Select * From pur_quotes Where qte_id = " & arrArgs(1)
Select Case arrArgs(0)
    Case "NewRecord", "Draft", "Under Revision"
        Select Case getVar("varMode")
            Case "approver-s", "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdDelete.Visible = True
            End Select
    End Select
If Me!qte_quotetype = 2 Then
    Me.qte_intprice.Visible = True
    Me.qte_inttax.Visible = True
    Me.qte_midtax.Visible = True
    End If
If Not getVar("varMode") Like "viewer*" Then
    Me.list_firmname.Visible = True
    Me.list_firmname.SetFocus
    Me.cmdAddFirm.Visible = True
    End If
Me.Visible = True
End Sub

Private Sub Form_AfterUpdate()
Forms!pur_purcases_detail.chkDataUpdateRequired = -1
End Sub

Private Sub qte_inttax_AfterUpdate()
Me!qte_inttax = Round(Me!qte_inttax, 0)
Me!qte_midprice = Me!qte_intprice + Me!qte_inttax
Me!qte_price = Me!qte_midprice + Me!qte_midtax
End Sub

Private Sub qte_midtax_AfterUpdate()
Me!qte_midtax = Round(Me!qte_midtax, 0)
Me!qte_midprice = Me!qte_intprice + Me!qte_inttax
Me!qte_price = Me!qte_midprice + Me!qte_midtax
End Sub

Private Sub cmdAddFirm_Click()
Dim strFirmName As String
strFirmName = InputBox("Please Enter Name of the firm to be added", "Add Firm")
If strFirmName = "" Then Exit Sub
CurrentDb.Execute "Insert Into frm_firmz (frm_name) Values ('" & strFirmName & "');"
Me.Refresh
End Sub

Private Sub cmdDelete_Click()
Dim intResponse  As Integer

intResponse = MsgBox("Are you sure you want to delete this quote?", 4, "Deletion Confirmation")
If intResponse = 6 Then
    CurrentDb.Execute "DELETE FROM pur_quotes WHERE qte_id = " & Me!qte_id
    Forms!pur_purcases_detail.chkDataUpdateRequired = -1
    DoCmd.Close
    End If
End Sub



Public Sub UpdateQuotePrices()
Dim arrMatrix() As Variant

arrMatrix = GetTaxes("qte", Me!qte_id)
Me!qte_intprice = arrMatrix(0)
If Me.qte_quotetype = 2 Then
    Me!qte_inttax = arrMatrix(1)
    Me!qte_midtax = arrMatrix(2)
    Else
    Me!qte_inttax = 0
    Me!qte_midtax = 0
    End If
Me!qte_midprice = Me!qte_intprice + Me!qte_inttax
Me!qte_price = Me!qte_midprice + Me!qte_midtax
End Sub



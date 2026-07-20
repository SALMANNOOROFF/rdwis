VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreceipts_u"
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
    Case "Draft"
        Me.lblHeader.Caption = "Purchase Receipts - Draft"
        Me.RecordSource = "pur_purreceipts_u_draft"
    Case "Closed"
        Me.lblHeader.Caption = "Purchase Receipts - Closed"
        Me.RecordSource = "pur_purreceipts_u_closed"
    End Select
Me.Visible = True

End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdPcs_Click()
DoCmd.OpenForm "pur_purreceipts_detail", acNormal, , , , acHidden, Me!prt_status & "~" & Me.prt_id
End Sub

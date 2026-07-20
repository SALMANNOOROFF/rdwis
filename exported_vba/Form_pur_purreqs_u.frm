VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreqs_u"
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
    Me.RecordSource = "pur_purreqs_u_draft"
    Me.lblHeader.Caption = "Purchase Requisitions - Draft"
    Case "Open"
    Case "Closed"
    Me.RecordSource = "pur_purreqs_u_closed"
    Me.lblHeader.Caption = "Purchase Requisitions - Closed"
    End Select
Me.Visible = True

End Sub

Private Sub prq_id_DblClick(Cancel As Integer)
Dim strArgs As String
Dim strDataset As String

strDataset = Me!prq_status
DoCmd.OpenForm "pur_purreqs_detail", acNormal, , , , acHidden, strDataset & "~" & Me!prq_id
End Sub

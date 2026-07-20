VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_commitments_u_pcs"
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
        Me.lblHeader.Caption = "Open Commitments - Purchase Cases"
        Me.RecordSource = "fin_commitments_u_pcs_open"
    Case "Closed"
        Me.lblHeader.Caption = "Closed Commitments - Purchase Cases"
        Me.RecordSource = "fin_commitments_u_pcs_closed"
    Case "FalseAwaited"
        Me.lblHeader.Caption = "Commitments Falsely Open"
        Me.RecordSource = "Select * From fin_commitments_u_pcs_open Where cmt_id In " & _
                          "(Select cmt_id From fin_warn_falsepocommits_awaited) "
    End Select

Me.Visible = True
End Sub

Private Sub cmdInv_Click()
DoCmd.OpenForm "fin_commitments_u_pcs_detail", acNormal, , , , , Me.cmt_status & "~" & Me.pcs_id
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

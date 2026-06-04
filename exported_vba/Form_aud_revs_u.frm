VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_aud_revs_u"
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
        Me.lblHeader.Caption = "Data Revision Cases - Draft"
        Me.RecordSource = "aud_revs_u_draft"
    Case "Open"
        Me.lblHeader.Caption = "Data Revision Cases - Open"
        Me.RecordSource = "aud_revs_u_open"
    Case "Closed"
        Me.lblHeader.Caption = "Data Revision Cases - Closed"
        Me.RecordSource = "aud_revs_u_closed"
    End Select

Me.Visible = True

End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdMsn_Click()
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, Me!rev_status & "~" & Me!rev_id

End Sub

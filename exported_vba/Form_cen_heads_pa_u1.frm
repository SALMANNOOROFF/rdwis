VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_heads_pa_u1"
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
        Me.RecordSource = "cen_heads_paopen_u"
        Me.lbl_title.Caption = "Open Accounts"
        If getVar("varMode") = "approver-m" Then AllowEditsAdvanced Me.Name, False, False
    Case "Closed"
        Me.RecordSource = "cen_heads_paclosed_u"
        Me.lbl_title.Caption = "Closed Accounts"
    End Select
Me.Visible = True
End Sub

Private Sub cmdReverse_Click()
Dim strHeadStatus As String

strHeadStatus = IIf(IsNull(Me!hed_closedt), "Open", "Closed")
DoCmd.OpenForm "cen_heads_rev", acNormal, "", "", , acHidden, strHeadStatus

End Sub

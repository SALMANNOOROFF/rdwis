VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_firms_detail"
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
    Case "View"
        Me.RecordSource = "Select * From frm_firms Where frm_id = " & arrArgs(1)
        If getVar("varUnitArea") = "fin" And getVar("varMode") <> "viewer*" Then AllowEditsAdvanced Me.Name, True, True
    Case "Add"
        AllowEditsAdvanced Me.Name, True, True
        Me.frm_name.Locked = False
        Me.AllowAdditions = True
        Me.AllowDeletions = True
        Me.DataEntry = True
        Me.lblTitle.Caption = "Add Firms"
        End Select
Me.Visible = True
End Sub

Private Sub cmpReport_Click()
On Error GoTo cmpReport_Click_Err

Me.Dirty = False
DoCmd.OpenReport "frm_firms", acViewReport, "", "", acNormal

cmpReport_Click_Exit:
    Exit Sub

cmpReport_Click_Err:
    MsgBox Error$
    Resume cmpReport_Click_Exit

End Sub




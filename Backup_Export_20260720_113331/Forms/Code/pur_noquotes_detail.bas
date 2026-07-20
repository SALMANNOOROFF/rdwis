VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_noquotes_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
arrArgs = Split(Me.OpenArgs, "~")

Me.RecordSource = "Select * From pur_noquotes Where nqt_pcs_id = " & arrArgs(1)
Select Case arrArgs(0)
    Case "NewRecord"
        Select Case getVar("varMode")
            Case "approver-s", "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.AllowAdditions = True
                Me.AllowDeletions = True
                Me.DataEntry = True
                Me.nqt_pcs_id.DefaultValue = arrArgs(1)
                Me.cmdDelete.Visible = True
            End Select
    Case "Draft", "Under Revision"
        Select Case getVar("varMode")
            Case "approver-s", "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.AllowDeletions = True
                Me.cmdDelete.Visible = True
            End Select
    End Select
Me.Visible = True
End Sub

Private Sub cmdDelete_Click()
On Error Resume Next
DoCmd.RunCommand acCmdDeleteRecord
End Sub

Private Sub Form_AfterUpdate()
Me.Dirty = False
Forms!pur_purcases_detail.subNoQuotes.Form.Requery
End Sub

Private Sub Form_Delete(Cancel As Integer)
Me.Dirty = False
Forms!pur_purcases_detail.subNoQuotes.Form.Requery
End Sub

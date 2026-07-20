VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasespettyitems"
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
    Case "NewRecord"
        AllowEditsAdvanced Me.Name, False, False
        Me.DataEntry = True
        Me.AllowAdditions = True
    Case "Draft", "Under Revision"
        Me.RecordSource = "Select * From pur_purcaseitems Where pci_id = " & arrArgs(1)
        If CheckAnyQuoteExists = False Then
            AllowEditsAdvanced Me.Name, False, False
            Me.cmdDelete.Visible = True
            Else
            Me.lblNote.Visible = True
            End If
    Case Else
        Me.RecordSource = "Select * From pur_purcaseitems Where pci_id = " & arrArgs(1)
    End Select
Me.Visible = True
End Sub

Private Sub Form_AfterUpdate()
Forms!pur_purcasespetty_detail.chkDataUpdateRequired = -1
End Sub

Private Sub pci_type_AfterUpdate()
Me.pci_subtype.Requery
End Sub

Private Sub cmdDelete_Click()
Dim intResponse  As Integer

intResponse = MsgBox("Are you sure you want to delete this quote?", 4, "Deletion Confirmation")
If intResponse <> 6 Then Exit Sub
CurrentDb.Execute "DELETE FROM pur_purcaseitems WHERE pci_id = " & Me!pci_id
Forms!pur_purcasespetty_detail.chkDataUpdateRequired = -1
DoCmd.Close
End Sub

Private Function CheckAnyQuoteExists() As Boolean
Dim dbsChkQuote As Database
Dim rstChkQuote As Recordset

Set dbsChkQuote = CurrentDb()
Set rstChkQuote = dbsChkQuote.OpenRecordset("Select qte_pcs_id From pur_quotes Where qte_pcs_id = " & Me!pci_pcs_id, dbOpenSnapshot)
If rstChkQuote.EOF = False Then CheckAnyQuoteExists = True
End Function







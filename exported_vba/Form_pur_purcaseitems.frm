VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcaseitems"
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
            AllowEditsAdvanced "pur_purcaseitems", False, False
            Me.cmdDelete.Visible = True
            Else
            Me.AllowEdits = True
            Me.box_pci_type.Visible = False
            Me.pci_type.BorderStyle = 1
            Me.pci_type.Locked = False
            Me.box_pci_subtype.Visible = False
            Me.pci_subtype.BorderStyle = 1
            Me.pci_subtype.Locked = False
            Me.box_pci_type2.Visible = False
            Me.pci_type2.BorderStyle = 1
            Me.pci_type2.Locked = False
            Me.lblNote.Visible = True
            End If
    Case Else
        Me.RecordSource = "Select * From pur_purcaseitems Where pci_id = " & arrArgs(1)
    End Select
If Nz(Forms!pur_purcases_detail!pcs_sudohed, "") <> "" Then Me.pci_subhead.Visible = False

Last_Step:
Me.Visible = True
End Sub

Private Sub Form_BeforeUpdate(Cancel As Integer)
If Me.pci_type <> 3 And Nz(Me.pci_type2, "") = "" Then
    Cancel = True
    MsgBox "Inventory / Asset field cannot be blank for permanent / consumable items.", vbCritical
    Me.pci_type2.SetFocus
    End If
End Sub

Private Sub Form_AfterUpdate()
Forms!pur_purcases_detail.chkDataUpdateRequired = -1
End Sub

Private Function CheckAnyQuoteExists() As Boolean
Dim dbsChkQuote As Database
Dim rstChkQuote As Recordset

Set dbsChkQuote = CurrentDb()
Set rstChkQuote = dbsChkQuote.OpenRecordset("Select qte_pcs_id From pur_quotes Where qte_pcs_id = " & Me!pci_pcs_id, dbOpenSnapshot)
If rstChkQuote.EOF = False Then CheckAnyQuoteExists = True
End Function

Private Sub cmdDelete_Click()
Dim intResponse  As Integer

intResponse = MsgBox("Are you sure you want to delete this quote?", 4, "Deletion Confirmation")
If intResponse <> 6 Then Exit Sub
CurrentDb.Execute "DELETE FROM pur_purcaseitems WHERE pci_id = " & Me!pci_id
Forms!pur_purcases_detail.chkDataUpdateRequired = -1
DoCmd.Close
End Sub

Private Sub pci_type_AfterUpdate()
Me.pci_subtype.Requery
Select Case Me.pci_type
    Case 7  'Permanent
        Me.pci_type2.Enabled = True
        Me.pci_type2 = Null
        Me.pci_type2.RowSource = "5;Inventory;6;Asset"
    Case 2  'Consumable
        Me.pci_type2.Enabled = True
        Me.pci_type2 = 5
        Me.pci_type2.RowSource = "5;Inventory"
    Case 3  'Service
        Me.pci_type2.Enabled = False
        Me.pci_type2 = Null
        Me.pci_type2.RowSource = ""
    Case Else
    End Select
End Sub

Private Sub pci_type2_BeforeUpdate(Cancel As Integer)
If Me.pci_type <> 3 And Nz(Me.pci_type2, "") = "" Then
    Cancel = True
    MsgBox "Inventory / Asset field cannot be blank for permanent / consumable items.", vbCritical
    Me.pci_type2.Undo
    End If
End Sub

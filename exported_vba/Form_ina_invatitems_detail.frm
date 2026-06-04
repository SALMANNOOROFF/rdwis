VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_invatitems_detail"
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
    Case "Inventory"
        Me.RecordSource = "Select * From ina_inventoryitemsp_u Where iac_id = " & arrArgs(2)
        Me.lblHeading.Caption = "Inventory Item"
    Case "Asset"
        Me.RecordSource = "Select * From ina_assetitemsp_u Where iac_id = " & arrArgs(2)
        Me.lblHeading.Caption = "Asset"
        Me.shared.Visible = True
        Me.shared_label.Visible = True
    Case Else
        MsgBox "Item Data incorrect.", vbCritical
        DoCmd.Close
    End Select
Select Case arrArgs(1)
    Case "Held"
        If getVar("varMode") = "approver-s" Then
            AllowEditsAdvanced Me.Name, True, True
            Me.subChildren.Form.cmdDetach.Visible = True
            End If
        If (Me.iac_isparent = "0" And Nz(Me.iac_parent_id, "") = "") Then   'If solo
            Me.cmdAttach.Visible = True
            If Me.iac_isassembly = "1" Then
                Me.cmdDelete.Visible = True
                Else
                Me.cmdChargeOff.Visible = True
                End If
            End If
        If Me.iac_isparent = "1" Then   'If parent
            Me.cmdChargeOff.Visible = True
            Me.cmdAttach.Visible = True
            Me.subChildren.Visible = True
            End If
        If Not IsNull(Me.iac_parent_id) Then   'If child
            Me.parent_ias_id.Visible = True
            End If
    Case Else
        If Me.iac_isparent = "1" Then   'If parent
            Me.subChildren.Visible = True
            End If
    End Select
Me.Visible = True
End Sub

Private Sub iac_status_BeforeUpdate(Cancel As Integer)
If (Me!iac_status = "Consumed" Or _
    Me!iac_status = "Issued to User" Or _
    Me!iac_status = "Written Off") And _
    Nz(Me!iac_dispdate, "") = "" Then
    Cancel = True
    MsgBox "Please Enter disposal date first.", vbCritical
    Me.iac_status.Undo
    End If
End Sub

Private Sub shared_Click()
If IsNull(Me.shared) Then
    Me.iac_shared = 1
    Else
    Me.iac_shared = 0
    End If
Me.iac_details.SetFocus
Me.shared.Requery
End Sub

Private Sub cmdChargeOff_Click()
Dim intResponse As Integer
Dim dblChargeOffQty As Double

'Exit if the item is a child
If Nz(Me.iac_parent_id, "") <> "" Then
    MsgBox "Child item cannot be disposed off. Please detach the item first.", vbCritical
    Exit Sub
    End If
    
DoCmd.OpenForm "ina_item_chargeoff", acNormal
End Sub

Private Sub cmdAttach_Click()
Dim strArgs As String
DoCmd.OpenForm "ina_item_attach", acNormal, , , , acWindowNormal, Me.iac_id & "~" & Me.iac_qty & "~" & Me.iac_qtyunit
End Sub

Private Sub cmdDelete_Click()
If Me.iac_isassembly = "0" Then
    MsgBox "Only an assembly can be deleted.", vbCritical
    Exit Sub
    End If
If Me.subChildren.Form.Recordset.RecordCount > 0 Then
    MsgBox "Assembly with attached items cannot be deleted. Remove all attach items before deleting an assembly.", vbCritical
    Exit Sub
    End If

DeleteAssembly Me.ias_id
MsgBox "Assembly deleted.", vbInformation
DoCmd.Close
End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcaseitems_tempchoose"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub receive_qty_BeforeUpdate(Cancel As Integer)
If Nz(Me!receive_qty, 0) > Me!balance_qty Then
    MsgBox "Maximum quantity for this item is " & Me!balance_qty & "."
    Cancel = True
    Me.balance_qty.Undo
    End If
End Sub

Private Sub receive_qty_AfterUpdate()
Dim rstReceive As Recordset
Dim intCount As Integer
Set rstReceive = Me.RecordsetClone

If IsNull(Me!receive_qty) Then Me!receive_qty = 0
Me.Dirty = False
intCount = 0
rstReceive.MoveFirst
Do While Not rstReceive.EOF
    If rstReceive!receive_qty > 0 Then intCount = intCount + 1
    rstReceive.MoveNext
    Loop
Me.txtSelected = intCount
If intCount > 0 Then
    Me.cmdReceiveItem.Enabled = True
    Else
    Me.cmdReceiveItem.Enabled = False
    End If

End Sub

Private Sub selected_BeforeUpdate(Cancel As Integer)
If IsNull(Me!receive_qty) Or Me!receive_qty = 0 Or Me!receive_qty > Me!balance_qty Then
    MsgBox "Please enter valid required qty before selection.", vbCritical
    Cancel = True
    Me.receive_qty.Undo
    End If
End Sub

Private Sub cmdReceiveItem_Click()
Dim dbsReceipt As Database
Dim rstSource As Recordset
Dim rstDestin As Recordset
Dim qdfReceipt As QueryDef
Dim lngPrtID As Long
Dim intNum As Integer

Me.Dirty = False
Set dbsReceipt = CurrentDb()
Set rstSource = dbsReceipt.OpenRecordset("Select * From pur_purcaseitems_temp Where receive_qty > 0 Order by pci_serial", dbOpenSnapshot)
'* Check for valid selection
If rstSource.EOF Then
    MsgBox "Please select atleast one item.", vbCritical
    Exit Sub
    End If

'***Add receipt
Set rstDestin = dbsReceipt.OpenRecordset("pur_purreceipts", dbOpenDynaset, dbSeeChanges)
With rstDestin
    .AddNew
    !prt_pcs_id = rstSource!pci_pcs_id
    !prt_unt_id = rstSource!unt_id
    If Nz(rstSource!prj_id, "") <> "" Then !prt_prj_id = rstSource!prj_id
    !prt_status = "Draft"
    .Update
    .Bookmark = .LastModified
    End With
lngPrtID = rstDestin!prt_id

'***Add items to receipt
intNum = 1
Set rstDestin = dbsReceipt.OpenRecordset("pur_purreceiptitems", dbOpenDynaset, dbAppendOnly)
Do While Not rstSource.EOF
    With rstDestin
        .AddNew
        !pti_prt_id = lngPrtID
        !pti_serial = intNum
        !pti_desc = rstSource!pci_desc
        !pti_qty = rstSource!receive_qty
        !pti_qtyunit = rstSource!pci_qtyunit
        !pti_pci_id = rstSource!pci_id
        !pti_pri_id = rstSource!pci_pri_id
        .Update
        End With
    intNum = intNum + 1
    rstSource.MoveNext
    Loop
DoCmd.OpenForm "pur_purreceipts_detail", acNormal, , , , acHidden, "Draft~" & lngPrtID
DoCmd.Close acForm, "pur_purcaseitems_tempchoose"
DoCmd.Close acForm, "pur_purcases_detail"
DoCmd.Close acForm, "pur_purcasespetty_detail"
DoCmd.Close acForm, "pur_purcasestada_detail"
DoCmd.Close acForm, "pur_purcases_u"
End Sub


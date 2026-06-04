VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_item_detach"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")

Me.child_invat_id = arrArgs(0)
Me.child_id = arrArgs(1)
Me.child_desc = arrArgs(2)
Me.child_held = arrArgs(3)
Me.child_held_units = arrArgs(4)
Me.parent_invat_id = arrArgs(5)
Me.parent_id = arrArgs(6)
End Sub

Private Sub cmdOK_Click()
Dim strOpenArgs As String
Dim lngParentId As Long

If Nz(Me.child_qty, "") = "" Then
    MsgBox "Please enter quantity to be attached.", vbCritical
    Exit Sub
    End If
If CDbl(Me.child_qty) > Me.child_held Then
    MsgBox "Quantity to be detached cannot be greater than attached quantity.", vbCritical
    Exit Sub
    End If

lngParentId = DetachItem(Me.child_invat_id, Me.child_id, Me.child_held, Me.child_qty, Me.parent_id, Me.parent_invat_id)
MsgBox "Item Detached.", vbInformation

DoCmd.Echo False
strOpenArgs = IIf(Forms!ina_invatitems_detail!ias_type2 = 5, "Inventory~", "Asset~") & Forms!ina_invatitems_detail!!iac_status & "~" & lngParentId
DoCmd.Close acForm, "ina_invatitems_detail"
DoCmd.OpenForm "ina_invatitems_detail", acNormal, "", "", , acHidden, strOpenArgs
DoCmd.Maximize
DoCmd.Close acForm, "ina_item_detach"
DoCmd.Echo True

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close acForm, "ina_item_attach"
End Sub


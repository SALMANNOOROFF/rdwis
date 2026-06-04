VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_item_attach"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.parent_id = arrArgs(0)
Me.parent_held = arrArgs(1)
arrArgs = Split(Me.OpenArgs, "~")

End Sub

Private Sub cmdOK_Click()
Dim strOpenArgs As String
Dim lngParentId As Long

If IsNull(Me.lstChildren.Value) Then
    MsgBox "Please select a child item.", vbCritical
    Exit Sub
    End If
If Nz(Me.child_qty, "") = "" Then
    MsgBox "Please enter quantity to be attached.", vbCritical
    Exit Sub
    End If
If CDbl(Me.child_qty) > Me.lstChildren.Column(2) Then
    MsgBox "Quantity to be attached cannot be greater than held quantity.", vbCritical
    Exit Sub
    End If

lngParentId = AttachItem(Me.lstChildren.Column(0), Me.lstChildren.Value, Me.lstChildren.Column(2), Me.child_qty, Me.parent_id, Me.parent_held)
MsgBox "Item Attached.", vbInformation

DoCmd.Echo False
strOpenArgs = IIf(Forms!ina_invatitems_detail!ias_type2 = 5, "Inventory~", "Asset~") & Forms!ina_invatitems_detail!!iac_status & "~" & lngParentId
DoCmd.Close acForm, "ina_invatitems_detail"
DoCmd.OpenForm "ina_invatitems_detail", acNormal, "", "", , acHidden, strOpenArgs
DoCmd.Maximize
DoCmd.Close acForm, "ina_item_attach"
DoCmd.Echo True

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close acForm, "ina_item_attach"
End Sub


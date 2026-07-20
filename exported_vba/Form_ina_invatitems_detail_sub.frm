VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_invatitems_detail_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub cmdDetach_Click()
DoCmd.OpenForm "ina_item_detach", acNormal, , , , acWindowNormal, Me.ias_id & "~" & Me.iac_id & "~" & Me.ias_desc & "~" & Me.iac_qty & "~" & Me.iac_qtyunit & "~" & Me.Parent.ias_id & "~" & Me.Parent.iac_id
End Sub

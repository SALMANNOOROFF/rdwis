VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_item_chargeoff"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.iac_id = Forms!ina_invatitems_detail!iac_id
Me.qty_held = Forms!ina_invatitems_detail!iac_qty
Me.qtyunit = "(" & Forms!ina_invatitems_detail!iac_qtyunit & ")"
If Me.qty_held = 1 And Me.qtyunit = "(num)" Then Me.qty_chargeoff = 1
End Sub

Private Sub cmdOK_Click()
Dim strMessage As String

If Nz(Me.qty_chargeoff, "") = "" Then
    MsgBox "Please enter quantity to be charged off.", vbCritical
    Exit Sub
    End If

If CDbl(Me.qty_chargeoff) > Me.qty_held Then
    MsgBox "Quantity to be charged off is greater than held quantity.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.status, "") = "" Then
    MsgBox "Please enter status.", vbCritical
    Exit Sub
    End If
    
If Nz(Me.dispdt, "") = "" Then
    MsgBox "Please enter date.", vbCritical
    Exit Sub
    End If
    
strMessage = ChargeOffItem(Me.iac_id, Me.qty_held, CDbl(Me.qty_chargeoff), Me.dispdt, Me.status, Nz(Me.remarks, ""))

Closing:
MsgBox strMessage, vbInformation
DoCmd.Close acForm, "ina_item_chargeoff"
DoCmd.Close acForm, "ina_invatitems_detail"

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close acForm, "ina_chargeoff"
End Sub


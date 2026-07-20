VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_quoteitems"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_AfterUpdate()
Parent.UpdateQuotePrices
Forms!pur_purcases_detail.chkDataUpdateRequired = -1
End Sub

Private Sub qti_price_AfterUpdate()
Me.Dirty = False
End Sub


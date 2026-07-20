VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_transfersamount_detail_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub tfc_amount1_AfterUpdate()
Me!tfc_amount2 = Me!tfc_amount1
Me.Recalc
Parent.trf_amount = Me.sum_amount1
End Sub

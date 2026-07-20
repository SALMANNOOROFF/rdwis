VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_detail_sub2"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdQte_Click()
DoCmd.OpenForm "pur_quotes_detail", acNormal, , , , acHidden, Me.Parent!pcs_status & "~" & Me!qte_id
End Sub



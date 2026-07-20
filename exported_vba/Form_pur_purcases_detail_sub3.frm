VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_detail_sub3"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdNoQte_Click()
DoCmd.OpenForm "pur_noquotes_detail", acNormal, , , , acHidden, Me.Parent!pcs_status & "~" & Me.Parent!pcs_id
End Sub

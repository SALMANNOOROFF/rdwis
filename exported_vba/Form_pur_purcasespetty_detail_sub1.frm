VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasespetty_detail_sub1"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdPci_Click()
DoCmd.OpenForm "pur_purcasespettyitems", acNormal, "", "", , acHidden, Parent!pcs_status & "~" & Me.pci_id
End Sub





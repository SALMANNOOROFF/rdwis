VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_detail_sub1"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdPci_Click()
Dim strPcsStatus As String
strPcsStatus = IIf(Parent!pcs_intunt_id = getVar("varUnitId") And getVar("varMode") Like "*-s", Parent!pcs_status, "OtherInitUnit")
DoCmd.OpenForm "pur_purcaseitems", acNormal, "", "", , acHidden, strPcsStatus & "~" & Me.pci_id
End Sub

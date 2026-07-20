VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_contractsverif"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cvf_verif_BeforeUpdate(Cancel As Integer)
If RecordBusDataAudit(Me!ctr_id) = False Then
    Cancel = 0
    Me.Undo
    MsgBox "The change cannot be saved.", vbCritical
    Else
    Me!cvf_dtg = GetNow()
    End If
End Sub


Private Sub cmdVerified_Click()
Dim strEmpId As String
Me.cvf_verif.Locked = True
Me.cvf_verif.BorderStyle = 0
Me.box_cvf_verif.Visible = True
Me.RecordSource = "fin_contractsverif_yes"
Me.cmdVerified.Visible = False
Me.Refresh
End Sub

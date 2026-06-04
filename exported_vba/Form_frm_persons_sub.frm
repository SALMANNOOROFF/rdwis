VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_persons_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub Form_Current()
On Error Resume Next
Me.txtBGP = Nz(Me.per_id, 0)
Forms!frm_firms_detail.subInfoPersons.Form.Requery
End Sub


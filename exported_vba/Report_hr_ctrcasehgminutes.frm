VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_ctrcasehgminutes"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
If Forms!hr_ctrcaseminutes.chkOmit = 0 Then
    Me.para_wo.Visible = True
    End If
If IsNull(Forms!hr_ctrcaseminutes!ccm_textc) Then Me.para_a1.Visible = False
If IsNull(Forms!hr_ctrcaseminutes!ccm_textd) Then Me.para_a2.Visible = False
End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_pur_purreceipts_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
If Forms!pur_purreceipts_detail.txtMat = 0 Then
    'Me.txtStat2.Visible = False
    End If
End Sub

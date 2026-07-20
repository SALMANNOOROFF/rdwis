VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_headstatus_mtss"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
Dim objCtl As Control

Me.lblTax.Caption = Forms!fin_headstatus_mtss.lblTax.Caption

'Exactness
If Me.OpenArgs = "Exact" Then
    For Each objCtl In Me.Controls
        If TypeOf objCtl Is TextBox Then
            objCtl.Format = "Standard"
            objCtl.DecimalPlaces = 0
            End If
        Next objCtl
    End If



End Sub

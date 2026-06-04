VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_salorders_u_doc2"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft"
        Me.RecordSource = "Select * From fin_salorders_u_doc Where sor_status like 'Draft'"
    Case "Closed"
        Me.RecordSource = "Select * From fin_salorders_u_doc Where sor_status like 'Fulfilled' And sor_month = #" & arrArgs(1) & "#"
    End Select

Me.Visible = True


End Sub

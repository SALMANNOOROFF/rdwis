VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_salreqs_u_doc"
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
        Me.RecordSource = "Select * From hr_salreqs_u_doc Where srq_status like 'Draft'"
    Case "In Process"
        Me.RecordSource = "Select * From hr_salreqs_u_doc Where srq_status like 'In Process'"
    Case "Closed"
        Me.RecordSource = "Select * From hr_salreqs_u_doc Where srq_status like 'Fulfilled' or srq_status like 'Cancelled'"
    End Select

Me.Visible = True
End Sub

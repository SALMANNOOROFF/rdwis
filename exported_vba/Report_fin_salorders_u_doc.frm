VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_salorders_u_doc"
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
    Case "Open"
        Me.RecordSource = "Select * From fin_salorders_u_doc Where sor_status In ('In Process','Under Revision') And sor_releasedtg >= #" & arrArgs(1) & "#"
    Case "Closed"
        Me.RecordSource = "Select * From fin_salorders_u_doc Where sor_status like 'Fulfilled' And sor_releasedtg >= #" & arrArgs(1) & "#"
    Case "Specific"
        Me.RecordSource = arrArgs(1)
    End Select

Me.Visible = True


End Sub

Private Sub cmdShowCheckedOnly_Click()
Dim strSql As String
strSql = Me.RecordSource
If InStr(strSql, "where") > 0 Then
    strSql = Me.RecordSource & " And sor_checked = True"
    Else
    strSql = Me.RecordSource & " Where sor_checked = True"
    End If
'Debug.Print strSql
DoCmd.Close
DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Specific~" & strSql
End Sub

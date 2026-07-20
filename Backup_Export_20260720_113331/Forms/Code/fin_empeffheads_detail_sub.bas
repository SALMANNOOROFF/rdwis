VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_empeffheads_detail_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


'****By default form allowedits = false and all fields locked = true
Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

'arrArgs = Split(Me.OpenArgs, "~")
'Select Case arrArgs(0)
'    Case "Draft"
'    Case "Closed"
'    Case Else
'        MsgBox "Access denied."
'        Exit Sub
'    End Select
Me.Visible = True
End Sub



Private Sub cmdReportSalOrders_Click()
On Error GoTo cmdReportSalOrders_Click_Err
Dim strDate As String

'Me.Dirty = False
'Select Case Me.lblTitle.Caption
'    Case "Salary Orders - Draft"
'    DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Draft"
'    Case "Salary Orders - Closed"
'    strDate = InputBox("Please enter salary order date.", "Date Required", CStr(Date))
'    'strDate = CStr(LastDateThisMonth(CDate(strDate)))
'    DoCmd.OpenReport "fin_salorders_u_doc", acViewReport, "", "", acHidden, "Closed~" & strDate
'End Select

cmdReportSalOrders_Click_Exit:
    Exit Sub

cmdReportSalOrders_Click_Err:
    MsgBox Error$
    Resume cmdReportSalOrders_Click_Exit

End Sub

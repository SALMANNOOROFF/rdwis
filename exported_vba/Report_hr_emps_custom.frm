VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_emps_custom"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit
Dim arrVar(1 To 15) As String

Private Sub Report_Open(Cancel As Integer)
Dim dcnFormatter As Variant
Dim key As Variant
Dim intHeight As Integer
Dim n As Integer

Me.lblHeader.Caption = IIf(Me.OpenArgs = "All", "R&&D Wing", Me.OpenArgs & " Division") & " Employees"

Set dcnFormatter = Form_hr_emps_custom.dcnFields
intHeight = 1
n = 2
For Each key In dcnFormatter
    Me.Controls("Label" & Format(n, "00")).Caption = key
    Me.Controls("Label" & Format(n, "00")).TextAlign = IIf(dcnFormatter(key)(1) = "c", fmTextAlignCenter, fmTextAlignLeft)
    If dcnFormatter(key)(3) > intHeight Then intHeight = dcnFormatter(key)(3)
    Me.Controls("Label" & Format(n, "00")).Visible = True
    Me.Controls("Text" & Format(n, "00")).ControlSource = key
    Select Case dcnFormatter(key)(1)
        Case "c": Me.Controls("Text" & Format(n, "00")).TextAlign = fmTextAlignCenter
        Case "r": Me.Controls("Text" & Format(n, "00")).TextAlign = fmTextAlignRight
        End Select
    Me.Controls("Text" & Format(n, "00")).TextAlign = IIf(dcnFormatter(key)(1) = "c", fmTextAlignCenter, fmTextAlignLeft)
    Me.Controls("Text" & Format(n, "00")).Width = dcnFormatter(key)(2)
    Me.Controls("Text" & Format(n, "00")).Visible = True
    Select Case dcnFormatter(key)(4)
        Case "d": Me.Controls("Text" & Format(n, "00")).Format = "dd mmm yy"
        Case "c": Me.Controls("Text" & Format(n, "00")).Format = "#,###"
        End Select
    n = n + 1
    Next key
Me.Controls("Label" & Format(n, "00")).Height = Me.Controls("Label" & Format(n, "00")).Height * intHeight
 
End Sub



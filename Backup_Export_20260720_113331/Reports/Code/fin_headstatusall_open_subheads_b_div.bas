VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_headstatusall_open_subheads_b_div"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
If Me.OpenArgs = "Rounded" Then Exit Sub
Dim objCtl As Control
For Each objCtl In Me.Controls
    If TypeOf objCtl Is TextBox Then
        objCtl.Format = "Standard"
        objCtl.DecimalPlaces = 0
        End If
    Next objCtl
End Sub

Private Sub cmdShowHide_Click()
Me.Detail.Visible = Not Me.Detail.Visible
If Me.lblCol1.Caption Like "*Project" Then
    Me.lblCol1.Caption = ""
    Else
    Me.lblCol1.Caption = vbCrLf & "Project"
    End If
End Sub

Private Sub cmdProjectWise_Click()
DoCmd.OpenReport "fin_headstatusall_open_subheads_a_div", acViewReport
DoCmd.Close acReport, "fin_headstatusall_open_subheads_b_div"
End Sub

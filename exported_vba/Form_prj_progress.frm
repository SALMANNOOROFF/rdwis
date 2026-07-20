VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_progress"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs() = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From prj_prghistory Where pgh_id = " & arrArgs(1)
Me.Visible = True

End Sub

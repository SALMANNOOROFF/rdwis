VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_pur_purcasepettyminutes"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Activate()
Dim n As Integer

If Me.prj_alloc_c = "(Blank)" Then Me.prj_alloc_c.Visible = False
For n = 1 To 5
    If Me.Controls("prj_name_" & Chr(96 + n)) <> "(Blank)" Then
        Me.Controls("prj_name_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_alloc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_canbespent_" & Chr(96 + n)).Visible = True
        Me.Controls("required_" & Chr(96 + n)).Visible = True
        End If
    Next n
End Sub

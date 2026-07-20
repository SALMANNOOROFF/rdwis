VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_pur_purcaseminutes"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
If Forms!pur_purcaseminutes.chkOmit = 0 Then
    Me.para_wo.Visible = True
    End If
If IsNull(Forms!pur_purcaseminutes!pcm_textd) Then Me.para_a1.Visible = False
If IsNull(Forms!pur_purcaseminutes!pcm_texte) Then Me.para_a2.Visible = False
End Sub

Private Sub Report_Load()
If Me.subMinutes.Report.HasData = False Then
    Me.subMinutes.Visible = False
    End If
End Sub


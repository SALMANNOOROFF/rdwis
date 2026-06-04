VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_fin_headstatusall_open_chrf"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdPageBreaks_Click()
DoCmd.OpenReport "fin_headstatusall_open_chrf_p", acViewReport
DoCmd.Close acReport, "fin_headstatusall_open_chrf"
End Sub

Private Sub cmdShowHide_Click()
Me.Detail.Visible = Not Me.Detail.Visible
Me.GroupFooter0.Visible = Not Me.GroupFooter0.Visible
End Sub

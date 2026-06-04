VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects_one"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From prj_projects Where prj_id = " & arrArgs(1)
If getVar("varRoleLevel") = LastStop("mpr_route") Then Me.cmdPreview.Visible = False
Me.cmdHistory.SetFocus
Me.Visible = True
End Sub

Private Sub cmdHistory_Click()
On Error GoTo cmdHistory_Click_Err

DoCmd.OpenForm "prj_events_detail", acNormal, "", "", , acHidden, "~" & Me.prj_id

cmdHistory_Click_Exit:
    Exit Sub

cmdHistory_Click_Err:
    MsgBox Error$
    Resume cmdHistory_Click_Exit

End Sub

Private Sub cmdPreview_Click()
On Error GoTo cmdPreview_Click_Err

Me.Refresh

DoCmd.OpenReport "prj_projects_detail", acViewPreview, "SELECT * FROM prj_projects_details WHERE prj_id =" & Me!prj_id, "", acNormal

cmdPreview_Click_Exit:
    Exit Sub

cmdPreview_Click_Err:
    MsgBox Error$
    Resume cmdPreview_Click_Exit

End Sub







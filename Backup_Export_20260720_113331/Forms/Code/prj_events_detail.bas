VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_events_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim strSql As String

arrArgs = Split(Me.OpenArgs, "~")
strSql = "Select * From prj_events_detail Where evt_xprj_id = " & arrArgs(1)
If getVar("varRoleLevel") > 30 Then strSql = strSql & " And evt_name = 'Finalize'"
Me.RecordSource = strSql
Me.Visible = True
End Sub

Private Sub cmdProgress_Click()
On Error GoTo cmdProgress_Click_Err

DoCmd.OpenForm "prj_progress", acNormal, "", "", , acHidden, "~" & Me!evt_xpgh_id


cmdProgress_Click_Exit:
    Exit Sub

cmdProgress_Click_Err:
    MsgBox Error$
    Resume cmdProgress_Click_Exit

End Sub

 
Private Sub cmh_comment_DblClick(Cancel As Integer)
OpenNotepad
End Sub



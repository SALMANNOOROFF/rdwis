VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_choose_file_action"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.txtPath = Me.OpenArgs
End Sub

Private Sub cmdOK_Click()
Dim intChoice As Integer
Dim strPath As String
On Error GoTo cmdOk_Err

intChoice = Me.frmChoose
strPath = Me.txtPath
DoCmd.Close
Select Case intChoice
    Case 1
        DoCmd.OpenForm "wait"
        DoCmd.Hourglass True
        FollowHyperlink strPath
        DoCmd.Close acForm, "wait"
        DoCmd.Hourglass False
    Case 2
        'Create data revision request
    End Select
Exit Sub

cmdOk_Err:
MsgBox Error$, vbCritical
If CurrentProject.AllForms("wait").IsLoaded Then
    DoCmd.Close acForm, "wait"
    DoCmd.Hourglass False
    End If
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub


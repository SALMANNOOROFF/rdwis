VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_prghistory_fin_last"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Const sModName = "Form_frm_DB_AboutEULA"

Private Sub hst_rem2_DblClick(Cancel As Integer)
OpenNotepad
End Sub

Private Sub Form_MouseWheel(ByVal Page As Boolean, ByVal count As Long)
On Error GoTo Error_Handler
Dim i As Long

If ActiveControl.ControlType = acTextBox Then
    For i = 1 To Abs(count)
        SendMessage apiGetFocus, WM_VSCROLL, IIf(count < 0, SB_LINEUP, SB_LINEDOWN), 0&
        'The following line would flip the scrolling direction of the mousewheel
        'SendMessage CtlHwnd, WM_VSCROLL, IIf(Count < 0, SB_LINEDOWN, SB_LINEUP), 0&
    Next
End If

Error_Handler_Exit:
    On Error Resume Next
    Exit Sub

Error_Handler:
    'MsgBox "The following error has occured" & vbCrLf & vbCrLf & _
           "Error Number: " & Err.Number & vbCrLf & _
           "Error Source: " & sModName & "\Form_MouseWheel" & vbCrLf & _
           "Error Description: " & Err.Description & _
           Switch(Erl = 0, "", Erl <> 0, vbCrLf & "Line No: " & Erl) _
           , vbOKOnly + vbCritical, "An Error has Occured!"
    Resume Error_Handler_Exit

End Sub

Private Sub pgh_path_Click()
Dim strTitle As String
strTitle = "Project Progress  -  " & Me.Parent!prj_code & " Project"
FileResponse "pgh", Me!pgh_xprj_id, "Progress Detail", Me!pgh_xprj_id, Nz(Me!pgh_path, ""), Me.Parent.Name, strTitle
End Sub


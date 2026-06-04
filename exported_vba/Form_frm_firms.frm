VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_firms"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Type"
        Me.RecordSource = "Select * From frm_firms" & IIf(arrArgs(1) = "All", " Order By frm_name", " Where frm_type = '" & arrArgs(1) & "'")
    Case "Search"
        Me.RecordSource = "Select * From frm_firms Where frm_id In (" & arrArgs(1) & ") Order By frm_id"
    End Select
Me.Visible = True
End Sub

Private Sub cmdEmp_Click()
DoCmd.OpenForm "frm_firms_detail", acNormal, "", "", , acHidden, "View~" & Me!frm_id

End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects"
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
    Case "Draft"
    Case "Open"
        Me.txtTitle = "Open Projects"
        Me.RecordSource = "prj_projects_u_open"
    Case "Open-Approved"
        Me.txtTitle = "Open Projects"
        Me.RecordSource = "Select * From prj_projects_u_open Where prj_aprvdt Is Not Null"
    Case "Closed"
        Me.txtTitle = "Closed Projects"
        Me.RecordSource = "prj_projects_u_closed"
    Case "Closed-Approved"
        Me.txtTitle = "Closed Projects"
        Me.RecordSource = "Select * From prj_projects_u_closed Where prj_aprvdt Is Not Null"
    End Select
Me.OrderBy = "[prj_code]"
Me.OrderByOn = True
Me.Visible = True
End Sub

Private Sub Form_Activate()
Me.Refresh
End Sub

Private Sub cmdMsn_Click()
If getVar("varRoleLevel") >= JunctionStop("mpr_route") Then
    DoCmd.OpenForm "prj_projects_one", acNormal, "", "", , acHidden, "One~" & Me.prj_id
    Else
    DoCmd.OpenForm "prj_projects_detail", acNormal, "", "", , acHidden, Me.prj_status & "~" & Me.prj_id
    End If
End Sub


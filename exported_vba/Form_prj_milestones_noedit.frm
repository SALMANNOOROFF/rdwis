VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_milestones_noedit"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub msn_cc_path_Click()
Dim strTitle As String
strTitle = "Milestone " & Me!msn_id & "  -  " & Me.Parent!prj_code & " Project"
FileResponse "msn", Me!msn_idd, "Milestone Completion Certificate", Me!msn_idd, Nz(Me!msn_cc_path, ""), Me.Parent.Name, strTitle
End Sub

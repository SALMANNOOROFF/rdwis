VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_msncosts_plus_sb"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub pat_path_Click()
If Nz(Me!jat_path, "") <> "" Then
    FollowHyperlink FileBankDest() & Me!jat_path
    Else
    MsgBox "Work order not attached.", vbCritical
    End If
End Sub

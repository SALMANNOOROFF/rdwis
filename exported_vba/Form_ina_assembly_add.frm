VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_assembly_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Select Case True
Case Forms!ina_invatitems_u.RecordSource Like "*inventory*": Me.item_type = 5
Case Forms!ina_invatitems_u.RecordSource Like "*asset*": Me.item_type = 6
End Select
End Sub

Private Sub cmdAdd_Click()
If Nz(Me.item_desc, "") = "" Then
    MsgBox "Please enter assembly description", vbCritical
    Exit Sub
    End If

If IsNull(Me.project_id) Then
    MsgBox "Please enter project", vbCritical
    Exit Sub
    End If
    
CreateAssembly Me.item_desc, Nz(Me.item_detail, ""), Me.item_type, Me.project_id, getVar("varUnitId")
MsgBox "Assembly added.", vbInformation
Forms!ina_invatitems_u.Refresh
DoCmd.Close acForm, "ina_assembly_add"

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close acForm, "ina_assembly_add"
End Sub


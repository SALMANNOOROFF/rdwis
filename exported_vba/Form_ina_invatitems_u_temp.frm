VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_invatitems_u_temp"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
If getVar("varMode") = "approver-s" Then AllowEditsAdvanced Me.Name, True, True
Me.Visible = True
End Sub

Private Sub ias_id_DblClick(Cancel As Integer)
DoCmd.OpenForm "ina_invatitems_detail", acNormal, , , , acHidden, IIf(Me!ias_type2 = 6, "Asset~", "Inventory~") & Me!iac_status & "~" & Me!iac_id
End Sub

Private Sub ias_pcs_id_DblClick(Cancel As Integer)
Dim dbsPcs As Database
Dim rstPcs As Recordset
Dim strFormName As String

Set dbsPcs = CurrentDb()
Set rstPcs = dbsPcs.OpenRecordset("Select pcs_type from pur_purcases Where pcs_id = " & Me!ias_pcs_id, dbOpenSnapshot)
Select Case rstPcs!pcs_type
    Case "Ps"
    strFormName = "pur_purcases_detail"
    Case "Rb"
    strFormName = "pur_purcasestada_detail"
    Case "Pt"
    strFormName = "pur_purcasespetty_detail"
    End Select

DoCmd.OpenForm strFormName, acNormal, , , , acHidden, "Fulfilled" & "~" & Me!ias_pcs_id

End Sub

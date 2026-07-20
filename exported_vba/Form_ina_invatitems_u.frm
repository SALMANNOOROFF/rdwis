VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_ina_invatitems_u"
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
    Case "Assets"
        Select Case arrArgs(1)
            Case "OnCharge"
                Me.lblHeader.Caption = "Assets - On Charge"
                Me.RecordSource = "Select * From ina_assetitemsp_u_oncharge Where IsNull([iac_parent_id])"
                Me.cmdAddAssembly.Visible = True
            Case "ChargedOff"
                Me.lblHeader.Caption = "Assets - Charged Off"
                Me.RecordSource = "Select * From ina_assetitemsp_u_chargedoff Where IsNull([iac_parent_id])"
            End Select
    Case "Inventory"
        Select Case arrArgs(1)
            Case "OnCharge"
                Me.lblHeader.Caption = "Inventory Items - On Charge"
                Me.RecordSource = "Select * From ina_inventoryitemsp_u_oncharge Where IsNull([iac_parent_id])"
                Me.cmdAddAssembly.Visible = True
            Case "ChargedOff"
                Me.lblHeader.Caption = "Inventory Items - Charged Off"
                Me.RecordSource = "Select * From ina_inventoryitemsp_u_chargedoff Where IsNull([iac_parent_id])"
            End Select
    End Select
Me.Visible = True

End Sub

Private Sub Form_Activate()
Dim str As String
On Error GoTo End_Sub
str = Me.ActiveControl.Name
Me.Requery
End_Sub:
End Sub

Private Sub cmdInv_Click()
DoCmd.OpenForm "ina_invatitems_detail", acNormal, , , , acHidden, IIf(Me!ias_type2 = 5, "Inventory~", "Asset~") & Me!iac_status & "~" & Me!iac_id
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

Private Sub cmdShowHideChildren_Click()
Select Case Me.lblShowHideChildren.Caption
    Case "Show Attached Items"
        Me.lblShowHideChildren.Caption = "Hide Attached Items"
        Me.RecordSource = Mid(Me.RecordSource, 15, Len(Me.RecordSource) - 44)
    Case "Hide Attached Items"
        Me.lblShowHideChildren.Caption = "Show Attached Items"
        Me.RecordSource = "Select * From " & Me.RecordSource & " Where IsNull([iac_parent_id])"
    End Select
End Sub


Private Sub cmdAddAssembly_Click()
DoCmd.OpenForm "ina_assembly_add"
End Sub

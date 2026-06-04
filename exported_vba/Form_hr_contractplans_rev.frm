VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contractplans_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsRev As Database
Dim qdfRev As QueryDef

Set dbsRev = CurrentDb()
dbsRev.Execute "Delete From hr_contractplan_temp"
Set qdfRev = dbsRev.QueryDefs("hr_contractplan_temp_adder")
qdfRev.Parameters(0) = Forms!hr_contracts_detail.ctr_id
qdfRev.Execute
qdfRev.Close
Me.Requery

Me.unit_id = Forms!hr_contracts_detail!ctr_unt_id
Me.object_id = Forms!hr_contracts_detail!ctr_id

Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intResponse As Integer
Dim lngRevId As Long

Me.Dirty = False
Me.Recalc
If AnyChangeOnForm = False Then
    MsgBox "No change in data requested.", vbCritical
    Exit Sub
    End If

intResponse = MsgBox("A Data Revision Request for milstone costs will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Contract Plan", Me.object_id, Me.unit_id, 2)
DoCmd.Close acForm, "hr_contractplans_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_contracts_detail"
DoCmd.Close acForm, "hr_contracts"
DoCmd.Close acForm, "hr_emps_detail"
DoCmd.Close acForm, "hr_emps_u"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub FormHeader_DblClick(Cancel As Integer)
'MsgBox Me.mct_cost.Tag
End Sub

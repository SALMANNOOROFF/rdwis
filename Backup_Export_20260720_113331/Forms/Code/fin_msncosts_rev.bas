VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_msncosts_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsGen As Database
Dim qdfGen As QueryDef
Set dbsGen = CurrentDb()

Set dbsGen = CurrentDb()
dbsGen.Execute "Delete From fin_msncosts_plus_temp"
Set qdfGen = dbsGen.QueryDefs("fin_msncosts_temp_adder")
qdfGen.Parameters(0) = Forms!vars!Parameter1
qdfGen.Execute
qdfGen.Close
Me.Requery

Me.unit_id = UnitFromHead(Forms!fin_msncosts_plus!mct_hed_id)
Me.object_id = Forms!fin_msncosts_plus!mct_hed_id
Me.object_code = Forms!fin_msncosts_plus!mct_hed_id.Column(1)

Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intresponse As Integer
Dim lngRevId As Long

Me.Dirty = False
Me.Recalc
If AnyChangeOnForm = False Then
    MsgBox "No change in data requested.", vbCritical
    Exit Sub
    End If

intresponse = MsgBox("A Data Revision Request for milstone costs will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

'If Me.sum_mct_cost <> Me.alloc Then
'    MsgBox "The sum of milestne costs is not equal to project allocation.", vbCritical
'    Exit Sub
'    End If

lngRevId = CreateDataRevision("Milestone Cost", Me.object_id, Me.unit_id, 2, Me.object_code)
DoCmd.Close acForm, "fin_msncosts_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_msncosts_plus"
DoCmd.Close acForm, "fin_sharesinstall_add"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub FormHeader_DblClick(Cancel As Integer)
MsgBox Me.mct_cost.Tag
End Sub

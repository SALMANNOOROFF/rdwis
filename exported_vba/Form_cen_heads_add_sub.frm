VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_heads_add_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdDelete_Click()
CurrentDb.Execute "Delete From fin_subheads_temp Where sbh_hed_id = " & Me!sbh_hed_id & " And sbh_name = '" & Me!sbh_name & "'"
Me.Requery
ChangeAllowAdditions
End Sub

Private Sub Form_AfterInsert()
ChangeAllowAdditions
End Sub

Sub ChangeAllowAdditions()
If Me.Recordset.RecordCount < 5 Then
    Me.AllowAdditions = True
    Else
    Me.AllowAdditions = False
    End If
End Sub

Private Sub sbh_name_AfterUpdate()
Me.sbh_name = Trim(Me.sbh_name)
If LCase(Me.sbh_name) = "equipment" Then Me.sbh_name = "Equipment"
If LCase(Me.sbh_name) = "construction" Then Me.sbh_name = "Construction"
If LCase(Me.sbh_name) = "training" Then Me.sbh_name = "Training"
If LCase(Me.sbh_name) = "software" Then Me.sbh_name = "Software"
If LCase(Me.sbh_name) = "hr" Then Me.sbh_name = "HR"
If LCase(Me.sbh_name) = "misc" Then Me.sbh_name = "Misc"
End Sub

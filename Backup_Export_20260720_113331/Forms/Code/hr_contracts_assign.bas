VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_assign"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim dbsExt As Database
Dim rstExt As Recordset

arrArgs = Split(Me.OpenArgs, "~")
Set dbsExt = CurrentDb()
Set rstExt = dbsExt.OpenRecordset("Select * From hr_contracts where ctr_id = " & arrArgs(1), dbOpenSnapshot)
Me.ctr_id = rstExt!ctr_id
Me.ctr_num = rstExt!ctr_num
Me.ctr_unt_id = rstExt!ctr_unt_id
Me.ctr_date = rstExt!ctr_date
Me.ctr_startdt = rstExt!ctr_startdt
Me.ctr_enddt = rstExt!ctr_enddt
Me.ctr_termindt = rstExt!ctr_termindt
Me.ctr_jobtitle = rstExt!ctr_jobtitle
Me.ctr_grade = rstExt!ctr_grade
Me.ctr_type = rstExt!ctr_type
Me.ctr_salary = rstExt!ctr_salary
Me.ctr_probnsal = IIf(IsNull(rstExt!ctr_prob), "", rstExt!ctr_prob & " (" & rstExt!ctr_probsal & ")")
Me.ctr_remarks = rstExt!ctr_remarks
rstExt.Close

dbsExt.Execute "Delete From hr_contractplan_temp"
GetContractPlanUnassigned Me.ctr_id
Me.subContractPlan.Requery

Me.Visible = True

End Sub

'Private Sub new_termindt_BeforeUpdate(Cancel As Integer)
'If IsNull(Me.ctr_termindt) And Me.new_termindt <= Me.ctr_enddt Then
'    MsgBox "Contract termination date should be greater than contract end date.", vbCritical
'    Cancel = True
'    Me.ctr_termindt.Undo
'    Else
'    If Me.new_termindt <= Me.ctr_termindt Then
'        MsgBox "Contract termination date should be greater than current contract termination date.", vbCritical
'        Cancel = True
'        Me.ctr_termindt.Undo
'        End If
'    End If
'End Sub
'
'Private Sub new_termindt_AfterUpdate()
'Dim dtStart As Date
'If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
'    dtStart = FirstDateThisMonth(DateAdd("m", 1, IIf(IsNull(Me.ctr_termindt), Me.ctr_enddt, ctr_termindt)))
'    GenerateContractPlan dtStart, Me.new_termindt
'    Me.subContractPlan.Requery
'    If Me.subContractPlan.Form.Recordset.RecordCount > 0 Then
'        Me.projects_list.Visible = True
'        Me.subContractPlan.Visible = True
'        Else
'        Me.projects_list.Visible = False
'        Me.subContractPlan.Visible = False
'        End If
'    End If
'End Sub

Private Sub projects_list_AfterUpdate()
Dim rstList As Recordset
Set rstList = Me.subContractPlan.Form.RecordsetClone
rstList.MoveFirst
If rstList.EOF Then Exit Sub
rstList.MoveFirst
Do While Not rstList.EOF
    rstList.Edit
    rstList!cpn_hed_id = Me.projects_list
    rstList.Update
    rstList.MoveNext
    Loop
Me.subContractPlan.Requery
End Sub

Private Sub cmdSavenExit_Click()
Dim wksCtr As Workspace
Dim dbsCtr As Database
Dim rstCtr As Recordset
Dim rstSource As Recordset
Dim strCommit As String
Dim booAllGood As Boolean
On Error GoTo Error_Part
Me.Dirty = False

'Execute --------------------------------------------------------
Set wksCtr = DBEngine.Workspaces(0)
wksCtr.BeginTrans
strCommit = "Begun"

'Add projects in contract plan
Set dbsCtr = CurrentDb()
Set rstSource = dbsCtr.OpenRecordset("hr_contractplan_temp", dbOpenSnapshot)
Do While Not rstSource.EOF
    If Not IsNull(rstSource!cpn_hed_id) Then
        Set rstCtr = dbsCtr.OpenRecordset("Select cpn_hed_id From hr_contractplans Where cpn_id = " & rstSource!cpn_id)
        rstCtr.Edit
        rstCtr!cpn_hed_id = rstSource!cpn_hed_id
        rstCtr.Update
        rstCtr.Close
        End If
    rstSource.MoveNext
    Loop
rstSource.Close

wksCtr.CommitTrans
strCommit = "Committed"
DoCmd.Close

Exit Sub
Error_Part:
If strCommit = "Begun" Then
    wksCtr.Rollback
    MsgBox "Contract cannot be changed." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub

Private Sub cmdDiscardnExit_Click()
Me.Undo
DoCmd.Close
End Sub



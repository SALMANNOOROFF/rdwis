VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_extend"
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
Set rstExt = dbsExt.OpenRecordset("Select * From hr_contracts_u_last where ctr_num = '" & arrArgs(1) & "'", dbOpenSnapshot)
Me.ctr_num = arrArgs(1)
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
Me.subContractPlan.Requery

Me.Visible = True

End Sub

Private Sub new_termindt_BeforeUpdate(Cancel As Integer)
If IsNull(Me.ctr_termindt) And Me.new_termindt <= Me.ctr_enddt Then
    MsgBox "Contract termination date should be greater than contract end date.", vbCritical
    Cancel = True
    Me.ctr_termindt.Undo
    Else
    If Me.new_termindt <= Me.ctr_termindt Then
        MsgBox "Contract termination date should be greater than current contract termination date.", vbCritical
        Cancel = True
        Me.ctr_termindt.Undo
        End If
    End If
End Sub

Private Sub new_termindt_AfterUpdate()
Dim dtStart As Date
If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
    dtStart = FirstDateThisMonth(DateAdd("m", 1, IIf(IsNull(Me.ctr_termindt), Me.ctr_enddt, ctr_termindt)))
    GeneratePlan "Contract", dtStart, Me.new_termindt
    Me.subContractPlan.Requery
    If Me.subContractPlan.Form.Recordset.RecordCount > 0 Then
        Me.projects_list.Visible = True
        Me.subContractPlan.Visible = True
        Else
        Me.projects_list.Visible = False
        Me.subContractPlan.Visible = False
        End If
    End If
End Sub

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

'Check Conditions ----------------------------------------------
If IsNull(Me!new_termindt) Then
    MsgBox "Please enter extended contract end date.", vbCritical
    Exit Sub
    End If
    
'booAllGood = True
'If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
'    Set rstCtr = Me.subContractPlan.Form.RecordsetClone
'    Do While Not rstCtr.EOF
'        If Nz(rstCtr!cpn_hed_id, "") = "" Then booAllGood = False
'        rstCtr.MoveNext
'        Loop
'    If booAllGood = False Then
'        MsgBox "Please enter projects for all months in the plan.", vbCritical
'        Exit Sub
'        End If
'    End If

'Execute --------------------------------------------------------
Set wksCtr = DBEngine.Workspaces(0)
wksCtr.BeginTrans
strCommit = "Begun"

'Edit contract
Set dbsCtr = CurrentDb()
Set rstCtr = dbsCtr.OpenRecordset("Select * From hr_contracts Where ctr_id = " & Me.ctr_id)
rstCtr.Edit
rstCtr!ctr_termindt = Me.new_termindt
rstCtr.Update

'Add contract plan if the employee is not centrally hired
If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
    Set rstSource = dbsCtr.OpenRecordset("hr_contractplan_temp")
    Set rstCtr = dbsCtr.OpenRecordset("hr_contractplans")
    Do While Not rstSource.EOF
        rstCtr.AddNew
        rstCtr!cpn_ctr_id = Me.ctr_id
        rstCtr!cpn_startdt = rstSource!cpn_startdt
        rstCtr!cpn_enddt = rstSource!cpn_enddt
        rstCtr!cpn_hed_id = rstSource!cpn_hed_id
        rstCtr.Update
        rstSource.MoveNext
        Loop
    rstCtr.Close
    rstSource.Close
    End If

'Add in attendance sheet
AddInAttendanceSheet Me!ctr_num, Forms!hr_contracts!empnamecomp, Me!ctr_unt_id, Me!ctr_startdt

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



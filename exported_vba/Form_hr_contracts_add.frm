VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim dbsNew As Database
Dim rstNew As Recordset

arrArgs = Split(Me.OpenArgs, "~")
Set dbsNew = CurrentDb()
Set rstNew = dbsNew.OpenRecordset("Select * From hr_contracts_u_last where ctr_num = '" & arrArgs(1) & "'", dbOpenSnapshot)
If rstNew.EOF = True Then
    Me.ctr_date.SetFocus
    Me.ctr_num = arrArgs(1)
    If Forms!hr_emps_detail!emp_unt_id >= 200000 And Forms!hr_emps_detail!emp_unt_id < 800000 Then
        Me.subContractPlan.Visible = True
        Me.projects_list.Visible = True
        End If
    Exit Sub
    End If

Me.contract_id = rstNew!ctr_id
Me.contract_date = rstNew!ctr_date
Me.contract_unt_id = rstNew!ctr_unt_id
Me.contract_startdt = rstNew!ctr_startdt
Me.contract_enddt = rstNew!ctr_enddt
Me.contract_termindt = rstNew!ctr_termindt
Me.contract_termindtinv = rstNew!ctr_termindt
Me.contract_jobtitle = rstNew!ctr_jobtitle
Me.contract_grade = rstNew!ctr_grade
Me.contract_type = rstNew!ctr_type
Me.contract_salary = rstNew!ctr_salary
Me.contract_probnsal = IIf(IsNull(rstNew!ctr_prob), Null, rstNew!ctr_prob & " months, " & rstNew!ctr_probsal)
Me.contract_effenddt = IIf(IsNull(Me.contract_termindt), Me.contract_enddt, Me.contract_termindt)

Me.ctr_date.SetFocus
Me.ctr_num = arrArgs(1)
Me.ctr_unt_id = rstNew!ctr_unt_id
Me.ctr_startdt.Value = DateAdd("d", 1, Me.contract_effenddt)
Me.ctr_enddt = DateAdd("d", -1, DateAdd("yyyy", 1, DateAdd("d", 1, Me.contract_effenddt)))
Me.ctr_jobtitle = rstNew!ctr_jobtitle
Me.ctr_grade = rstNew!ctr_grade
Me.ctr_type = rstNew!ctr_type
Me.ctr_salary = rstNew!ctr_salary
rstNew.Close

If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
    GeneratePlan "Contract", Me.ctr_startdt, Me.ctr_enddt
    Me.subContractPlan.Requery
    Me.subContractPlan.Visible = True
    Me.projects_list.Visible = True
    End If
Me.Visible = True

End Sub

Private Sub ctr_startdt_BeforeUpdate(Cancel As Integer)

If IsNull(Me.contract_enddt) Then Exit Sub          'If there is no previous contract then exit

If Me.ctr_startdt <= Me.contract_startdt Then
    MsgBox "Contract start date is not valid. New contracts can only start after the old contract.", vbCritical
    Cancel = True
    Me.ctr_startdt.Undo
    Exit Sub
    End If

If Me.ctr_startdt > DateAdd("d", 1, Me.contract_effenddt) Then
    MsgBox "Contract start date is not valid. New contract has to start immediately after previous contract.", vbCritical
    Cancel = True
    Me.ctr_startdt.Undo
    Exit Sub
    End If
    
End Sub

Private Sub ctr_startdt_AfterUpdate()
If IsNull(Me.contract_enddt) Then Exit Sub          'If there is no previous contract then exit
If Me.ctr_startdt <= Me.contract_effenddt Then
    Me.lblWarning.Visible = True
    Me.contract_termindt = DateAdd("d", -1, Me!ctr_startdt)
    Me.contract_remarks.BorderStyle = 1
    Me.contract_remarks.Locked = False
    Me.contract_remarks.Enabled = True
    Me.contract_termindt.BackColor = 62207
    Else
    Me.lblWarning.Visible = False
    Me.contract_termindt = Me.contract_termindtinv
    Me.contract_remarks.BorderStyle = 0
    Me.contract_remarks = Null
    Me.contract_remarks.Locked = True
    Me.contract_remarks.Enabled = False
    Me.contract_termindt.BackColor = 15064278
    End If

GeneratePlan "Contract", Me.ctr_startdt, Me.ctr_enddt
Me.subContractPlan.Requery
End Sub

Private Sub ctr_enddt_AfterUpdate()
GeneratePlan "Contract", Me.ctr_startdt, Me.ctr_enddt
Me.subContractPlan.Requery
End Sub

Private Sub ctr_unt_id_AfterUpdate()
Me.projects_list.Requery
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
Dim lngCtrId As Long
Dim booAllGood As Boolean
On Error GoTo Error_Part

Me.Dirty = False

'Check Conditions ----------------------------------------------
If IsNull(Me!ctr_date) Then
    MsgBox "Please enter contract signing date.", vbCritical
    Exit Sub
    End If
    
If IsNull(Me.ctr_date) Or IsNull(Me.ctr_unt_id) Or IsNull(Me.ctr_startdt) Or IsNull(Me.ctr_enddt) Or _
   IsNull(ctr_type) Or IsNull(ctr_grade) Or IsNull(ctr_salary) Then
    MsgBox "Incomplete data.", vbCritical
    Exit Sub
    End If
    
If Not IsNull(Me.contract_enddt) Then
    If Me.ctr_startdt <= Me.contract_effenddt Then
        If IsNull(Me.contract_remarks) Then
            MsgBox "Please enter reasons of early cancellation of previous contract.", vbCritical
            Me.contract_remarks.SetFocus
            Exit Sub
            End If
        End If
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

'Add contract
Set dbsCtr = CurrentDb()
Set rstCtr = dbsCtr.OpenRecordset("hr_contracts", dbOpenDynaset, dbSeeChanges)
With rstCtr
    .AddNew
    !ctr_num = Me.ctr_num
    !ctr_date = Me.ctr_date
    !ctr_startdt = Me.ctr_startdt
    !ctr_enddt = Me.ctr_enddt
    !ctr_unt_id = Me.ctr_unt_id
    !ctr_type = Me.ctr_type
    !ctr_grade = Me.ctr_grade
    !ctr_jobtitle = Me.ctr_jobtitle
    !ctr_salary = Me.ctr_salary
    If Nz(Me.ctr_prob, "") <> "" Then !ctr_prob = Me.ctr_prob
    If Nz(Me.ctr_probsal, "") <> "" Then !ctr_probsal = Me.ctr_probsal
    .Update
    .Bookmark = .LastModified
    lngCtrId = !ctr_id
    .Close
    End With

'Add contract plan if the employee is not centrally hired
If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
    Set rstSource = dbsCtr.OpenRecordset("hr_contractplan_temp")
    Set rstCtr = dbsCtr.OpenRecordset("hr_contractplans")
    Do While Not rstSource.EOF
        rstCtr.AddNew
        rstCtr!cpn_ctr_id = lngCtrId
        rstCtr!cpn_startdt = rstSource!cpn_startdt
        rstCtr!cpn_enddt = rstSource!cpn_enddt
        rstCtr!cpn_hed_id = rstSource!cpn_hed_id
        rstCtr.Update
        rstSource.MoveNext
        Loop
    rstCtr.Close
    rstSource.Close
    End If

'Update tremination date of last contract if required
If Not IsNull(Me.contract_enddt) Then
    If Me.ctr_startdt <= Me.contract_effenddt Then
        Set rstCtr = dbsCtr.OpenRecordset("Select * From hr_contracts where ctr_id = " & Me.contract_id)
        rstCtr.Edit
        rstCtr!ctr_termindt = Me.contract_termindt
        rstCtr!ctr_remarks = Me.contract_remarks
        rstCtr.Update
        rstCtr.Close
        End If
    End If

'Update unit in employees table if required
If Me.contract_unt_id <> Me.ctr_unt_id Then
    Set rstCtr = dbsCtr.OpenRecordset("Select * From hr_emps Where emp_id = '" & Me.ctr_num & "'")
    rstCtr.Edit
    rstCtr!emp_unt_id = Me.ctr_unt_id
    rstCtr.Update
    rstCtr.Close
    End If

'Reset Salary Head
Set rstCtr = dbsCtr.OpenRecordset("Select * From fin_empeffheads Where eeh_emp_id = '" & Me.ctr_num & "'")
rstCtr.Edit
rstCtr!eeh_emphed_id = Null
rstCtr!eeh_sudohed = Null
rstCtr!eeh_dtg = GetNow()
rstCtr.Update
rstCtr.Close

'Add Contract Verification
Set rstCtr = dbsCtr.OpenRecordset("fin_contractsverif")
rstCtr.AddNew
rstCtr!cvf_ctr_id = lngCtrId
rstCtr.Update
rstCtr.Close

'Add in attendance sheet
AddInAttendanceSheet Me!ctr_num, Forms!hr_contracts!empnamecomp, Me!ctr_unt_id, Me!ctr_startdt

wksCtr.CommitTrans
strCommit = "Committed"
DoCmd.Close

'Set qdfCtr = dbsCtr.QueryDefs("hr_contractplan_add")
'Set qdfCtr = dbsCtr.QueryDefs("hr_update_empdata")
'Set qdfCtr = dbsRes.QueryDefs("hr_reset_salaryhead")
'Set qdfCtr = dbsCtr.QueryDefs("fin_contractsverif_add")

Exit Sub
Error_Part:
If strCommit = "Begun" Then
    wksCtr.Rollback
    MsgBox "Contract cannot be added." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub

Private Sub cmdDiscardnExit_Click()
Me.Undo
DoCmd.Close
End Sub

'Private Sub EditEmpsDataAsPerContract(EmpId As String, UnitId As Variant)
'Dim dbsData As Database
'Dim qdfData As QueryDef
'
'Set dbsData = CurrentDb()
'Set qdfData = dbsData.QueryDefs("hr_update_empdata")
'qdfData.Parameters("Emp") = EmpId
'qdfData.Parameters("Unit") = UnitId
'qdfData.Execute
'qdfData.Close
'End Sub
'
'Private Sub ResetSalaryHead(EmpId)
'Dim dbsRes As Database
'Dim qdfRes As QueryDef
'
'Set dbsRes = CurrentDb()
'Set qdfRes = dbsRes.QueryDefs("hr_reset_salaryhead")
'qdfRes.Parameters("Emp") = EmpId
'qdfRes.Parameters("EntryDtg") = GetNow()
'qdfRes.Execute
'qdfRes.Close
'End Sub
'
'Private Sub AddContractVerif(CtrId As Long)
'Dim dbsNew As Database
'Dim qdfNew As QueryDef
'
'Set dbsNew = CurrentDb
'Set qdfNew = dbsNew.QueryDefs("fin_contractsverif_add")
'qdfNew.Parameters("ContractId") = CtrId
'qdfNew.Execute
'End Sub




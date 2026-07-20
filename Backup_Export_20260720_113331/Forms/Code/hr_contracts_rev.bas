VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub Form_Open(Cancel As Integer)
Dim dbsDR As Database
Dim rstDR As Recordset

Me.object_id = Forms!hr_contracts!subContracts.Form!ctr_id
Me.unit_id = Forms!hr_contracts!subContracts.Form!ctr_unt_id

Me.ctr_date = Forms!hr_contracts!subContracts.Form!ctr_date
Me.ctr_date_old = Forms!hr_contracts!subContracts.Form!ctr_date

Me.ctr_unt_id = Forms!hr_contracts!subContracts.Form!ctr_unt_id.Column(1)
Me.ctr_unt_id_old = Forms!hr_contracts!subContracts.Form!ctr_unt_id.Column(1)

Me.ctr_grade = Forms!hr_contracts!subContracts.Form!ctr_grade
Me.ctr_grade_old = Forms!hr_contracts!subContracts.Form!ctr_grade

Me.ctr_jobtitle = Forms!hr_contracts!subContracts.Form!ctr_jobtitle
Me.ctr_jobtitle_old = Forms!hr_contracts!subContracts.Form!ctr_jobtitle

Me.ctr_startdt = Forms!hr_contracts!subContracts.Form!ctr_startdt
Me.ctr_startdt_old = Forms!hr_contracts!subContracts.Form!ctr_startdt

Me.ctr_enddt = Forms!hr_contracts!subContracts.Form!ctr_enddt
Me.ctr_enddt_old = Forms!hr_contracts!subContracts.Form!ctr_enddt

Me.ctr_termindt = Forms!hr_contracts!subContracts.Form!ctr_termindt
Me.ctr_termindt_old = Forms!hr_contracts!subContracts.Form!ctr_termindt

Me.ctr_salary = Forms!hr_contracts!subContracts.Form!ctr_salary
Me.ctr_salary_old = Forms!hr_contracts!subContracts.Form!ctr_salary

Me.ctr_prob = Forms!hr_contracts!subContracts.Form!ctr_prob
Me.ctr_prob_old = Forms!hr_contracts!subContracts.Form!ctr_prob

Me.ctr_probsal = Forms!hr_contracts!subContracts.Form!ctr_probsal
Me.ctr_probsal_old = Forms!hr_contracts!subContracts.Form!ctr_probsal

Me.emp_unt_id = Forms!hr_contracts!subContracts.Form!ctr_unt_id.Column(1)
Me.emp_unt_id_old = Forms!hr_contracts!subContracts.Form!ctr_unt_id.Column(1)

Me.ctr_remarks = Forms!hr_contracts!subContracts.Form!ctr_remarks
Me.ctr_remarks_old = Forms!hr_contracts!subContracts.Form!ctr_remarks

Set dbsDR = CurrentDb()
Set rstDR = dbsDR.OpenRecordset("Select * From fin_contractsverif Where cvf_ctr_id = " & Me.object_id, dbOpenSnapshot)
Me.cvf_verif = rstDR!cvf_verif
Me.cvf_verif_old = rstDR!cvf_verif

If Me.OpenArgs = "Previous" Then
    Me.ctr_date.Enabled = False
    Me.ctr_unt_id.Enabled = False
    Me.ctr_grade.Enabled = False
    Me.ctr_jobtitle.Enabled = False
    Me.ctr_startdt.Enabled = False
    Me.ctr_termindt.Enabled = False
    Me.ctr_salary.Enabled = False
    Me.ctr_prob.Enabled = False
    Me.ctr_probsal.Enabled = False
    Me.Option2.Enabled = False
    Me.ctr_enddt.SetFocus
    End If

Me.Visible = True

End Sub

Private Sub cmdGenerate_Click()

Dim dbsGen As Database
Dim rstGen As Recordset
Dim intRevType As Integer
Dim intresponse As Integer
Dim lngRevId As Long

Me.Dirty = False

Select Case Me.frmOption
    Case 1
        If AnyChangeOnForm = False Then
            MsgBox "No change in data requested.", vbCritical
            Exit Sub
            End If
        intRevType = 2
    Case 2
        'Check if a salary has been generated against this contract
        Set dbsGen = CurrentDb()
        Set rstGen = dbsGen.OpenRecordset("Select srq_id from hr_salreqs where srq_status <> 'Cancelled' And Inlist(" & Me.object_id & ",[srq_contracts]) = True", dbOpenSnapshot)
        If Not rstGen.EOF Then
            MsgBox "The contract cannot be reversed as a salary requisition for this contract exists.", vbCritical
            Exit Sub
            End If
        intRevType = 1
    End Select
    
intresponse = MsgBox("A Data Revision Request for Contract " & Me.object_id & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Contract", Me.object_id, Me.unit_id, intRevType)
DoCmd.Close acForm, "hr_contracts_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_contracts"
DoCmd.Close acForm, "hr_emps_detail"
DoCmd.Close acForm, "hr_emps_u"
   
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub ctr_prob_BeforeUpdate(Cancel As Integer)
If Not IsNumeric(Nz(Me.ctr_prob, 0)) Then
    Cancel = True
    Me.ctr_prob.Undo
    MsgBox "Please enter duration of probation in months.", vbCritical
    End If
End Sub

Private Sub ctr_probsal_BeforeUpdate(Cancel As Integer)
If Not IsNumeric(Nz(Me.ctr_probsal)) Then
    Cancel = True
    Me.ctr_probsal.Undo
    MsgBox "Please enter salary for probation period.", vbCritical
    End If
End Sub

Private Sub ctr_prob_AfterUpdate()
Me.cvf_verif = Abs(CInt(Nz(ctr_prob, "") <> Nz(ctr_prob_old, "")))
End Sub

Private Sub ctr_probsal_AfterUpdate()
Me.cvf_verif = IIf(Nz(ctr_probsal) = Nz(ctr_probsal_old) And Nz(ctr_salary) = Nz(ctr_salary_old), Me.cvf_verif_old, 0)
End Sub

Private Sub ctr_unt_id_AfterUpdate()
Me.emp_unt_id = Me.ctr_unt_id
End Sub

Private Sub ctr_salary_AfterUpdate()
Me.cvf_verif = IIf(Nz(ctr_salary) = Nz(ctr_salary_old) And Nz(ctr_probsal) = Nz(ctr_probsal_old), Me.cvf_verif_old, 0)
End Sub



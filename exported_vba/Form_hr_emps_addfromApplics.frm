VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_addfromApplics"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmbCnic_AfterUpdate()
Me.CnicApl = Me.cmbCnic.Column(1)
Me.NameApl = Me.cmbCnic.Value
Me.RankApl = Me.cmbCnic.Column(2)
End Sub

Private Sub cmdAdd_Click()
On Error GoTo Error_Handler
Dim dbsEmp As Database
Dim rstEmp As Recordset

Set dbsEmp = CurrentDb()

'Check if the applicant cinc exists in employees table
Set rstEmp = dbsEmp.OpenRecordset("Select * From hr_emps Where emp_cnic = '" & Me.CnicApl & "'", dbOpenSnapshot)
If rstEmp.EOF Then GoTo Action:

'Check if the applicant cinc exists in employees table with status 'Active*'
rstEmp.FindFirst ("emp_status like 'Active*'")
If Not rstEmp.NoMatch Then
    MsgBox "Cannot add the applicant to employees. An active employee is present with same CNIC.", vbCritical
    Exit Sub
    End If

Action:
Forms!hr_emps_detail.emp_cnic = Me.CnicApl
Forms!hr_emps_detail.emp_name = Me.NameApl
Forms!hr_emps_detail.emp_rank = Me.RankApl
DoCmd.Close
Forms!hr_emps_detail.emp_id.SetFocus
Forms!hr_emps_detail.cmdAddFromApplics.Visible = False
Exit Sub
Error_Handler:
MsgBox Error$
End Sub

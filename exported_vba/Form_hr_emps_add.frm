VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsUnit As Database
Dim rstUnit As Recordset
Dim strUnitNum As String

Me.emp_name = Forms!hr_ctrcasehg_detail!ctc_empnamecomp
Me.emp_unt_id = Forms!hr_ctrcasehg_detail!ctc_unt_id

If IsNull(Me!emp_unt_id) Then
    strUnitNum = ""
    Else
    Set dbsUnit = CurrentDb()
    Set rstUnit = dbsUnit.OpenRecordset("Select unt_num From units Where unt_id = " & Me!emp_unt_id, dbOpenSnapshot)
    strUnitNum = IIf(rstUnit.EOF, "", rstUnit!unt_num)
    End If
Me!emp_id = EmpIdChanger(Me!emp_id, 0, IIf(IsNull(Me!emp_unt_id), "", Format(strUnitNum, "00")))

End Sub
Private Sub emp_cnic_AfterUpdate()
Me!emp_id = EmpIdChanger(Me!emp_id, 3, IIf(Nz(Me!emp_cnic, "") = "", "", Mid(Me!emp_cnic, 11, 3) & Right(Me!emp_cnic, 1)))
End Sub

Private Sub emp_unt_id_AfterUpdate()
Dim dbsUnit As Database
Dim rstUnit As Recordset
Dim strUnitNum As String

If IsNull(Me!emp_unt_id) Then
    strUnitNum = ""
    Else
    Set dbsUnit = CurrentDb()
    Set rstUnit = dbsUnit.OpenRecordset("Select unt_num From units Where unt_id = " & Me!emp_unt_id, dbOpenSnapshot)
    strUnitNum = IIf(rstUnit.EOF, "", rstUnit!unt_num)
    End If
Me!emp_id = EmpIdChanger(Me!emp_id, 0, IIf(IsNull(Me!emp_unt_id), "", Format(strUnitNum, "00")))
End Sub

Private Sub emp_joindt_AfterUpdate()
Me!emp_id = EmpIdChanger(Me!emp_id, 1, IIf(IsNull(Me!emp_joindt), "", Format(Me!emp_joindt, "yy")))
Me!emp_id = EmpIdChanger(Me!emp_id, 2, IIf(IsNull(Me!emp_joindt), "", Format(Me!emp_joindt, "mm")))
End Sub

Private Function EmpIdChanger(CurrentEmpId As String, PartPos As Integer, PartText As String) As String
Dim arrEmpId() As String
arrEmpId = Split(CurrentEmpId, "-")
arrEmpId(PartPos) = PartText
EmpIdChanger = Join(arrEmpId, "-")
End Function

Private Sub cmdAddFromApplics_Click()
On Error GoTo cmdAddFromApplics_Click_Err

Me.Dirty = False
DoCmd.OpenForm "hr_emps_addfromApplics", acNormal, "", "", , acNormal

cmdAddFromApplics_Click_Exit:
    Exit Sub

cmdAddFromApplics_Click_Err:
    MsgBox Error$
    Resume cmdAddFromApplics_Click_Exit

End Sub

Private Sub cmdDiscardnExit_Click()
DoCmd.Close
End Sub

Private Sub cmdSavenExit_Click()
'Me.Dirty = True
'Checks -----------------------------------------------------------------------------------
If Nz(Me!emp_cnic, "") = "" Then
    MsgBox "Please enter CNIC."
    Exit Sub
    End If

If IsNull(Me!emp_unt_id) Then
    MsgBox "Please enter department."
    Exit Sub
    End If

If Nz(Me!emp_name, "") = "" Then
    MsgBox "Please enter employee name."
    Exit Sub
    End If

If IsNull(Me!emp_joindt) Then
    MsgBox "Please enter joining date."
    Exit Sub
    End If

'Add Data ---------------------------------------------------------------------------------
Dim wksEmp As Workspace
Dim dbsEmp As Database
Dim rstEmp As Recordset
Dim strCommit As String
On Error GoTo Error_Part

Set wksEmp = DBEngine.Workspaces(0)
wksEmp.BeginTrans
strCommit = "Begun"

'***Add employee
Set dbsEmp = CurrentDb
Set rstEmp = dbsEmp.OpenRecordset("hr_emps")
With rstEmp
    .AddNew
    !emp_id = Me.emp_id
    !emp_cnic = Me.emp_cnic
    !emp_unt_id = Me.emp_unt_id
    !emp_name = Me.emp_name
    If Nz(Me.emp_title, "") <> "" Then !emp_title = Me.emp_title
    If Nz(Me.emp_rank, "") <> "" Then !emp_rank = Me.emp_rank
    !emp_joindt = Me.emp_joindt
    !emp_status = Me.emp_status
    .Update
    End With
    
'***Add attachment slots
CreateAttachmentSlot "emp", Me.emp_id, "Appointment Letter"
CreateAttachmentSlot "emp", Me.emp_id, "Notice"

'***Add salary head
Set rstEmp = dbsEmp.OpenRecordset("fin_empeffheads")
rstEmp.AddNew
rstEmp!eeh_emp_id = Me!emp_id
rstEmp!eeh_status = "Open"
rstEmp.Update

'***Add attendance records from joining month till current month if the attendance sheet has been generated
AddInAttendanceSheet Me!emp_id, NameComplete(Me!emp_name, Me!emp_title, Me!emp_rank), Me!emp_unt_id, Me!emp_joindt

'***Add Employee ID to the hiring case
Forms!hr_ctrcasehg_detail!ctc_emp_id = Me.emp_id

wksEmp.CommitTrans
strCommit = "Committed"
MsgBox "Employee added.", vbInformation
DoCmd.Close
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksEmp.Rollback
    MsgBox "Employee cannot be added." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If

End Sub


VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)

Me.object_id = Forms!hr_emps_detail!emp_id
Me.unit_id = Forms!hr_emps_detail!emp_unt_id

Me.emp_id = Forms!hr_emps_detail!emp_id
Me.emp_id_old = Forms!hr_emps_detail!emp_id

Me.emp_cnic = Forms!hr_emps_detail!emp_cnic
Me.emp_cnic_old = Forms!hr_emps_detail!emp_cnic

Me.emp_name = Forms!hr_emps_detail!emp_name
Me.emp_name_old = Forms!hr_emps_detail!emp_name

Me.emp_title = Forms!hr_emps_detail!emp_title
Me.emp_title_old = Forms!hr_emps_detail!emp_title

Me.emp_rank = Forms!hr_emps_detail!emp_rank
Me.emp_rank_old = Forms!hr_emps_detail!emp_rank

Me.emp_joindt = Forms!hr_emps_detail!emp_joindt
Me.emp_joindt_old = Forms!hr_emps_detail!emp_joindt

Me.emp_photodest = Forms!hr_emps_detail!emp_photodest
Me.emp_photodest_old = Forms!hr_emps_detail!emp_photodest

Me.Visible = True
End Sub

Private Sub chkRemovePhoto_AfterUpdate()
Select Case Me.chkRemovePhoto
    Case 0
        Me.emp_photodest = Me.emp_photodest_old
        Me.boxRemovePhoto.BackStyle = 0
    Case -1
        Me.emp_photodest = Null
        Me.boxRemovePhoto.BackStyle = 1
    End Select
End Sub

Private Sub cmdGenerate_Click()
Dim intResponse As Integer
Dim lngRevId As Long

Me.Dirty = False
If AnyChangeOnForm = False Then
    MsgBox "No change in data requested.", vbCritical
    Exit Sub
    End If
    
intResponse = MsgBox("A Data Revision Request for Emp " & Me!emp_id & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Employee", Me.object_id, Me.unit_id, 2)
DoCmd.Close acForm, "hr_emps_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_emps_detail"
DoCmd.Close acForm, "hr_emps_u"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub





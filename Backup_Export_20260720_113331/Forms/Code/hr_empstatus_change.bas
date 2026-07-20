VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_empstatus_change"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.empname = NameComplete(Forms!hr_emps_detail!emp_name, Forms!hr_emps_detail!emp_title, Forms!hr_emps_detail!emp_rank)
Me.id = Forms!hr_emps_detail!emp_id
Me.joindt = Forms!hr_emps_detail!emp_joindt
Me.lastdt = Forms!hr_emps_detail!emp_lastdt
Me.Status = Forms!hr_emps_detail!emp_status
Me.remarks = Forms!hr_emps_detail!emp_remarks
End Sub

Private Sub cmdSave_Click()
Select Case Me.Status
    Case "Active"
        If Not IsNull(Me.lastdt) Then
            MsgBox "Last day is not allowed with current status.", vbCritical
            Exit Sub
            End If
    Case "Active (on notice)", "Terminated", "Released"
        If IsNull(Me.lastdt) Then
            MsgBox "Last day is required with current status.", vbCritical
            Exit Sub
            End If
        Dim rstCtr As Recordset
        Dim lngCtr As Long
        Set rstCtr = CurrentDb().OpenRecordset("Select ctr_id, ctr_effenddt From hr_contracts_u_last Where ctr_num = '" & Me.id & "'", dbOpenSnapshot)
        If Me.lastdt > rstCtr!ctr_effenddt Then
            MsgBox "Last date cannot be greater than contract end date. If required, extend the contract.", vbCritical
            Exit Sub
            End If
        If Me.lastdt < rstCtr!ctr_effenddt Then
            lngCtr = rstCtr!ctr_id
            rstCtr.Close
            Set rstCtr = CurrentDb().OpenRecordset("Select ctr_termindt From hr_contracts Where ctr_id = " & lngCtr)
            rstCtr.Edit
            rstCtr!ctr_termindt = Me.lastdt
            rstCtr.Update
            rstCtr.Close
            AdjustPlan "Contract", lngCtr
            End If
    End Select
Forms!hr_emps_detail!emp_lastdt = Me.lastdt
Forms!hr_emps_detail!emp_status = Me.Status
Forms!hr_emps_detail!emp_remarks = Me.remarks
Forms!hr_emps_detail.Dirty = False
DoCmd.Close acForm, "hr_empstatus_change"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From hr_emps Where emp_id = '" & arrArgs(1) & "'"
If Me.subContracts.Form.Recordset.RecordCount > 0 Then Me.last_contract = Me.subContracts.Form!ctr_id
If getVar("varMode") = "approver-s" And Me.emp_status Like "Active*" Then
    Me.cmdContractAdd.Visible = True
    End If
Me.Visible = True
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdContractAdd_Click()
Dim dbsAddCtr As Database
Dim rstAddCtr As Recordset
Dim lngCaseId As Long
Dim intDecision As Integer
On Error GoTo cmdContractAdd_Click_Err

Set dbsAddCtr = CurrentDb
Set rstAddCtr = dbsAddCtr.OpenRecordset("Select ctc_status From hr_ctrcases Where ctc_emp_id = '" & Me.emp_id & "' And " & _
                                         "ctc_status Not In('Fulfilled','Not Approved', 'Cancelled')", dbOpenSnapshot)
If Not rstAddCtr.EOF Then
    MsgBox "A contract case of this employee exists in draft or open status. A new case cannot be raised.", vbCritical, "Denied"
    Exit Sub
    End If

intDecision = MsgBox("A contract renewal case will be created. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intDecision = 7 Then Exit Sub

lngCaseId = AddCtrCase(Me.emp_id, "Cr")
DoCmd.OpenForm "hr_ctrcase_detail", acNormal, "", "", , acNormal, "Draft~" & lngCaseId

cmdContractAdd_Click_Exit:
    Exit Sub

cmdContractAdd_Click_Err:
    MsgBox Error$
    Resume cmdContractAdd_Click_Exit

End Sub

Private Sub cmdEmpRecord_Click()
On Error GoTo cmdEmpRecord_Click_Err

DoCmd.OpenReport "hr_contracts", acViewReport, "", "", acNormal

cmdEmpRecord_Click_Exit:
    Exit Sub

cmdEmpRecord_Click_Err:
    MsgBox Error$
    Resume cmdEmpRecord_Click_Exit

End Sub



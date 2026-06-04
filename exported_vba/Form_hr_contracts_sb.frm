VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_sb"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdCtr_Click()
DoCmd.OpenForm "hr_contracts_detail", acNormal, "", "", , acHidden, Me.ctr_id & "~" & Me.ctr_num & "~" & _
                Me.Parent.empnamecomp & "~" & Me.Parent.emp_unt_id & "~" & Me.Parent.emp_joindt & "~" & _
                Me.Parent.emp_hed_id & "~" & Me.Parent.emp_lastdt & "~" & Me.Parent.emp_status & "~" & _
                Me.Parent.last_contract
cmdCtr_Click_Exit:
    Exit Sub

cmdCtr_Click_Err:
    MsgBox Error$
    Resume cmdCtr_Click_Exit
End Sub

Private Sub ctr_path_Click()
Dim strTitle As String
strTitle = "Contract " & Me!ctr_num & " dated " & Me!ctr_date & "  -  " & NameComplete(Me.Parent!emp_name, Me.Parent!emp_title, Me.Parent!emp_rank)
FileResponse "ctr", Me!ctr_id, "Contract", Me!ctr_id, Nz(Me!ctr_path, ""), Me.Parent.Name, strTitle
End Sub

Private Sub ctr_path2_Click()
Dim strTitle As String
If Nz(Me!ctr_path2, "") = "" And Not IsNull(Me!ctr_ctc_id) Then
    MsgBox "Please attach minute with contract case " & Me!ctr_ctc_id & ".", vbCritical
    Exit Sub
    End If
strTitle = "Contract " & Me!ctr_num & " dated " & Me!ctr_date & "  -  " & NameComplete(Me.Parent!emp_name, Me.Parent!emp_title, Me.Parent!emp_rank)
FileResponse "ctr", Me!ctr_id, "Minute", Me!ctr_id, Nz(Me!ctr_path2, ""), Me.Parent.Name, strTitle
End Sub

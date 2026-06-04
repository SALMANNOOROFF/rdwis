VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_contracts_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim rstCtr As Recordset
Dim booBlanks As Boolean

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From hr_contracts Where ctr_id = " & arrArgs(0)
Me.emp_id = arrArgs(1)
Me.empnamecomp = arrArgs(2)
Me.emp_unt_id = arrArgs(3)
Me.emp_joindt = Format(arrArgs(4), "dd mmm yy")
Me.emp_hed_id = arrArgs(5)
Me.emp_lastdt = Format(arrArgs(6), "dd mmm yy")
Me.emp_status = arrArgs(7)
Me.last_contract = CLng(arrArgs(8))
Me.subPlan.Form.OrderBy = "[cpn_startdt]"
Me.subPlan.Form.OrderByOn = True

If getVar("varMode") = "approver-s" And Me.emp_status Like "Active*" Then
    Me.cmdReverse.Visible = True
    Me.cmdReverse2.Visible = True
    If Me!ctr_id = Me.last_contract Then
        Me.cmdContractExtend.Visible = True
        End If
    End If

If getVar("varMode") = "approver-s" And getVar("varUnitArea") = "prj" Then
    booBlanks = False
    If Me.ctr_unt_id >= 200000 And Me.ctr_unt_id < 800000 Then
        Set rstCtr = Me.subPlan.Form.RecordsetClone
        Do While Not rstCtr.EOF
            If Nz(rstCtr!cpn_hed_id, "") = "" Then booBlanks = True
            rstCtr.MoveNext
            Loop
        If booBlanks = True Then Me.cmdAssignProject.Visible = True
        End If
    End If

If getVar("varMode") = "approver-s" And getVar("varUnitArea") = "prj" Then
    Me.cmdReverse.Visible = True
    Me.cmdReverse2.Visible = True
    End If
If getVar("varUnitArea") = "prj" Or getVar("varUnitArea") = "hr" Or getVar("varUnitArea") = "rdw" Then
    Me.cmdAttendance.Visible = True
    End If
If getVar("varUnitArea") = "hr" Then
    Me.cmdContract.Visible = True
    End If
    
Me.Visible = True
End Sub

Private Sub Form_Activate()
Me.Refresh
End Sub

Private Sub cmdContract_Click()
On Error GoTo cmdContract_Click_Err

DoCmd.OpenReport "hr_contract_doc", acViewReport, "", "", acNormal

cmdContract_Click_Exit:
    Exit Sub

cmdContract_Click_Err:
    MsgBox Error$
    Resume cmdContract_Click_Exit

End Sub

Private Sub cmdAttendance_Click()
On Error GoTo cmdAttendance_Click_Err
Dim dbsAttMon As Database
Dim dcnAttMon As Variant
Dim dtStart As Date
Dim dtEnd As Date
Dim dtToday As Date

Me.Dirty = False
Set dbsAttMon = CurrentDb()
dtToday = DateValue(GetNow)

dtStart = Me!ctr_startdt
If Me.emp_joindt > dtStart Then dtStart = Me.emp_joindt
Forms!vars.Parameter1 = dtStart
dtEnd = Me!ctr_enddt
If Not IsNull(Me!ctr_termindt) Then _
    dtEnd = Me!ctr_termindt
If dtToday < dtEnd Then dtEnd = dtToday
Forms!vars.Parameter2 = dtEnd

dbsAttMon.Execute "Delete From hr_attendance_summary_temp"
Set dcnAttMon = EmpAttendance(Me!emp_id, dtStart, dtEnd)
DictToTable dcnAttMon, "hr_attendance_summary_temp"

DoCmd.OpenReport "hr_attendance_summary_emp", acViewReport, "", "", acNormal

cmdAttendance_Click_Exit:
    Exit Sub

cmdAttendance_Click_Err:
    MsgBox Error$
    Resume cmdAttendance_Click_Exit

End Sub

Private Sub cmdContractExtend_Click()
Dim dbsExtCtr As Database
Dim rstExtCtr As Recordset
Dim lngCaseId As Long
Dim intDecision As Integer
On Error GoTo cmdContractExtend_Click_Err

Set dbsExtCtr = CurrentDb
Set rstExtCtr = dbsExtCtr.OpenRecordset("Select ctc_status From hr_ctrcases Where ctc_emp_id = '" & Me.emp_id & "' And " & _
                                         "ctc_status Not In('Fulfilled','Not Approved', 'Cancelled')", dbOpenSnapshot)
If Not rstExtCtr.EOF Then
    MsgBox "A contract case of this employee exists in draft or open status. A new case cannot be raised.", vbCritical, "Denied"
    Exit Sub
    End If

intDecision = MsgBox("A contract extension case will be created. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intDecision = 7 Then Exit Sub

lngCaseId = AddCtrCase(Me.emp_id, "Ce")
DoCmd.OpenForm "hr_ctrcaseext_detail", acNormal, "", "", , acNormal, "Draft~" & lngCaseId

cmdContractExtend_Click_Exit:
    Exit Sub

cmdContractExtend_Click_Err:
    MsgBox Error$
    Resume cmdContractExtend_Click_Exit

End Sub

Private Sub cmdAssignProject_Click()
On Error GoTo cmdAssignProject_Click_Err

DoCmd.OpenForm "hr_contracts_assign", acNormal, "", "", , acNormal, "OneRecord~" & Me.ctr_id

cmdAssignProject_Click_Exit:
    Exit Sub

cmdAssignProject_Click_Err:
    MsgBox Error$
    Resume cmdAssignProject_Click_Exit

End Sub

Private Sub cmdReverse_Click()
Dim rstCtr As Recordset
Dim lngFuture As Long
Dim lngCurrent As Long
Dim lngPrevious As Long
Dim strContractAge As String

Set rstCtr = Me.RecordsetClone
rstCtr.Sort = "ctr_date"
rstCtr.MoveFirst
Do While Not rstCtr.EOF
    Select Case True
        Case Date < rstCtr!ctr_startdt
            lngFuture = rstCtr!ctr_id
        Case Date >= rstCtr!ctr_startdt And Date <= IIf(IsNull(rstCtr!ctr_termindt), rstCtr!ctr_enddt, rstCtr!ctr_termindt)
            lngCurrent = rstCtr!ctr_id
        Case Date > IIf(IsNull(rstCtr!ctr_termindt), rstCtr!ctr_enddt, rstCtr!ctr_termindt)
            If lngPrevious = 0 Then lngPrevious = rstCtr!ctr_id
        End Select
    rstCtr.MoveNext
    Loop

Select Case Me!ctr_id
    Case lngFuture
        strContractAge = "Future"
    Case lngCurrent
        strContractAge = "Current"
    Case lngPrevious
        strContractAge = "Previous"
    Case Else
        strContractAge = "Old"
        MsgBox "Cannot change old contracts.", vbCritical
        Exit Sub
    End Select

DoCmd.OpenForm "hr_contracts_rev", acNormal, "", "", , acHidden, strContractAge

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

Private Sub cmdReverse2_Click()
Dim dbsGen As Database
Dim rstGen As Recordset
Dim qdfGen As QueryDef

DoCmd.OpenForm "hr_contractplans_rev", acNormal, "", ""

End Sub


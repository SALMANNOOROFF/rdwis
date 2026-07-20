VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcase_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
arrArgs = Split(Me.OpenArgs, "~")

Me.RecordSource = "Select * From hr_ctrcases Where ctc_id = " & arrArgs(1)
Select Case arrArgs(0)
    Case "Draft", "Under Revision"
        Select Case getVar("varMode")
            Case "editor-s", "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
                Me.cmdRelease.Visible = True
                Me.cmdMinute.Visible = True
            End Select
    Case "Under Finance Scrutiny"
        Select Case getVar("varMode")
            Case "approver-m"
                If getVar("varUnitArea") = "fin" Then
                    Me.cmdForward.Visible = True
                    Me.cmdReturn.Visible = True
                    End If
            End Select
    Case "Under HR Scrutiny"
        Select Case getVar("varMode")
            Case "approver-m"
                If getVar("varUnitArea") = "hr" Then
                    Me.cmdForward.Visible = True
                    Me.cmdReturn.Visible = True
                    End If
            End Select
    Case "Under Approval"
        Select Case getVar("varMode")
            Case "approver-m"
                If getVar("varUnitArea") = "hr" Then
                    Me.cmdFulfill.Visible = True
                    Me.cmdReject.Visible = True
                    Me.cmdReturn.Visible = True
                    '-------------------------------
                    Me.AllowEdits = True
                    Me.lblApproved.Visible = True
                    Me.ctc_approvedstartdt.Visible = True
                    Me.ctc_approvedenddt.Visible = True
                    Me.ctc_approvedgrade.Visible = True
                    Me.ctc_approvedjobtitle.Visible = True
                    Me.ctc_approvedsalary.Visible = True
                    Me.ctc_approvedctrtype.Visible = True
                    Me.ctc_approvedunt_id.Visible = True
                    Me.ctc_newsigndt.Visible = True
                    Me.ctc_approvedstartdt.BorderStyle = 1
                    Me.ctc_approvedenddt.BorderStyle = 1
                    Me.ctc_approvedgrade.BorderStyle = 1
                    Me.ctc_approvedjobtitle.BorderStyle = 1
                    Me.ctc_approvedsalary.BorderStyle = 1
                    Me.ctc_approvedctrtype.BorderStyle = 1
                    Me.ctc_approvedunt_id.BorderStyle = 1
                    Me.ctc_newsigndt.BorderStyle = 1
                    Me.ctc_date.Locked = True
                    Me.ctc_price.Locked = True
                    Me.ctc_minute.Locked = True
                    Me.ctc_newstartdt.Locked = True
                    Me.ctc_newenddt.Locked = True
                    Me.ctc_newgrade.Locked = True
                    Me.ctc_newjobtitle.Locked = True
                    Me.ctc_newsalary.Locked = True
                    Me.ctc_newctrtype.Locked = True
                    Me.ctc_newunt_id.Locked = True
                    Me.box_ctc_approvedgrade.Visible = False
                    Me.box_ctc_approvedctrtype.Visible = False
                    Me.box_ctc_approvedunt_id.Visible = False
                    If Me!ctc_newstartdt <= Me.subContract.Form!ctr_effenddt Then
                        Me.ctc_terminremarks.Visible = True
                        End If
                    End If
            End Select
    Case "Fulfilled", "Not Aprroved", "Cancelled"
        Select Case getVar("varMode")
            Case "approver-m", "approver-s"
                If getVar("varUnitArea") = "hr" Or getVar("varUnitArea") = "prj" Then Me.cmdReverse.Visible = True
            End Select
        'Me.subApproved.Visible = True
        Me.subAttachments.Visible = True
        Me.lblApproved.Visible = True
        Me.ctc_approvedstartdt.Visible = True
        Me.ctc_approvedenddt.Visible = True
        Me.ctc_approvedgrade.Visible = True
        Me.ctc_approvedjobtitle.Visible = True
        Me.ctc_approvedsalary.Visible = True
        Me.ctc_approvedctrtype.Visible = True
        Me.ctc_approvedunt_id.Visible = True
    End Select

If Not (Me.ctc_newunt_id >= 200000 And Me.ctc_newunt_id < 800000) Then Me.subPlan.Visible = False
If Me!ctc_newstartdt <= Me.subContract.Form!ctr_effenddt Then
    Me.txtWarning.Visible = True
    End If

Me.Visible = True
End Sub

Private Sub ctc_newstartdt_BeforeUpdate(Cancel As Integer)
If Me.subContract.Form.Recordset.RecordCount = 0 Then Exit Sub           'If there is no previous contract then exit

If Me.ctc_newstartdt <= Me.subContract.Form.ctr_startdt Then
    MsgBox "Contract start date is not valid. New contracts can only start after the old contract.", vbCritical
    Cancel = True
    Me.ctc_newstartdt.Undo
    Exit Sub
    End If

If Me.ctc_newstartdt > DateAdd("d", 1, Me.subContract.Form.ctr_effenddt) Then
    MsgBox "Contract start date is not valid. New contract has to start immediately after previous contract.", vbCritical
    Cancel = True
    Me.ctc_newstartdt.Undo
    Exit Sub
    End If
End Sub

Private Sub ctc_newstartdt_AfterUpdate()
Me.Dirty = False
Me!ctc_approvedstartdt = Me!ctc_newstartdt
If Me.subContract.Form.Recordset.RecordCount = 0 Then Exit Sub          'If there is no previous contract then exit
If Me!ctc_newstartdt <= Me.subContract.Form!ctr_effenddt Then
    Me!ctc_termindt = DateAdd("d", -1, Me!ctc_newstartdt)
    Me.txtWarning.Visible = True
    Else
    Me.txtWarning.Visible = False
    Me!ctc_termindt = Null
    Me.ctc_terminremarks = Null
    End If

AdjustPlan "CtrCase", Me!ctc_id
Me.subPlan.Requery
Me!ctc_prj_id = GetContractCaseProject(Me!ctc_id)
Me!ctc_price = CalculateCcPrice(Me!ctc_id)
End Sub

Private Sub ctc_newenddt_AfterUpdate()
Me.Dirty = False
Me!ctc_approvedenddt = Me!ctc_newenddt
AdjustPlan "CtrCase", Me!ctc_id
Me.subPlan.Requery
Me!ctc_prj_id = GetContractCaseProject(Me!ctc_id)
Me!ctc_price = CalculateCcPrice(Me!ctc_id)
End Sub

Private Sub ctc_newctrtype_AfterUpdate()
Me!ctc_approvedctrtype = Me!ctc_newctrtype
End Sub

Private Sub ctc_newgrade_AfterUpdate()
Me!ctc_approvedgrade = Me!ctc_newgrade
End Sub

Private Sub ctc_newjobtitle_AfterUpdate()
Me!ctc_approvedjobtitle = Me!ctc_newjobtitle
End Sub

Private Sub ctc_newsalary_AfterUpdate()
Me.Dirty = False
Me!ctc_approvedsalary = Me!ctc_newsalary
Me!ctc_price = CalculateCcPrice(Me!ctc_id)
End Sub

Private Sub ctc_newunt_id_AfterUpdate()
Me!ctc_approvedunt_id = Me!ctc_newunt_id
End Sub

Private Sub cmdCtrCase_Click()
Dim dbsPrice As Database
Dim strSql As String

Set dbsPrice = CurrentDb()
strSql = dbsPrice.QueryDefs("hr_ctrcaseplans_summary1").sql
dbsPrice.QueryDefs("hr_ctrcaseplans_summary1").sql = Left(strSql, InStr(strSql, "In (") + 3) & Me!ctc_id & ")))"
DoCmd.OpenReport "hr_ctrcase_detail", acViewReport
End Sub

Private Sub cmdMinute_Click()
On Error GoTo cmdMinute_Click_Err

Dim intresponse As Integer
Dim strDocName As String
Dim dbsMin As Database
Dim rstMin As Recordset
Dim lngMin As Long


Me.Dirty = False
If IsNull(Me!ctc_minute) Then
    MsgBox "Please enter minute number", vbCritical
    Exit Sub
    End If

Set dbsMin = CurrentDb()
Set rstMin = dbsMin.OpenRecordset("Select ccm_id From hr_ctrcaseminutes Where ccm_ctrcases = '" & Me!ctc_id & "'", dbOpenDynaset)
If Not rstMin.EOF Then
    lngMin = rstMin!ccm_id
    GoTo OpenMinute
    End If
    
'Make New Minute
intresponse = MsgBox("New minute will be created. Do you want to proceed?", 4, "Confirmation")
If intresponse <> 6 Then Exit Sub
lngMin = AddCtrApprovalDoc(Me!ctc_id, Me!ctc_minute)
Me.Refresh

'Open minute
OpenMinute:
strDocName = "hr_ctrcaseminutes"

DoCmd.OpenForm strDocName, acNormal, "", "", , acHidden, _
                lngMin & "~" & Me!ctc_minute & "~" & Me!ctc_type & "~" & Me!ctc_status & "~" & Me!ctc_price & "~" & Me!ctc_newunt_id & "~" & Me!ctc_prj_id

cmdMinute_Click_Exit:
    Exit Sub

cmdMinute_Click_Err:
    MsgBox Error$
    Resume cmdMinute_Click_Exit

End Sub

Private Sub cmdRelease_Click()
Me.Dirty = False
'If CCClearForRelease(Me!ctc_type) = False Then Exit Sub
ReleaseCC
End Sub

Private Sub cmdForward_Click()
ForwardCC
End Sub

Private Sub cmdReturn_Click()
ReturnCC
End Sub

Private Sub cmdReject_Click()
RejectCC
End Sub

Private Sub cmdFulfill_Click()
Me.Dirty = False
FulfillCC
End Sub

Private Sub cmdCancel_Click()
CancelCC
End Sub

Private Sub projects_list_AfterUpdate()
'Dim rstList As Recordset
'Set rstList = Me.subContractPlan.Form.RecordsetClone
'rstList.MoveFirst
'If rstList.EOF Then Exit Sub
'rstList.MoveFirst
'Do While Not rstList.EOF
'    rstList.Edit
'    rstList!cpn_hed_id = Me.projects_list
'    rstList.Update
'    rstList.MoveNext
'    Loop
'Me.subContractPlan.Requery
End Sub



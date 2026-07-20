VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcaseext_detail"
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
                    Me.ctc_approvedenddt.Visible = True
                    Me.ctc_approvedenddt.BorderStyle = 1
                    Me.ctc_terminremarks.Visible = True
                    Me.ctc_date.Locked = True
                    Me.ctc_price.Locked = True
                    Me.ctc_minute.Locked = True
                    Me.ctc_newenddt.Locked = True
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
        Me.ctc_approvedenddt.Visible = True
    End Select

Me.Visible = True
End Sub

Private Sub ctc_newenddt_BeforeUpdate(Cancel As Integer)
If Me.ctc_newenddt <= IIf(IsNull(Me.subContract.Form!ctr_termindt), Me.subContract.Form!ctr_enddt, Me.subContract.Form!ctr_termindt) Then
    MsgBox "The contract has to be extended beyond current completion date.", vbCritical
    Cancel = True
    Me.ctc_newenddt.Undo
    Exit Sub
    End If
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
DoCmd.OpenReport "hr_ctrcaseext_detail", acViewReport
End Sub

Private Sub cmdMinute_Click()
On Error GoTo cmdMinute_Click_Err

Dim intResponse As Integer
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
intResponse = MsgBox("New minute will be created. Do you want to proceed?", 4, "Confirmation")
If intResponse <> 6 Then Exit Sub
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
FulfillCCExt
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



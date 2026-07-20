VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_aud_revs_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From aud_revs Where rev_id = " & arrArgs(1)
arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft", "Under Revision"
        If Me!rev_status = "Draft" Then Me!rev_date = DateValue(GetNow())
        Select Case getVar("varMode")
            Case "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdRelease.Visible = True
                Me.cmdCancel.Visible = True
                Me.rev_reason.SetFocus
            Case "approver-m"
                If Me.rev_intunt_id = getVar("varUnitId") Then
                    AllowEditsAdvanced Me.Name, False, False
                    Me.cmdRelease.Visible = True
                    Me.cmdCancel.Visible = True
                    Me.rev_reason.SetFocus
                    End If
            Case "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.rev_reason.SetFocus
            Case "editor-m"
                If Me.rev_intunt_id = getVar("varUnitId") Then
                    AllowEditsAdvanced Me.Name, False, False
                    Me.rev_reason.SetFocus
                    End If
            End Select
    Case "In Process"
        Select Case getVar("varMode")
            Case "approver-m"
                If getVar("varUnitId") = 860000 Then
                    Me.cmdExecute.Visible = True
                    Me.cmdReturn.Visible = True
                    End If
                Me.cmdReport.Visible = True
            Case "approver-s", "editor-s"
                Me.cmdReport.Visible = True
            End Select
    Case "Fulfilled"
        Select Case getVar("varMode")
            Case "viewer-m", "approver-m"
                Me.subAttachments.Visible = True
            Case "approver-s", "editor-s"
                Me.subAttachments.Visible = True
            End Select
    End Select

'If getVar("varRoleDesigShort") = "MD" Then
'    Select Case arrArgs(0)
'        Case "In Process"
'            Me.cmdExecute.Visible = True
'            Me.cmdReturn.Visible = True
'            Me.cmdReport.Visible = True
'        Case "Fulfilled"
'            Me.subAttachments.Visible = True
'        End Select
'    End If


Select Case Me.rev_type
    Case 1, 3: Me.subComps.Visible = True
    Case 2:    Me.subData.Visible = True
    End Select

Me.Visible = True
End Sub

Private Sub cmdRelease_Click()
Dim intResponse  As Integer
Dim lngRevId As Long

If Nz(Me.rev_reason, "") = "" Then
    MsgBox "Please enter reason for data revision", vbCritical
    Exit Sub
    End If

intResponse = MsgBox("The data revision case will be released. Are you sure you want to release this request?", vbExclamation + vbYesNo, "Release Confirmation")
If intResponse <> 6 Then Exit Sub

If Me!rev_status = "Draft" Then Me!rev_releasedtg = GetNow()
Me!rev_status = "In Process"
MsgBox "The data revision case has been released.", vbInformation
lngRevId = Me!rev_id
DoCmd.Echo (False)
DoCmd.Close
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "In Process" & "~" & lngRevId
DoCmd.Echo (True)
End Sub

Private Sub cmdCancel_Click()
Dim dbsCancel As Database
Dim rstCancel As Recordset
Dim intResponse  As Integer

intResponse = MsgBox("The data revision case will be cancelled. Are you sure you want to cancel this request?", vbExclamation + vbYesNo, "Cancellation Confirmation")
If intResponse <> 6 Then Exit Sub

If Me.rev_status = "Draft" Then
    Set dbsCancel = CurrentDb()
    Set rstCancel = dbsCancel.OpenRecordset("Select rev_id From aud_revs Where rev_id = " & Me.rev_id, dbOpenDynaset)
    rstCancel.Delete
    Else
    Me!rev_closedtg = GetNow()
    Me!rev_status = "Cancelled"
    End If
MsgBox "The data revision case has been cancelled.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdReturn_Click()
Me!rev_status = "Under Revision"
MsgBox "The data revision case has been returned.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdExecute_Click()
Dim intResponse  As Integer
Dim booSuccess As Boolean
Dim strRevType As String

intResponse = MsgBox("The data revision case will be implemented and data will be reversed. Are you sure you want to execute this request?", vbExclamation + vbYesNo, "Execution Confirmation")
If intResponse <> 6 Then Exit Sub

strRevType = "Data Revision Case"
Select Case Me!rev_type
    Case 1, 3: ExecuteDRComps Me!rev_id
    Case 2:    ExecuteDRData Me!rev_id
    End Select
Me!rev_closedtg = GetNow()
Me!rev_status = "Fulfilled"

'Add attachment slots
CreateAttachmentSlot "rev", Me!rev_id, strRevType

MsgBox strRevType & " implemented successfully.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdReport_Click()
Dim strReport As String
On Error GoTo cmdReport_Click_Err


Me.Dirty = False
Select Case Me.rev_obj
    
    Case "Employee":        strReport = "emp"
    Case "Contract":        strReport = "ctr"
    Case "Contract Plan":   strReport = "cpl"
    Case "Attendance":      strReport = IIf(IsNumeric(Me.rev_objid), "att", "atd")
    Case "Salary Order":    strReport = "sor"
    Case "Allocation":      strReport = "alc"
    Case "Funding":         strReport = "fnd"
    Case "Purchase Case":   strReport = "pcs"
    Case "Commitment":      strReport = "cmt"
    Case "Payment":         strReport = "trn"
    Case "Account":         strReport = "hed"
    Case "Milestone Cost":  strReport = "mct"
    Case "Task":            strReport = "msn"
    End Select
strReport = "aud_revs" & Me.rev_type & "_" & strReport

DoCmd.OpenReport strReport, acViewReport, "", "", acNormal

cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub

VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_salreqs_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

'**** By default form allowedits = false and all fields locked = true
Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft"
        Me.lblTitle.Caption = "Salary Requisitions - Draft"
        Me.RecordSource = "hr_salreqs_u_draft"
        Me.cmdPrintSalReqs.Visible = True
        If getVar("varMode") = "approver-s" Then
            Me.AllowEdits = True
            Me.srq_remarks2.Locked = False
            Me.srq_remarks2.BorderStyle = 1
            Me.cmdRelease.Visible = True
            Me.cmdCancel.Visible = True
            Me.cmdSplit.Visible = True
            End If
    Case "In Process"
        Me.lblTitle.Caption = "Salary Requisitions - In Process"
        Me.RecordSource = "hr_salreqs_u_inprocess"
        Me.cmdPrintSalReqs.Visible = True
        If getVar("varMode") = "approver-s" Then
            Me.cmdCancel.Visible = True
            End If
    Case "Closed"
        Me.lblTitle.Caption = "Salary Requisitions - Closed"
        Me.RecordSource = "hr_salreqs_u_closed"
        Me.cmdReverse.Visible = True
    Case "Waiting"
        If getVar("varMode") = "approver-m" Then
            Me.lblTitle.Caption = "Salary Requisitions - Awaiting order"
            Me.RecordSource = "hr_salreqs_u_waiting"
            Me.cmdGenOrder.Visible = True
            End If
    Case Else
        MsgBox "Access denied."
        Exit Sub
    End Select
Me.Visible = True
End Sub

Private Sub cmdRelease_Click()
Dim rstRel  As Recordset
Dim strObjections As String

Me.Dirty = False
If Me!srq_parent > 0 Then
    MsgBox "This requisition cannot be released directly. Please relaese the parent requisition.", vbCritical
    Exit Sub
    End If
strObjections = AnyObjectionsOnExp(Me!srq_effhed_id, Me!srq_hed_id, Me!srq_sudohed, Me!srq_salary, Me!srq_salary)
If strObjections <> "" Then
    MsgBox strObjections, vbCritical
    Exit Sub
    End If

AddSalaryOrderGroup Me!srq_id
ReleaseSalaryReqGroup Me!srq_id
Me.Requery
SendKeys "+{TAB}"
End Sub

Private Sub cmdCancel_Click()
Dim rstCan  As Recordset

Me.Dirty = False
If Me!srq_parent > 0 Then
    MsgBox "This requisition cannot be cancelled directly. Please cancel the parent requisition.", vbCritical
    Exit Sub
    End If

CancelSalaryReqGroup Me!srq_id
Me.Requery
SendKeys "+{TAB}"
End Sub

Private Sub cmdGenOrder_Click()
Me.Dirty = False
AddSalaryOrderGroup Me!srq_id
Me.Requery
SendKeys "+{TAB}"
End Sub

Private Sub cmdSplit_Click()
DoCmd.OpenForm "hr_salreq_split", acNormal, , , , acHidden
End Sub

Private Sub cmdReverse_Click()
Dim intresponse  As Integer
Dim dbsRev As Database
Dim rstRev As Recordset
Dim lngSoId As Long
Dim lngRevId As Long

Set dbsRev = CurrentDb()
Set rstRev = dbsRev.OpenRecordset("Select sor_id From fin_salorders Where sor_srq_id = " & Me!srq_id, dbOpenSnapshot)
lngSoId = rstRev!sor_id

intresponse = MsgBox("A Data Revision Request for Salary Requisition " & Me!srq_id & " and associated Salary Order " & lngSoId & ", will be generated. " & _
                     "Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Salary Order", lngSoId, Me!srq_unt_id, 1)
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_sal_reqs_u"
End Sub


Private Sub cmdPrintSalReqs_Click()
On Error GoTo cmdPrintSalReqs_Click_Err

Me.Dirty = False
Select Case Me.lblTitle.Caption
    Case "Salary Requisitions - Draft"
        DoCmd.OpenReport "hr_salreqs_u_doc", acViewReport, "", "", acHidden, "Draft"
    Case "Salary Requisitions - In Process"
        DoCmd.OpenReport "hr_salreqs_u_doc", acViewReport, "", "", acHidden, "In Process"
    Case "Salary Requisitions - Closed"
        DoCmd.OpenReport "hr_salreqs_u_doc", acViewReport, "", "", acHidden, "Closed"
    End Select

cmdPrintSalReqs_Click_Exit:
    Exit Sub

cmdPrintSalReqs_Click_Err:
    MsgBox Error$
    Resume cmdPrintSalReqs_Click_Exit

End Sub


Private Sub srq_salary_DblClick(Cancel As Integer)
SalaryTest Me!srq_emp_id, Me!srq_month
DoCmd.OpenForm "salary_matrix_test", acNormal, "", "", , acNormal
End Sub

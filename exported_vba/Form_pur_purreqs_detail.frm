VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreqs_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Detail_DblClick(Cancel As Integer)
'MsgBox Me.subPurReqItems.Form.Recordset.RecordCount
MsgBox Me.subPurReqItems.Form.price_grand
End Sub

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "DataEntry"
        Me.prq_id.Visible = False
        Me.subPurReqItems.Form.pri_appqty.Width = 0
        Me.subPurReqItems.Form.pri_appqty.Visible = False
        Me.subPurReqItems.Form.lbl_approved.Width = 0
        Me.subPurReqItems.Form.balance_qty.Width = 0
        Me.subPurReqItems.Form.balance_qty.Visible = False
        Me.subPurReqItems.Form.lbl_balance.Width = 0
        Select Case getVar("varMode")
            Case "approver-s"
                AllowEditsAdvanced Me.Name, True, True
                Me.cmdCancel.Visible = True
                Me.cmdRelease.Visible = True
            Case "editor-s"
                AllowEditsAdvanced Me.Name, True, True
                Me.cmdCancel.Visible = True
            End Select
    Case "Draft", "Under Revision"
        Me.RecordSource = "Select * From pur_purreqs Where prq_id = " & arrArgs(1)
        Me.subPurReqItems.Form.pri_appqty.Width = 0
        Me.subPurReqItems.Form.pri_appqty.Visible = False
        Me.subPurReqItems.Form.lbl_approved.Width = 0
        Me.subPurReqItems.Form.balance_qty.Width = 0
        Me.subPurReqItems.Form.balance_qty.Visible = False
        Me.subPurReqItems.Form.lbl_balance.Width = 0
        If Me!prq_status = "Draft" Then Me.lblDraft.Visible = True
        Select Case getVar("varMode")
            Case "approver-s"
                AllowEditsAdvanced Me.Name, True, True
                Me.cmdCancel.Visible = True
                Me.cmdRelease.Visible = True
            Case "editor-s"
                AllowEditsAdvanced Me.Name, True, True
                Me.cmdCancel.Visible = True
            End Select
    Case "Under Scrutiny"
        Me.RecordSource = "Select * From pur_purreqs Where prq_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "editor-s"
                Me.cmdCreateCase.Visible = True
                Me.cmdCancel.Visible = True
            Case "approver-m"
                Me.cmdForward.Visible = True
                Me.cmdReturn.Visible = True
            End Select
    Case "Under Approval"
        Me.RecordSource = "Select * From pur_purreqs Where prq_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "approver-m"
                Me.cmdApprove.Visible = True
                Me.cmdReject.Visible = True
                Me.cmdReturn.Visible = True
            End Select
    Case "Approved"
        Me.RecordSource = "Select * From pur_purreqs Where prq_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "approver-s"
                Me.cmdCancel.Visible = True
                Me.cmdCreateCase.Visible = True
            Case "editor-s"
                Me.cmdCreateCase.Visible = True
            End Select
    Case Else
        Me.RecordSource = "Select * From pur_purreqs Where prq_id = " & arrArgs(1)
    End Select
Me.Visible = True

End Sub

Private Sub cmdRelease_Click()
Me!prq_status = "Under Scrutiny"
MsgBox "The purchase requisition has been released", vbInformation
DoCmd.Close
End Sub

Private Sub cmdForward_Click()
Me!prq_status = "Under Approval"
DoCmd.Close
End Sub

Private Sub cmdReturn_Click()
Me!prq_status = "Under Revision"
DoCmd.Close
End Sub

Private Sub cmdApprove_Click()
Dim dbsApprove As Database
Dim qdfApprove As QueryDef

Me.Dirty = False
Set dbsApprove = CurrentDb()
dbsApprove.Execute "Delete From pur_purreqitems_appr_temp"
Set qdfApprove = dbsApprove.QueryDefs("pur_purreqitems_appr_tempadder")
qdfApprove.Parameters("PurReqId") = Me!prq_id
qdfApprove.Execute
DoCmd.OpenForm "pur_purreqitems_appr_tempchoose", , , , , , Me.prq_effhed_id
End Sub

Private Sub cmdReject_Click()
Me!prq_status = "Rejected"
DoCmd.Close
End Sub


Private Sub cmdCancel_Click()
Dim dbsCancel As Database
Dim rstCancel As Recordset
Dim intDecision As Integer

'***For draft requisition only update status
If Me!prq_status = "Draft" Then
    intDecision = MsgBox("The requisition will be cancelled. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
    If intDecision = 7 Then Exit Sub
    Me!prq_status = "Cancelled"
    MsgBox "The purchase requisition has been cancelled.", vbInformation
    Exit Sub
    End If

'***Check for any draft or open purchase case for this requisition
Set dbsCancel = CurrentDb()
Set rstCancel = dbsCancel.OpenRecordset("Select pcs_id From pur_purcases Where StringHasNumber ([pcs_purreqs]," & Me!prq_id & ") And " & _
                                        "pcs_status Not In ('Cancelled','Fulfilled','Partially Fulfilled');", dbOpenSnapshot)
If rstCancel.EOF = False Then
    MsgBox "A draft or open purchase case exists for this requisition. It cannot be cancelled.", vbCritical
    Exit Sub
    End If
rstCancel.Close
'***Execution
intDecision = MsgBox("The requisition will be cancelled. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intDecision = 7 Then Exit Sub
Set rstCancel = dbsCancel.OpenRecordset("Select prt_id From ((pur_purreqs " & _
                                        "Inner Join pur_purreqitems On pur_purreqs.prq_id = pur_purreqitems.pri_prq_id) " & _
                                        "Inner Join pur_purreceiptitems On pur_purreqitems.pri_id = pur_purreceiptitems.pti_pri_id) " & _
                                        "Inner Join pur_purreceipts On pur_purreceiptitems.pti_prt_id = pur_purreceipts.prt_id " & _
                                        "Where prt_status = 'Finalized';", dbOpenSnapshot)
If rstCancel.EOF = True Then
    Me!prq_status = "Cancelled"
    MsgBox "The purchase requisition has been cancelled.", vbInformation
    Else
    Me!prq_status = "Partially Fulfilled"
    MsgBox "The remaining purchase requisition has been cancelled.", vbInformation
    End If

DoCmd.Close
End Sub

Private Sub cmdCreateCase_Click()
Dim dbsPurCase As Database
Dim qdfPurCase As QueryDef

Me.Dirty = False
Set dbsPurCase = CurrentDb()
dbsPurCase.Execute "Delete From pur_purreqitems_temp"
Set qdfPurCase = dbsPurCase.QueryDefs("pur_purreqitems_tempadder")
qdfPurCase.Parameters("PurReqId") = Me!prq_id
qdfPurCase.Parameters("UnitID") = Me!prq_unt_id
qdfPurCase.Parameters("HeadId") = Me!prq_hed_id
qdfPurCase.Parameters("EffHeadId") = Me!prq_appeffhed_id
qdfPurCase.Execute
DoCmd.OpenForm "pur_purreqitems_tempchoose"
End Sub

Private Sub cmdReq_Click()
On Error GoTo cmdReq_Click_Err

Me.Dirty = False
Forms!vars.Parameter1 = Me!prq_id
DoCmd.OpenReport "pur_purreqs", acViewReport, "", "", acNormal

cmdReq_Click_Exit:
    Exit Sub

cmdReq_Click_Err:
    MsgBox Error$
    Resume cmdReq_Click_Exit

End Sub



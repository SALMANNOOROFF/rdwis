VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreceipts_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "DataEntry"
        Select Case getVar("varMode")
            Case "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
                Me.cmdFinalize.Visible = True
            Case "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
            End Select
    Case "Draft"
        Me.RecordSource = "Select * From pur_purreceipts Where prt_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
                Me.cmdFinalize.Visible = True
            Case "editor-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
            End Select
    Case "Finalized"
        Me.RecordSource = "Select * From pur_purreceipts Where prt_id = " & arrArgs(1)
        Me.cmdAcceptance.Visible = True
        Select Case getVar("varMode")
            Case "approver-s"
            Case "editor-s"
            End Select
    Case Else
        Me.RecordSource = "Select * From pur_purreceipts Where prt_id = " & arrArgs(1)
    End Select
Me.Visible = True

End Sub

Private Sub cmdFinalize_Click()
Dim wksFin As Workspace
Dim dbsFin As Database
Dim rstFin As Recordset
Dim rstFin2 As Recordset
Dim rstChg As Recordset
Dim rstChg2 As Recordset
Dim strMsg As String
Dim intCount As Integer
Dim intType As Integer
Dim lngIasId As Long
Dim strArgs As String
Dim strCommit As String
On Error GoTo Error_Part

Me.Dirty = False
If IsNull(Me.prt_date) Then
    MsgBox "Please enter receipt date.", vbCritical
    Exit Sub
    End If
If Me.prt_date > GetNow() Then
    MsgBox "Future date is not allowed.", vbCritical
    Exit Sub
    End If

Set dbsFin = CurrentDb()
Set wksFin = DBEngine.Workspaces(0)
wksFin.BeginTrans
strCommit = "Begun"

'***Add to case items fulfilment
Set rstFin = Me.subRecItems.Form.RecordsetClone
rstFin.MoveFirst
Do While Not rstFin.EOF
    Set rstChg = dbsFin.OpenRecordset("Select pci_fulfilment From pur_purcaseitems Where pci_id = " & rstFin!pti_pci_id)
    rstChg.Edit
    rstChg!pci_fulfilment = Nz(rstChg!pci_fulfilment, 0) + rstFin!pti_qty
    rstChg.Update
    intCount = intCount + 1
    rstFin.MoveNext
    rstChg.Close
    Loop
strMsg = Plr(intCount, "item") & " received."

'***Add to inventory/assets if not service
intCount = 0
rstFin.MoveFirst
Set rstChg = dbsFin.OpenRecordset("Select * From ina_invats", dbOpenDynaset, dbSeeChanges)
Set rstChg2 = dbsFin.OpenRecordset("Select * From ina_invatcomps")
Do While Not rstFin.EOF
    
    Set rstFin2 = dbsFin.OpenRecordset("Select * FROM pur_purcaseitems Where pci_id = " & rstFin!pti_pci_id, dbOpenSnapshot)
    If rstFin2!pci_type = 3 Then GoTo Loop_End
    
    rstChg.AddNew
    rstChg!ias_pcs_id = Me.prt_pcs_id
    rstChg!ias_unt_id = Me.prt_unt_id
    If Nz(Me.prt_prj_id, "") <> "" Then rstChg!ias_prj_id = Me.prt_prj_id
    rstChg!ias_effhed_id = Me.subPurCase.Form!pcs_effhed_id
    rstChg!ias_chargedate = Me.prt_date
    rstChg!ias_pci_id = rstFin2!pci_id
    rstChg!ias_desc = rstFin2!pci_desc
    rstChg!ias_qty = rstFin2!pci_qty
    rstChg!ias_qtyunit = rstFin2!pci_qtyunit
    rstChg!ias_price = rstFin2!pci_price
    rstChg!ias_type = rstFin2!pci_type
    rstChg!ias_type2 = rstFin2!pci_type2
    rstChg!ias_subtype = rstFin2!pci_subtype
    rstChg!ias_dtg = GetNow()
    rstChg.Update
    rstChg.Bookmark = rstChg.LastModified
    lngIasId = rstChg!ias_id
    
    rstChg2.AddNew
    rstChg2!iac_ias_id = lngIasId
    rstChg2!iac_qty = rstFin2!pci_qty
    rstChg2!iac_qtyunit = rstFin2!pci_qtyunit
    rstChg2!iac_status = "Held"
    rstChg2!iac_dtg = GetNow()
    rstChg2.Update
    
    intCount = intCount + 1
Loop_End:
    rstFin2.Close
    rstFin.MoveNext
    Loop
strMsg = strMsg & vbCrLf & Plr(intCount, "item") & " added to inventory/assets."
rstChg.Close
rstChg2.Close
rstFin.Close

'***Update receipt status
Set rstChg = dbsFin.OpenRecordset("Select prt_status From pur_purreceipts Where prt_id = " & Me!prt_id)
rstChg.Edit
rstChg!prt_status = "Finalized"
rstChg.Update
Me.Refresh

'***Update case status if required
Set rstFin = dbsFin.OpenRecordset("Select pci_pcs_id From pur_purcaseitems Where pci_pcs_id = " & Me!prt_pcs_id & " And Nz([pci_fulfilment],0)< [pci_qty]", dbOpenSnapshot)
If rstFin.EOF Then
    Set rstChg = dbsFin.OpenRecordset("Select pcs_status, pcs_closedtg From pur_purcases Where pcs_id = " & Me!prt_pcs_id)
    rstChg.Edit
    rstChg!pcs_status = "Fulfilled"
    rstChg!pcs_closedtg = GetNow()
    rstChg.Update
    strMsg = strMsg & vbCrLf & "Purcahase case " & Me!prt_pcs_id & " fulfilled."
    End If
   
wksFin.CommitTrans
strCommit = "Committed"
 
MsgBox strMsg, vbInformation
Application.Echo False
strArgs = Me!prt_status & "~" & Me!prt_id
DoCmd.Close
DoCmd.OpenForm "pur_purreceipts_detail", acNormal, , , , acHidden, strArgs
Application.Echo True
Exit Sub

Error_Part:
If strCommit = "Begun" Then
    wksFin.Rollback
    MsgBox "Purcase Receipt cannot be finalized." & vbCrLf & Err.Description, vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub

Private Sub cmdCancel_Click()
Dim intDecision As Integer
intDecision = MsgBox("The requisition will be cancelled. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intDecision = 7 Then Exit Sub
Me!prt_status = "Cancelled"
MsgBox "Item receipt has been cancelled.", vbInformation
DoCmd.Close
End Sub

Private Sub cmdAcceptance_Click()
On Error GoTo cmdAcceptance_Click_Err

Me.Dirty = False
CountCategories
DoCmd.OpenReport "pur_purreceipts_detail", acViewReport, "", "", acNormal

cmdAcceptance_Click_Exit:
    Exit Sub

cmdAcceptance_Click_Err:
    MsgBox Error$
    Resume cmdAcceptance_Click_Exit

End Sub

Private Sub CountCategories()
Dim rstCount As Recordset
Dim intMat As Integer
Dim intSvc As Integer

Set rstCount = Me.subRecItems.Form.RecordsetClone
rstCount.MoveFirst
Do While Not rstCount.EOF
    If rstCount!pci_category = 1 Then
        intMat = intMat + 1
        Else
        intSvc = intSvc + 1
        End If
    rstCount.MoveNext
    Loop

Me.txtMat = intMat
Me.txtSvc = intSvc
End Sub



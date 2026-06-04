Attribute VB_Name = "PurchaseCase"
Option Compare Database
Option Explicit

Public Sub ReleasePC()
Dim frmRel As Form
Dim dbsRel As Database
Dim rstSource As Recordset
Dim rstRel As Recordset
Dim qdfRel As QueryDef
Dim intResponse  As Integer

intResponse = MsgBox("The case will be forwarded to Finance Department. Are you sure you want to release this case?", 4, "Release Confirmation")
If intResponse <> 6 Then Exit Sub

Set frmRel = Screen.ActiveForm
Set dbsRel = CurrentDb()
Set rstRel = frmRel.subPurCaseItems.Form.RecordsetClone
Do While Not rstRel.EOF
    'Update req item fulfilment
    Set qdfRel = dbsRel.QueryDefs("pur_purreqitems_fulfil")
    qdfRel.Parameters("ItemId") = rstRel!pci_pri_id
    qdfRel.Parameters("ChangeQty") = rstRel!pci_qty
    qdfRel.Execute
    'Check requisition fulfilled flag if required
    Set qdfRel = dbsRel.QueryDefs("pur_purreqs_fulfil")
    qdfRel.Parameters("ReqItemId") = rstRel!pci_pri_id
    qdfRel.Execute
    rstRel.MoveNext
    Loop

'Add subheads ratios
Set qdfRel = dbsRel.QueryDefs("pur_purcaseitems_byshd")
qdfRel.Parameters(0) = frmRel!pcs_id
Set rstSource = qdfRel.OpenRecordset(dbOpenSnapshot)
Set rstRel = dbsRel.OpenRecordset("Select * From pur_purcases_shd Where pcd_pcs_id = " & frmRel!pcs_id, dbOpenDynaset)
Do While Not rstRel.EOF
    rstRel.Delete
    rstRel.MoveNext
    Loop
Do While Not rstSource.EOF
    With rstRel
        .AddNew
        !pcd_pcs_id = frmRel!pcs_id
        !pcd_type = frmRel!pcs_type
        !pcd_subhead = rstSource!pci_subhead
        !pcd_ratio = IIf(frmRel!pcs_transtype = 1, rstSource!ratio1, rstSource!ratio2)
        .Update
        End With
    rstSource.MoveNext
    Loop

'Change purchase case status
frmRel!pcs_status = "Under Scrutiny"
If frmRel!pcs_status = "Draft" Then frmRel!pcs_releasedtg = GetNow()
MsgBox "The purchase case has been released", vbInformation
DoCmd.Close

End Sub

Public Sub ApprovePC()
Dim wksPcase As Workspace
Dim dbsPcase As Database
Dim qdfPcase As QueryDef
Dim rstPcase As Recordset
'Dim rstSource As Recordset
Dim frmPCase As Form
Dim strCommit As String
Dim lngCmtId As Long
Dim n As Integer
On Error GoTo Error_Handler

Set frmPCase = Screen.ActiveForm
Set dbsPcase = CurrentDb()
Set wksPcase = DBEngine.Workspaces(0)
wksPcase.BeginTrans
strCommit = "Begun"
If Nz(frmPCase!pcs_hed_id, "") = "" Then GoTo Add_Commitment

''Add subheads ratios
'Set qdfPcase = dbsPcase.QueryDefs("pur_purcaseitems_byshd")
'qdfPcase.Parameters(0) = frmPCase!pcs_id
'Set rstSource = qdfPcase.OpenRecordset(dbOpenSnapshot)
'Set rstPcase = dbsPcase.OpenRecordset("pur_purcases_shd", dbOpenDynaset)
'Do While Not rstSource.EOF
'    With rstPcase
'        .AddNew
'        !pcd_pcs_id = frmPCase!pcs_id
'        !pcd_type = frmPCase!pcs_type
'        !pcd_subhead = rstSource!pci_subhead
'        !pcd_ratio = IIf(frmPCase!pcs_transtype = 1, rstSource!ratio1, rstSource!ratio2)
'        .Update
'        End With
'    rstSource.MoveNext
'    Loop
    
'Add commitment
Add_Commitment:
Set rstPcase = dbsPcase.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstPcase
    .AddNew
    !cmt_docid = frmPCase!pcs_id
    !cmt_type = frmPCase!pcs_type
    !cmt_date = frmPCase!pcs_date
    !cmt_amount = -1 * IIf(frmPCase!pcs_transtype = 1, frmPCase!pcs_midprice, frmPCase!pcs_price)
    !cmt_status = "Awaited"
    !cmt_effhed_id = frmPCase.pcs_effhed_id
    !cmt_effunt_id = frmPCase.pcs_effunt_id
    If Not IsNull(frmPCase.pcs_hed_id) Then !cmt_hed_id = frmPCase.pcs_hed_id
    !cmt_unt_id = frmPCase.pcs_unt_id
    If Not IsNull(frmPCase.pcs_sudohed) Then !cmt_sudohed = frmPCase.pcs_sudohed
    .Update
    .Bookmark = .LastModified
    End With
lngCmtId = rstPcase!cmt_id
rstPcase.Close

'Add attachment slots

Select Case frmPCase!pcs_type
    Case "Ps"
        CreateAttachmentSlot "pcs", frmPCase!pcs_id, "Minute"
        CreateAttachmentSlot "pcs", frmPCase!pcs_id, "Market Research Report"
        
    Case "Rb"
        CreateAttachmentSlot "pcs", frmPCase!pcs_id, "Minute"
        CreateAttachmentSlot "pcs", frmPCase!pcs_id, "Financial Status"
    Case "Pt"
        CreateAttachmentSlot "pcs", frmPCase!pcs_id, "Form"
    End Select

'Change purchase case status
frmPCase!pcs_status = "Approved"
frmPCase!pcs_approvedtg = GetNow()

wksPcase.CommitTrans
strCommit = "Committed"

MsgBox "The purchase case has been approved", vbInformation
DoCmd.Close
Exit Sub

Error_Handler:
If strCommit = "Begun" Then
    wksPcase.Rollback
    MsgBox "Purchase Case cannot be marked as 'Approved'.", vbCritical
    Else
    MsgBox Err.Description, vbCritical
    End If
End Sub

Public Sub CancelPC()
Dim frmPC As Form
Dim dbsPC As Database
Dim rstPC As Recordset
Dim qdfPC1 As QueryDef
Dim qdfPC2 As QueryDef
Dim intResponse  As Integer

intResponse = MsgBox("This purchase case will be cancelled. Do you want to proceed?", vbExclamation + vbYesNo, "Cancellation Confirmation")
If intResponse <> 6 Then Exit Sub

Set frmPC = Screen.ActiveForm
'***For draft case only update status.
If frmPC!pcs_status = "Draft" Then
    frmPC!pcs_status = "Cancelled"
    MsgBox "The purchase case has been cancelled.", vbInformation
    Exit Sub
    End If

'***Check for any draft receipts for this case
Set dbsPC = CurrentDb()
Set rstPC = dbsPC.OpenRecordset("Select prt_id From pur_purreceipts Where prt_pcs_id = " & frmPC!pcs_id & " And " & _
                                        "prt_status Not In ('Cancelled','Finalized');", dbOpenSnapshot)
If rstPC.EOF = False Then
    MsgBox "A draft purchase receipt exists for this case. It cannot be cancelled.", vbCritical
    Exit Sub
    End If
rstPC.Close

'***Execution - Related data
'Update requisitions data
Set rstPC = frmPC.subPurCaseItems.Form.RecordsetClone
Set dbsPC = CurrentDb()
Set qdfPC1 = dbsPC.QueryDefs("pur_purreqitems_fulfil")
Set qdfPC2 = dbsPC.QueryDefs("pur_purreqs_fulfilreverse")
Do While Not rstPC.EOF
    'Subtract from purchase item fulfilment
    qdfPC1.Parameters("ItemId") = rstPC!pci_pri_id
    qdfPC1.Parameters("ChangeQty") = -1 * (rstPC!pci_qty - rstPC!pci_fulfilment)
    qdfPC1.Execute
    'Uncheck requisition fulfilled flag
    qdfPC2.Parameters("ReqItemId") = rstPC!pci_pri_id
    qdfPC2.Execute
    rstPC.MoveNext
    Loop
'***Execution - Current case
Set rstPC = dbsPC.OpenRecordset("Select prt_id From ((pur_purcases " & _
                                        "Inner Join pur_purcaseitems On pur_purcases.pcs_id = pur_purcaseitems.pci_pcs_id) " & _
                                        "Inner Join pur_purreceiptitems On pur_purcaseitems.pci_id = pur_purreceiptitems.pti_pci_id) " & _
                                        "Inner Join pur_purreceipts On pur_purreceiptitems.pti_prt_id = pur_purreceipts.prt_id " & _
                                        "Where pcs_id = " & frmPC.pcs_id & " And prt_status = 'Finalized';", dbOpenSnapshot)
If rstPC.EOF = True Then
    frmPC!pcs_status = "Cancelled"
    frmPC!pcs_closedtg = GetNow()
    MsgBox "The purchase case has been cancelled.", vbInformation
    Else
    frmPC!pcs_status = "Partially Fulfilled"
    frmPC!pcs_closedtg = GetNow()
    MsgBox "The remaining case requisition has been cancelled.", vbInformation
    End If

DoCmd.Close
End Sub

Public Sub ForwardPC()
Screen.ActiveForm!pcs_status = "Under Approval"
DoCmd.Close
End Sub

Public Sub ReturnPC()
Screen.ActiveForm!pcs_status = "Under Revision"
DoCmd.Close
End Sub

Public Sub RejectPC()
Screen.ActiveForm!pcs_status = "Not Approved"
Screen.ActiveForm!pcs_closedtg = GetNow()
DoCmd.Close
End Sub

Public Sub ReceivePCItems()
Dim frmRec As Form
Dim dbsRec As Database
Dim rstRec As Recordset
Dim qdfRec As QueryDef

Set frmRec = Screen.ActiveForm
frmRec.Dirty = False
Set dbsRec = CurrentDb()
Set rstRec = dbsRec.OpenRecordset("Select prt_id From pur_purreceipts Where prt_pcs_id = " & frmRec!pcs_id & "And prt_status = 'Draft'", dbOpenSnapshot)
If Not rstRec.EOF Then
    MsgBox "Items cannot be received till there are draft recepits for this case. Please finalize or cancel all draft receipts for this case.", vbCritical
    Exit Sub
    End If

dbsRec.Execute "Delete From pur_purcaseitems_temp"
Set qdfRec = dbsRec.QueryDefs("pur_purcaseitems_tempadder")
qdfRec.Parameters("PurCaseId") = frmRec!pcs_id
qdfRec.Parameters("UnitId") = frmRec!pcs_intunt_id
qdfRec.Parameters("HeadId") = frmRec!pcs_hed_id
qdfRec.Execute
DoCmd.OpenForm "pur_purcaseitems_tempchoose"

End Sub

Public Function TaxRate(ItemType As Integer) As Single
Select Case ItemType
    Case 3: TaxRate = 0.13
    Case Else: TaxRate = 0.18
    End Select
End Function

'To be Deleted xxxxxxxxxxxxxxxxxx
Public Function GetTaxes(doctype As String, docid As Long) As Variant()
Dim strQryName As String
Dim dbsType As Database
Dim qdfType As QueryDef
Dim rstType As Recordset
Dim arrType(2) As Variant

Select Case doctype
    Case "pcs": strQryName = "pur_purcase_taxes"
    Case "qte": strQryName = "pur_quote_taxes"
    End Select

Set dbsType = CurrentDb()
Set qdfType = dbsType.QueryDefs(strQryName)
qdfType.Parameters(0) = docid
Set rstType = qdfType.OpenRecordset(dbOpenSnapshot)

arrType(0) = rstType!price
arrType(1) = Round(rstType!sst, 0)
arrType(2) = Round(rstType!GST, 0)

GetTaxes = arrType()
End Function

'To be Deleted xxxxxxxxxxxxxxxxxx
Public Function PcsPriceByCat(PcsIds As String) As Variant
Dim dbsPcsExp As Database
Dim rstPcsExp As Recordset
Dim arrInput() As String
Dim arrOutput(1 To 6)
Dim Item As Variant
Dim lngAmount As Long

arrInput() = Split(PcsIds, ",")
For Each Item In arrInput
    Set dbsPcsExp = CurrentDb()
    Set rstPcsExp = dbsPcsExp.OpenRecordset("SELECT pci_pcs_id, pci_category, Sum([pci_price]*[pci_qty]) AS total_price" & _
                                            " FROM pur_purcaseitems Where pci_pcs_id = " & Item & _
                                            " GROUP BY pci_pcs_id, pci_category", dbOpenSnapshot)
    Do While Not rstPcsExp.EOF
        Select Case rstPcsExp!pci_category
            Case 1: arrOutput(4) = Nz(arrOutput(4), 0) + rstPcsExp!total_price
            Case 2: arrOutput(5) = Nz(arrOutput(5), 0) + rstPcsExp!total_price
            Case 3: arrOutput(6) = Nz(arrOutput(6), 0) + rstPcsExp!total_price
            End Select
        rstPcsExp.MoveNext
        Loop
    Next
PcsPriceByCat = arrOutput
rstPcsExp.Close
Set rstPcsExp = Nothing
End Function

Public Sub EffHead__AfterUpdate()
Dim frm As Form
Dim dbsType As Database
Dim rstType As Recordset
Dim intTransType As Integer

Set frm = Screen.ActiveForm
Set dbsType = CurrentDb
Set rstType = dbsType.OpenRecordset("Select hed_transtype From cen_heads Where hed_id = " & frm!pcs_effhed_id, dbOpenSnapshot)
frm!pcs_transtype = rstType!hed_transtype
frm!pcs_effunt_id = UnitFromHead(frm!pcs_effhed_id)
frm!pcs_hed_id = IIf(frm!pcs_effhed_id >= 200000, frm!pcs_effhed_id, Null)
If frm!pcs_hed_id.ListIndex = -1 Then frm!pcs_hed_id = Null
Head__AfterUpdate

End Sub

Public Sub Head__AfterUpdate()
Dim frm2 As Form

Set frm2 = Screen.ActiveForm
frm2!pcs_unt_id = UnitFromHead(frm2!pcs_hed_id)
Select Case True
    Case Nz(frm2.pcs_hed_id, "") = "" And frm2!pcs_effhed_id >= 200000
        frm2!pcs_sudohed = "CHRF"
        frm2!pcs_noloan = 1
    Case Else
        frm2.pcs_sudohed = Null
        frm2!pcs_noloan = 0
    End Select

End Sub

Public Function RefreshDataEntryForm() As Boolean
Dim frm3 As Form
Dim strPcsId As String
Dim strFormName As String

Set frm3 = Screen.ActiveForm
strFormName = frm3.Name
strPcsId = frm3!pcs_id
If strPcsId = "" Then
    DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Draft"
    DoCmd.Close acForm, strFormName
    MsgBox "New purchase case has been created. Please open again.", vbExclamation
    RefreshDataEntryForm = False
    Else
    Application.Echo False
    DoCmd.Close acForm, strFormName
    DoCmd.OpenForm strFormName, acNormal, , , , acHidden, "Draft~" & strPcsId
    Application.Echo True
    RefreshDataEntryForm = True
    End If
End Function

Public Function ClearForRelease(CaseType As String) As Boolean
Dim frm As Form
Dim dbsType As Database
Dim rstCheck  As Recordset
Dim booClear As Boolean

booClear = True
Set frm = Screen.ActiveForm

If frm!pcs_frm_id = 0 Then
    MsgBox "Firm Name is required"
    booClear = False
    GoTo The_End
    End If

If IsNull(frm!pcs_effhed_id) Then
    MsgBox "Please enter 'Head'. Case not released.", vbCritical
    booClear = False
    GoTo The_End
    End If

If IsNull(frm!pcs_minute) Or frm!pcs_minute = "" Then
    MsgBox "Minute number is required. Case not released.", vbCritical
    booClear = False
    GoTo The_End
    End If

If Not IsNull(frm!pcs_hed_id) And Nz(frm!pcs_sudohed, "") = "" Then
'    MsgBox "CHRF tag incorrect. Please contact IS. Case not released.", vbCritical
'    booClear = False
'    GoTo The_End
    End If

Select Case CaseType
    Case "Ps"
        If frm.subQuotes.Form.Recordset.RecordCount = 0 Then
            MsgBox "Atleast one quote is required. Case not released.", vbCritical
            booClear = False
            GoTo The_End
            End If
        
        Set rstCheck = frm.subQuotes.Form.RecordsetClone
        Do While Not rstCheck.EOF
            If rstCheck!qte_num Like "<*>" Then booClear = False
            If rstCheck!qte_frm_id Like "<*>" Then booClear = False
            If rstCheck!qte_price = 0 Then booClear = False
            rstCheck.MoveNext
            Loop
        If booClear = False Then
            MsgBox "References, firm names and prices required in all quotes. Case not released."
            GoTo The_End
            End If
    End Select

The_End:
ClearForRelease = booClear
End Function

Public Function AddPcsApprovalDoc(PcsIds As String, minute As Integer) As Long
Dim dbsAddApp As Database
Dim rstAddApp As Recordset
Dim lngPcmId As Long

'Add record in Approval docs list
Set dbsAddApp = CurrentDb()
Set rstAddApp = dbsAddApp.OpenRecordset("pur_purcaseminutes", dbOpenDynaset, dbSeeChanges)
With rstAddApp
    .AddNew
    !pcm_purcases = PcsIds
    !pcm_minute = minute
    .Update
    .Bookmark = .LastModified
    End With
lngPcmId = rstAddApp!pcm_id
rstAddApp.Close
Set rstAddApp = Nothing

AddPcsApprovalDoc = lngPcmId
End Function

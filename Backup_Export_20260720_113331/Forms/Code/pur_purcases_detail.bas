VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_detail"
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
    Case "Draft", "Under Revision"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "editor-s", "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
                Me.cmdRelease.Visible = True
                Me.cmdAddItem.Visible = True
                Me.cmdAddQuotes.Visible = True
                Me.cmdAddFirms.Visible = True
                Me.cmdCompStat.Visible = True
                Me.cmdMinute.Visible = True
                If Me.pcs_quotetype = 1 Then
                    Me.pcs_inttax.Locked = False
                    Me.pcs_inttax.BorderStyle = 1
                    Me.pcs_midtax.Locked = False
                    Me.pcs_midtax.BorderStyle = 1
                    End If
            Case "editor-m", "approver-m"
                If getVar("varUnitArea") = "prc" Then
                    Me.AllowEdits = True
                    Me.pcs_quotetype.BorderStyle = 1
                    Me.box_pcs_quotetype.Visible = False
                    Me.CmdIt.Visible = True
                    Me.cmdAddQuotes.Visible = True
                    End If
            End Select
    Case "Under Scrutiny"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Select Case getVar("varMode")
            Case "approver-m"
                Me.cmdForward.Visible = True
                Me.cmdReturn.Visible = True
            End Select
    Case "Under Approval"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        If Me!pcs_status = "Draft" Then Me!pcs_date = DateValue(GetNow())
        Select Case getVar("varMode")
            Case "approver-m"
                Me.cmdApprove.Visible = True
                Me.cmdReject.Visible = True
                Me.cmdReturn.Visible = True
            End Select
    Case "Approved"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Me.subAttachments.Visible = True
        Select Case getVar("varMode")
            Case "approver-s"
                Me.cmdReceive.Visible = True
                Me.cmdCancel.Visible = True
                Me.cmdPayMile.Visible = True
                Me.cmdReverse.Visible = True
            Case "editor-s"
                Me.cmdReceive.Visible = True
                Me.cmdPayMile.Visible = True
            End Select
    Case "Fulfilled", "Partially Fulfilled"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Me.subAttachments.Visible = True
        Select Case getVar("varMode")
            Case "approver-s"
                Me.cmdReverse.Visible = True
                Me.cmdPayMile.Visible = True
            Case "editor-s"
                Me.cmdPayMile.Visible = True
            End Select
    Case "Cancelled"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Me.subAttachments.Visible = True
    Case "OtherInitUnit"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
    End Select

Me.Visible = True

End Sub

Private Sub Form_Activate()
If (Me!pcs_status <> "Draft" And Me!pcs_status <> "Under Revision") Or getVar("varRoleAuth") = "viewer" Then Exit Sub
If Me.chkDataUpdateRequired = 0 Then Exit Sub
CompareQuotesAndMarkLowest
UpdatePricesAsPerLowestQuote
Refresh
Me.chkDataUpdateRequired = 0
End Sub

Private Sub pcs_effhed_id_AfterUpdate()
EffHead__AfterUpdate
End Sub

Private Sub pcs_hed_id_AfterUpdate()
Head__AfterUpdate
End Sub

Private Sub pcs_inttax_AfterUpdate()
On Error Resume Next
Me.pcs_inttax = Round(Me.pcs_inttax, 0)
Me.pcs_midprice = Me.pcs_Intprice + Me.pcs_inttax
Me.pcs_price = Me.pcs_midprice + Me.pcs_midtax
End Sub

Private Sub pcs_midtax_AfterUpdate()
On Error Resume Next
Me.pcs_midtax = Round(Me.pcs_midtax, 0)
Me.pcs_price = Me.pcs_midprice + Me.pcs_midtax
End Sub

Private Sub pcs_quotetype_BeforeUpdate(Cancel As Integer)
If Me.subQuotes.Form.Recordset.RecordCount > 0 Then
    Cancel = True
    Me.pcs_quotetype.Undo
    MsgBox "To change this value, first delete all quotes."
    End If
End Sub

Private Sub pcs_quotetype_AfterUpdate()
If Me.pcs_quotetype = 1 Then
    Me.pcs_inttax.Locked = False
    Me.pcs_inttax.BorderStyle = 1
    Me.pcs_midtax.Locked = False
    Me.pcs_midtax.BorderStyle = 1
    Else
    Me.pcs_inttax.Locked = True
    Me.pcs_inttax.BorderStyle = 0
    Me.pcs_midtax.Locked = True
    Me.pcs_midtax.BorderStyle = 0
    End If
End Sub

Private Sub cmdAddItem_Click()
On Error GoTo cmdAddItem_Click_Err

Me.Dirty = False
If Me.subQuotes.Form.Recordset.RecordCount > 0 Then
    MsgBox "To add items, all quotes have to be deleted.", vbCritical
    Exit Sub
    End If
    
DoCmd.OpenForm "pur_purcaseitems", acNormal, "", "", , acHidden, "NewRecord~" & Me!pcs_id

cmdAddItem_Click_Exit:
    Exit Sub

cmdAddItem_Click_Err:
    If Err.Number = 3155 Then
        MsgBox "Please complete case data before adding item", vbCritical
        Else
        MsgBox Err.Number & "-" & Error$, vbCritical
        End If
    Resume cmdAddItem_Click_Exit

End Sub

Private Sub cmdAddQuotes_Click()
Dim dbsDb As Database
Dim rstQtSource As Recordset
Dim rstQtDestin As Recordset
Dim lngQtId As Long
Dim IsFirstQuote As Boolean

Me.Dirty = False
If Me.subPurCaseItems.Form.Recordset.RecordCount = 0 Then
    MsgBox "Please add items before adding quotes.", vbCritical
    Exit Sub
    End If

If IsNull(Me.pcs_quotetype) Or Me.pcs_quotetype = "" Then
    MsgBox "Please select quote type first.", vbCritical
    Exit Sub
    End If
Forms!pur_purcases_detail.chkDataUpdateRequired = -1

Set dbsDb = CurrentDb()
Set rstQtDestin = dbsDb.OpenRecordset("Select qte_id From pur_quotes Where qte_pcs_id =" & Me!pcs_id, dbOpenSnapshot)
If rstQtDestin.EOF Then IsFirstQuote = True
rstQtDestin.Close
   
'*Add quote
Set rstQtDestin = dbsDb.OpenRecordset("pur_quotes", dbOpenDynaset, dbSeeChanges)
With rstQtDestin
    .AddNew
    !qte_pcs_id = Me!pcs_id
    !qte_recomm = IIf(IsFirstQuote = True, 1, 0)
    !qte_quotetype = Me!pcs_quotetype
    .Update
    .Bookmark = .LastModified
    End With
lngQtId = rstQtDestin!qte_id

'*Add quote items
Set rstQtSource = Me.subPurCaseItems.Form.RecordsetClone
Set rstQtDestin = dbsDb.OpenRecordset("pur_quoteitems", dbOpenDynaset, dbAppendOnly)
rstQtSource.MoveFirst
Do While Not rstQtSource.EOF
    With rstQtDestin
        .AddNew
        !qti_qte_id = lngQtId
        !qti_pci_id = rstQtSource!pci_id
        !qti_serial = rstQtSource!pci_serial
        !qti_desc = rstQtSource!pci_desc
        !qti_pcsdesc = rstQtSource!pci_desc
        !qti_qty = rstQtSource!pci_qty
        !qti_qtyunit = rstQtSource!pci_qtyunit
        .Update
        End With
    rstQtSource.MoveNext
    Loop
rstQtSource.Close
Set rstQtSource = Nothing
rstQtDestin.Close
Set rstQtDestin = Nothing

Me.subQuotes.Requery
DoCmd.OpenForm "pur_quotes_detail", acNormal, , , , acHidden, "NewRecord~" & lngQtId
End Sub

Private Sub cmdAddFirms_Click()
Me.Dirty = False
Me.chkDataUpdateRequired = -1
DoCmd.OpenForm "pur_noquotes_detail", acNormal, , , , acHidden, "NewRecord~" & Me.pcs_id
End Sub

Private Sub cmdRelease_Click()
Dim strObjections As String
strObjections = AnyObjectionsOnExp(Me!pcs_effhed_id, Me!pcs_hed_id, Me!pcs_sudohed, _
                                   IIf(Me!pcs_status = "Under Revision", 0, Me!pcs_midprice), _
                                   IIf(Me!pcs_status = "Under Revision", 0, Me!pcs_price))
If strObjections <> "" Then
    MsgBox strObjections, vbCritical
    Exit Sub
    End If
If ClearForRelease(Me!pcs_type) = False Then Exit Sub
ReleasePC
End Sub

Private Sub cmdForward_Click()
ForwardPC
End Sub

Private Sub cmdReturn_Click()
ReturnPC
End Sub

Private Sub cmdReject_Click()
RejectPC
End Sub

Private Sub cmdApprove_Click()
ApprovePC
End Sub

Private Sub cmdCancel_Click()
CancelPC
End Sub

Private Sub cmdReceive_Click()
Dim dbsRec As Database
Dim rstRec As Recordset
ReceivePCItems
End Sub

Public Sub CompareQuotesAndMarkLowest()
Dim dbsMark As Database
Dim qdfMark As QueryDef
Dim rstMark As Recordset
Dim idLowest As String
Dim intTotal As Integer

'***Get Id of lowest tech acceptable quote
Set dbsMark = CurrentDb()
Set qdfMark = dbsMark.QueryDefs("pur_quotesmin")
qdfMark.Parameters("PurCaseId") = Me!pcs_id
Set rstMark = qdfMark.OpenRecordset
If rstMark.EOF = True Then Exit Sub
idLowest = rstMark!qte_id
rstMark.Close
qdfMark.Close

'***Set Lowest tech acceptable quote as recommended and clear others
Set qdfMark = dbsMark.QueryDefs("pur_quotes_recomm")
qdfMark.Parameters("PurCaseId") = Me!pcs_id
qdfMark.Parameters("Id") = idLowest
qdfMark.Execute
qdfMark.Close
Set qdfMark = Nothing
End Sub

Public Sub UpdatePricesAsPerLowestQuote()
Dim dbsUdata As Database
Dim rstUData As Recordset
Dim qryUData As QueryDef
Dim booNoQuotes As Boolean
Dim arrUData() As Variant

'*** Update prices of Purchase Case Items
Set dbsUdata = CurrentDb()
Set rstUData = dbsUdata.OpenRecordset("Select * From pur_quotes Where qte_pcs_id = " & Me!pcs_id & " And qte_recomm = '1'", dbOpenSnapshot)
If Not rstUData.EOF Then
    booNoQuotes = False
    Set qryUData = dbsUdata.QueryDefs("pur_purcase_updatetolowest")
    qryUData.Parameters("PurCaseId") = Me!pcs_id
    qryUData.Execute
    Else
    booNoQuotes = True
    Set qryUData = dbsUdata.QueryDefs("pur_purcase_updatetolowest_nothing")
    qryUData.Parameters("PurCaseId") = Me!pcs_id
    qryUData.Execute
    End If

'*** Update Prices in Purchase Case
If booNoQuotes = True Then
    Me.pcs_frm_id = ""
    Me!pcs_Intprice = 0
    Me!pcs_inttax = 0
    Me!pcs_midprice = 0
    Me!pcs_midtax = 0
    Me!pcs_price = 0
    Else
    Me!pcs_Intprice = rstUData!qte_intprice
    If Me.pcs_quotetype = 2 Then
        Me!pcs_inttax = rstUData!qte_inttax
        Me!pcs_midtax = rstUData!qte_midtax
        Else
        arrUData = GetTaxes("pcs", Me!pcs_id)
        Me!pcs_inttax = arrUData(1)
        Me!pcs_midtax = arrUData(2)
        End If
    Me!pcs_midprice = Me!pcs_Intprice + Me!pcs_inttax
    Me!pcs_price = Me!pcs_midprice + Me!pcs_midtax
    End If
    
Set rstUData = Nothing

'*** Update firm and recommendation
UpdateFirmAndRecomm

End Sub

Private Sub UpdateFirmAndRecomm()

Dim dbsRecomm As Database
Dim rstRecomm As Recordset
Dim priceLowest  As Long
Dim intUnacceptable  As Integer
Dim intTotal As Integer
Dim strLowest As String
Dim lngLowest As Long
Dim strUnacceptable As String
Dim strRecomm As String

Set dbsRecomm = CurrentDb()
Set rstRecomm = dbsRecomm.OpenRecordset("Select * From pur_quotes Inner Join frm_firms On pur_quotes.qte_frm_id = frm_firms.frm_id " & _
                                        "Where qte_pcs_id = " & Me!pcs_id, dbOpenSnapshot)
If rstRecomm.EOF Then
    Me.pcs_recomm = ""
    Exit Sub
    End If

If IsNull(rstRecomm!qte_price) Then
    Me.pcs_recomm = "Cannot be calculated"
    Exit Sub
    End If

priceLowest = rstRecomm!qte_price
strLowest = rstRecomm!frm_name
lngLowest = rstRecomm!frm_id
intUnacceptable = 0
Do While Not rstRecomm.EOF
    If rstRecomm!qte_techaccept = 1 Then
        If rstRecomm!qte_price < priceLowest Then
            priceLowest = rstRecomm!qte_price
            strLowest = rstRecomm!frm_name
            lngLowest = rstRecomm!frm_id
            End If
        Else
        intUnacceptable = intUnacceptable + 1
        strUnacceptable = strUnacceptable & ", M/s " & rstRecomm!frm_name
        End If
    rstRecomm.MoveNext
    Loop
intTotal = rstRecomm.RecordCount
If strUnacceptable <> "" Then strUnacceptable = Right(strUnacceptable, Len(strUnacceptable) - 2)
If intUnacceptable > 0 Then
    If intUnacceptable = 1 Then
        strUnacceptable = "Offer of " & strUnacceptable & " is technically unacceptable."
        Else
        strUnacceptable = "Offers of " & strUnacceptable & " are technically unacceptable."
        End If
    End If
strLowest = "Offer of M/s " & strLowest & " is recommended based on lowest cost."
strRecomm = IIf(Len(strUnacceptable) > 0, strUnacceptable & vbCrLf, "") & strLowest
If intTotal - intUnacceptable = 1 Then
    rstRecomm.MoveFirst
    Do While rstRecomm!qte_techaccept <> 1      'FindFirst method not working
        rstRecomm.MoveNext
        Loop
    strRecomm = "Offer of M/s " & rstRecomm!frm_name & " is recommended as it is the only technically acceptable offer."
    End If
Me.pcs_frm_id = lngLowest
Me.pcs_recomm = strRecomm

End Sub

Private Sub cmdCompStat_Click()
On Error GoTo cmdCompStat_Click_Err

Me.Dirty = False
Forms!vars.Parameter1 = Me!pcs_id
DoCmd.OpenReport "pur_compstatement", acViewReport, "", "", acNormal


cmdCompStat_Click_Exit:
    Exit Sub

cmdCompStat_Click_Err:
    MsgBox Error$
    Resume cmdCompStat_Click_Exit

End Sub

Private Sub cmdPcs_Click()
On Error GoTo cmdPcs_Click_Err

Me.Dirty = False
DoCmd.OpenReport "pur_purcases_detail", acViewReport, "", "", acNormal

cmdPcs_Click_Exit:
    Exit Sub

cmdPcs_Click_Err:
    MsgBox Error$
    Resume cmdPcs_Click_Exit

End Sub

Private Sub CmdIt_Click()
On Error GoTo CmdIt_Click_Err

Me.Dirty = False
DoCmd.OpenReport "pur_it_detail", acViewReport, "", "", acNormal

CmdIt_Click_Exit:
    Exit Sub

CmdIt_Click_Err:
    MsgBox Error$
    Resume CmdIt_Click_Exit

End Sub

Private Sub cmdMinute_Click()
On Error GoTo cmdMinute_Click_Err

Dim intresponse As Integer
Dim strDocName As String
Dim dbsMin As Database
Dim rstMin As Recordset
Dim lngMin As Long


Me.Dirty = False
If IsNull(Me!pcs_minute) Then
    MsgBox "Please enter minute number", vbCritical
    Exit Sub
    End If

Set dbsMin = CurrentDb()
Set rstMin = dbsMin.OpenRecordset("Select pcm_id From pur_purcaseminutes Where pcm_purcases = '" & Me!pcs_id & "'", dbOpenDynaset)
If Not rstMin.EOF Then
    lngMin = rstMin!pcm_id
    GoTo OpenMinute
    End If
    
'Make New Minute
intresponse = MsgBox("New minute will be created. Do you want to proceed?", 4, "Confirmation")
If intresponse <> 6 Then Exit Sub
lngMin = AddPcsApprovalDoc(Me!pcs_id, Me!pcs_minute)
Me.Refresh

'Open minute
OpenMinute:
If Not (Me.pcs_effhed_id >= 200000 And IsNull(Me.pcs_hed_id)) Then
    strDocName = "pur_purcaseminutes"
    Else
    strDocName = "pur_purcaseminutes_chrf"
    End If

DoCmd.OpenForm strDocName, acNormal, "", "", , acHidden, _
                lngMin & "~" & Me!pcs_minute & "~" & Me!pcs_hed_id & "~" & Me!pcs_effhed_id & "~" & Me!pcs_transtype & "~" & Me!pcs_type & "~" & Me!pcs_status

cmdMinute_Click_Exit:
    Exit Sub

cmdMinute_Click_Err:
    MsgBox Error$
    Resume cmdMinute_Click_Exit

End Sub

Private Sub cmdPayMile_Click()
On Error GoTo cmdPayMile_Click_Err

Me.Dirty = False
DoCmd.OpenForm "pur_purcasemilestone", acNormal, "", "", , acNormal

cmdPayMile_Click_Exit:
    Exit Sub

cmdPayMile_Click_Err:
    MsgBox Error$
    Resume cmdPayMile_Click_Exit

End Sub

Private Sub cmdReverse_Click()
DoCmd.OpenForm "pur_purcases_rev", acNormal, "", "", , acHidden, Me!pcs_quotetype
End Sub



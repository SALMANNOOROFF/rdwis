VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasestada_detail"
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
        If Me!pcs_status = "Draft" Then Me!pcs_date = DateValue(GetNow())
        Select Case getVar("varMode")
            Case "editor-s", "approver-s"
                AllowEditsAdvanced Me.Name, False, False
                Me.cmdCancel.Visible = True
                Me.cmdRelease.Visible = True
                Me.cmdMinute.Visible = True
                'Me.subPurCaseItems.Form.balance_qty.Width = 0
                Me.cmdAddItem.Visible = True
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
        Select Case getVar("varMode")
            Case "approver-m"
                Me.cmdApprove.Visible = True
                Me.cmdReject.Visible = True
                Me.cmdReturn.Visible = True
            End Select
    Case "Approved", "Fulfilled"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
        Me.subAttachments.Visible = True
        Select Case getVar("varMode")
            Case "approver-s"
                Me.cmdReceive.Visible = True
                Me.cmdCancel.Visible = True
                Me.cmdReverse.Visible = True
            Case "editor-s"
                Me.cmdReceive.Visible = True
            End Select
    Case "OtherInitUnit"
        Me.RecordSource = "Select * From pur_purcases Where pcs_id = " & arrArgs(1)
    End Select

Me.Visible = True

End Sub

Private Sub Form_Activate()
If (Me!pcs_status <> "Draft" And Me!pcs_status <> "Under Revision") Or getVar("varRoleAuth") = "viewer" Then Exit Sub    'Just in case
If Me.chkDataUpdateRequired = 0 Then Exit Sub
Refresh
UpdatePurCaseData
Me.chkDataUpdateRequired = 0
End Sub

Private Sub pcs_effhed_id_AfterUpdate()
EffHead__AfterUpdate
End Sub

Private Sub pcs_hed_id_BeforeUpdate(Cancel As Integer)
If getVar("varUnitId") <> 840000 Then
    Cancel = True
    Me.pcs_hed_id.Undo
    End If
End Sub

Private Sub pcs_hed_id_AfterUpdate()
Head__AfterUpdate
End Sub

Private Sub cmdAddItem_Click()
On Error GoTo cmdAddItem_Click_Err

Me.Dirty = False
If IsNull(Me!pcs_id) Then
    MsgBox "Please enter case data before adding items", vbCritical
    Exit Sub
    End If

DoCmd.OpenForm "pur_purcasestadaitems", acNormal, "", "", , acHidden, "NewRecord~" & Me!pcs_id

cmdAddItem_Click_Exit:
    Exit Sub

cmdAddItem_Click_Err:
    If Err.Number = 3155 Then
        MsgBox "Please complete case data before adding item", vbCritical
        Else
        MsgBox Err.Number & "-" & Error$
        End If
    Resume cmdAddItem_Click_Exit

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
ReceivePCItems
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
If Not IsNull(Me.pcs_hed_id) Then
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

Public Sub UpdatePurCaseData()
Me!pcs_Intprice = Me.subPurCaseItems.Form.sum_price_total
If Nz(Me!pcs_inttax, "") = "" Then Me!pcs_inttax = 0
Me!pcs_midprice = Me!pcs_Intprice + Me!pcs_inttax
If Nz(Me!pcs_midtax, "") = "" Then Me!pcs_midtax = 0
Me!pcs_price = Me!pcs_midprice + Me!pcs_midtax

End Sub

Private Sub cmdPcs_Click()
On Error GoTo cmdPcs_Click_Err

Me.Dirty = False
DoCmd.OpenReport "pur_purcasestada_detail", acViewReport, "", "", acNormal

cmdPcs_Click_Exit:
    Exit Sub

cmdPcs_Click_Err:
    MsgBox Error$
    Resume cmdPcs_Click_Exit

End Sub

Private Sub cmdReverse_Click()
Dim intresponse As Integer
Dim lngRevId As Long

intresponse = MsgBox("A Data Revision Request for Purchase Case " & Me!pcs_id & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Purchase Case", Me!pcs_id, Me.pcs_intunt_id, 1)
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "pur_purcasestada_detail"
DoCmd.Close acForm, "pur_purcases_u"
End Sub


VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_choose"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub lbxPurchaseCases_AfterUpdate()
Dim varItem As Variant
Dim strIds As String
Dim arrIds() As Variant

For Each varItem In Me.lbxPurchaseCases.ItemsSelected
    strIds = strIds & Me.lbxPurchaseCases.ItemData(varItem) & ","
    Next varItem
If Len(strIds) <> 0 Then strIds = Left(strIds, Len(strIds) - 1)

arrIds = ArrayFromString(strIds)
Call BubbleSort(arrIds)
strIds = Join(arrIds, ",")
Me.txtIDs = strIds

End Sub

Private Sub cmdOK_Click()
Dim dbsMin As Database
Dim rstMin As Recordset
Dim lngHead As Long, lngEffHead As Long
Dim intMinNum As Integer, intTransType As Integer
Dim lngApprovalDoc As Long, strPurCaseIds As String, strPurCaseType As String
Dim booEffHeadMismatch As Boolean, booDataMismatch As Boolean, booMissingHead As Boolean
Dim arrPurcaseIds() As Variant
Dim intResponse As Integer
Dim strStatus As String
Dim strFormName As String, strArgs As String

'Stop if no or one minute is selected
If IsNull(Me!txtIDs) Or Not Me!txtIDs Like "*,*" Then
    MsgBox "Please select atleast two purchase cases.", vbCritical
    Exit Sub
    End If

'Check pre-conditions for making a new minute and record values
Set dbsMin = CurrentDb()
Set rstMin = dbsMin.OpenRecordset("Select * From pur_purcases Where pcs_id In (" & Me.txtIDs & ") Order By pcs_id Desc", dbOpenDynaset)
lngHead = Nz(rstMin!pcs_hed_id, 0)
lngEffHead = Nz(rstMin!pcs_effhed_id, 0)
intMinNum = Nz(rstMin!pcs_minute, 0)
strPurCaseType = rstMin!pcs_type
strPurCaseIds = rstMin!pcs_id
strStatus = rstMin!pcs_status
rstMin.MoveNext
Do While Not rstMin.EOF
    If lngEffHead = -1 Or lngHead = -1 Then booMissingHead = True
    If lngEffHead <> Nz(rstMin!pcs_effhed_id, 0) Then booEffHeadMismatch = True
    If lngHead <> Nz(rstMin!pcs_hed_id, 0) Then booDataMismatch = True
    If intMinNum <> Nz(rstMin!pcs_minute, 0) Then booDataMismatch = True
    If strPurCaseType <> rstMin!pcs_type Then booDataMismatch = True
    If strStatus <> rstMin!pcs_status Then booDataMismatch = True
    lngHead = Nz(rstMin!pcs_hed_id, 0)
    lngEffHead = Nz(rstMin!pcs_effhed_id, 0)
    intMinNum = Nz(rstMin!pcs_minute, 0)
    strPurCaseIds = rstMin!pcs_id & IIf(strPurCaseIds = "", "", "," & strPurCaseIds)
    intTransType = rstMin!pcs_transtype
    rstMin.MoveNext
    Loop

If booMissingHead = True Then
    MsgBox "Please enter missing heads and recipients in selected cases.", vbCritical
    Exit Sub
    End If
If intMinNum = 0 Then
    MsgBox "Please enter minute number in selected cases.", vbCritical
    Exit Sub
    End If
If lngHead = 0 And booEffHeadMismatch = True And booDataMismatch = True Then
    MsgBox "The selected cases must have same minutes, heads and recipients.", vbCritical
    Exit Sub
    End If
If Not lngHead = 0 And booDataMismatch = True Then
    MsgBox "The selected cases must have same types, minutes, status and for-heads.", vbCritical
    Exit Sub
    End If

'Make new minute if required
Set rstMin = dbsMin.OpenRecordset("Select pcm_id From pur_purcaseminutes Where pcm_purcases = '" & Me.txtIDs & "'", dbOpenDynaset)
If Not rstMin.EOF Then
    lngApprovalDoc = rstMin!pcm_id
    Else
    arrPurcaseIds = ArrayFromString(strPurCaseIds)
    Call BubbleSort(arrPurcaseIds)
    strPurCaseIds = Join(arrPurcaseIds, ",")
    intResponse = MsgBox("New minute will be created. Do you want to proceed?", 4, "Confirmation")
    If intResponse <> 6 Then Exit Sub
    lngApprovalDoc = AddPcsApprovalDoc(strPurCaseIds, intMinNum)
    End If

'Open minute
Select Case lngHead
    Case 0
    strFormName = "pur_purcaseminutes_chrf"
    strArgs = lngApprovalDoc & "~" & intMinNum & "~~~" & intTransType & "~" & strPurCaseType & "~" & strStatus
    Case Else
    strFormName = "pur_purcaseminutes"
    strArgs = lngApprovalDoc & "~" & intMinNum & "~" & lngHead & "~" & lngEffHead & "~" & intTransType & "~" & strPurCaseType & "~" & strStatus
    End Select
    

DoCmd.Close
DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden, strArgs


End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

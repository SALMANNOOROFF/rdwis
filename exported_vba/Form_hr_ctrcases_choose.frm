VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcases_choose"
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
Dim intMinNum As Integer, intResponse As Integer
Dim lngApprovalDoc As Long, lngUnitId As Long, lngProjId
Dim strCtrcaseIds As String, strCtrcaseType As String, strStatus As String
Dim booDataMismatch As Boolean, booDataMismatch2 As Boolean
Dim booMissingMinNum As Boolean, booCentral As Boolean
Dim arrCtrCaseIds() As Variant
Dim strFormName As String, strArgs As String

'Stop if no or one minute is selected
If IsNull(Me!txtIDs) Or Not Me!txtIDs Like "*,*" Then
    MsgBox "Please select atleast two contract cases.", vbCritical
    Exit Sub
    End If

'Check pre-conditions for making a new minute and record values
Set dbsMin = CurrentDb()
Set rstMin = dbsMin.OpenRecordset("Select * From hr_ctrcases Where ctc_id In (" & Me.txtIDs & ") Order By ctc_id Desc", dbOpenDynaset)
strCtrcaseIds = rstMin!ctc_id
lngUnitId = rstMin!ctc_unt_id
lngProjId = rstMin!ctc_prj_id
booCentral = IIf(rstMin!ctc_unt_id >= 200000 And rstMin!ctc_unt_id < 800000, 0, -1)
intMinNum = Nz(rstMin!ctc_minute, 0)
If intMinNum = 0 Then booMissingMinNum = True
strCtrcaseType = rstMin!ctc_type
strStatus = rstMin!ctc_status
rstMin.MoveNext
Do While Not rstMin.EOF
    If intMinNum <> Nz(rstMin!ctc_minute, 0) Then booDataMismatch = True
    If intMinNum = 0 Then booMissingMinNum = True
    If strCtrcaseType <> rstMin!ctc_type Then booDataMismatch = True
    If strStatus <> rstMin!ctc_status Then booDataMismatch = True
    If lngProjId <> rstMin!ctc_prj_id Then booDataMismatch2 = True
    strCtrcaseIds = rstMin!ctc_id & IIf(strCtrcaseIds = "", "", "," & strCtrcaseIds)
    rstMin.MoveNext
    Loop

If booMissingMinNum = True Then
    MsgBox "Please enter minute number in selected cases.", vbCritical
    Exit Sub
    End If
If booDataMismatch = True Then
    MsgBox "The selected cases must have same types, minutes and status.", vbCritical
    Exit Sub
    End If

If booDataMismatch2 = True And strCtrcaseType = "Hg" Then
    MsgBox "First projects in project plans of all cases must be the same.", vbCritical
    Exit Sub
    End If

'Make new minute if required
Set rstMin = dbsMin.OpenRecordset("Select ccm_id From hr_ctrcaseminutes Where ccm_ctrcases = '" & Me.txtIDs & "'", dbOpenDynaset)
If Not rstMin.EOF Then
    lngApprovalDoc = rstMin!ccm_id
    Else
    arrCtrCaseIds = ArrayFromString(strCtrcaseIds)
    Call BubbleSort(arrCtrCaseIds)
    strCtrcaseIds = Join(arrCtrCaseIds, ",")
    intResponse = MsgBox("New minute will be created. Do you want to proceed?", 4, "Confirmation")
    If intResponse <> 6 Then Exit Sub
    lngApprovalDoc = AddCtrApprovalDoc(strCtrcaseIds, intMinNum)
    End If

'Open minute
Select Case booCentral
    Case 0
    strFormName = "hr_ctrcaseminutes"
    Case -1
    strFormName = "hr_ctrcaseminutes_chrf"
    End Select
strArgs = lngApprovalDoc & "~" & intMinNum & "~" & strCtrcaseType & "~" & strStatus & "~" & CalculateCcPrice(strCtrcaseIds) & "~" & lngUnitId & "~" & lngProjId
    

DoCmd.Close
DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden, strArgs


End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

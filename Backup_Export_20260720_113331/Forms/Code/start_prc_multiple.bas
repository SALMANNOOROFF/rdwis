VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_prc_multiple"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
StartupSteps Me.Name
Me.Visible = True
End Sub

Private Sub Form_Current()
SetWarningsFinance
End Sub

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub

Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
End Sub

Private Sub cmdMyUnit_Click()
On Error GoTo cmdMyUnit_Click_Err

DoCmd.OpenForm "start_all_single", acNormal, "", "", , acNormal

cmdMyUnit_Click_Exit:
    Exit Sub

cmdMyUnit_Click_Err:
    MsgBox Error$
    Resume cmdMyUnit_Click_Exit

End Sub

Private Sub cmdSalHeads_Click()
On Error GoTo cmdSalHeads_Click_Err

DoCmd.OpenForm "fin_empeffheads_u", acNormal, "", "", , acNormal


cmdSalHeads_Click_Exit:
    Exit Sub

cmdSalHeads_Click_Err:
    MsgBox Error$
    Resume cmdSalHeads_Click_Exit

End Sub

Private Sub cmdContractsVerif_Click()
On Error GoTo cmdContractsVerif_Click_Err

DoCmd.OpenForm "fin_contractsverif", acNormal, "", "", , acNormal


cmdContractsVerif_Click_Exit:
    Exit Sub

cmdContractsVerif_Click_Err:
    MsgBox Error$
    Resume cmdContractsVerif_Click_Exit

End Sub

Private Sub cmdSalOrdersDraft_Click()
On Error GoTo cmdSalOrdersDraft_Click_Err

DoCmd.OpenForm "fin_salorders_u", acNormal, "", "", , acHidden, "Draft"


cmdSalOrdersDraft_Click_Exit:
    Exit Sub

cmdSalOrdersDraft_Click_Err:
    MsgBox Error$
    Resume cmdSalOrdersDraft_Click_Exit

End Sub

Private Sub cmdSalOrdersOpen_Click()
On Error GoTo cmdSalOrdersOpen_Click_Err

DoCmd.OpenForm "fin_salorders_u", acNormal, "", "", , acHidden, "Open"

cmdSalOrdersOpen_Click_Exit:
    Exit Sub

cmdSalOrdersOpen_Click_Err:
    MsgBox Error$
    Resume cmdSalOrdersOpen_Click_Exit

End Sub

Private Sub cmdSalOrdersClosed_Click()
On Error GoTo cmdSalOrdersClosed_Click_Err

DoCmd.OpenForm "fin_salorders_u", acNormal, "", "", , acHidden, "Closed"


cmdSalOrdersClosed_Click_Exit:
    Exit Sub

cmdSalOrdersClosed_Click_Err:
    MsgBox Error$
    Resume cmdSalOrdersClosed_Click_Exit

End Sub


Private Sub cmdSalReqsWaiting_Click()
On Error GoTo cmdSalReqsWaiting_Click_Err

DoCmd.OpenForm "hr_salreqs_u", acNormal, "", "", , acHidden, "Waiting"


cmdSalReqsWaiting_Click_Exit:
    Exit Sub

cmdSalReqsWaiting_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsWaiting_Click_Exit

End Sub

Private Sub CmdCtrCasesOpen_Click()
On Error GoTo CmdCtrCasesOpen_Click_Err

DoCmd.OpenForm "hr_ctrcases_u", acNormal, "", "", , acNormal, "Open~"

CmdCtrCasesOpen_Click_Exit:
    Exit Sub

CmdCtrCasesOpen_Click_Err:
    MsgBox Error$
    Resume CmdCtrCasesOpen_Click_Exit

End Sub

Private Sub CmdCtrCasesClosed_Click()
On Error GoTo CmdCtrCasesClosed_Click_Err

DoCmd.OpenForm "hr_ctrcases_u", acNormal, "", "", , acNormal, "Closed~"

CmdCtrCasesClosed_Click_Exit:
    Exit Sub

CmdCtrCasesClosed_Click_Err:
    MsgBox Error$
    Resume CmdCtrCasesClosed_Click_Exit

End Sub

Private Sub cmdEmpCurrent_Click()
On Error GoTo cmdEmpCurrent_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"

cmdEmpCurrent_Click_Exit:
    Exit Sub

cmdEmpCurrent_Click_Err:
    MsgBox Error$
    Resume cmdEmpCurrent_Click_Exit
End Sub

Private Sub cmdEmpPrev_Click()
On Error GoTo cmdEmpPrev_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Previous~"

cmdEmpPrev_Click_Exit:
    Exit Sub

cmdEmpPrev_Click_Err:
    MsgBox Error$
    Resume cmdEmpPrev_Click_Exit
End Sub

Private Sub cmdActiveEmp_Click()
On Error GoTo cmdActiveEmp_Click_Err

DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal


cmdActiveEmp_Click_Exit:
    Exit Sub

cmdActiveEmp_Click_Err:
    MsgBox Error$
    Resume cmdActiveEmp_Click_Exit

End Sub


Private Sub cmdExServicemen_Click()
On Error GoTo cmdExServicemen_Click_Err

    DoCmd.OpenReport "hr_emps_exservicemen_doc", acViewReport, "", "", acNormal


cmdExServicemen_Click_Exit:
    Exit Sub

cmdExServicemen_Click_Err:
    MsgBox Error$
    Resume cmdExServicemen_Click_Exit

End Sub

Private Sub cmdPurReqOpen_Click()
On Error GoTo cmdPurReqOpen_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Open"


cmdPurReqOpen_Click_Exit:
    Exit Sub

cmdPurReqOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurReqOpen_Click_Exit

End Sub

Private Sub cmdPurReqClosed_Click()
On Error GoTo cmdPurReqClosed_Click_Err

    DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Closed"


cmdPurReqClosed_Click_Exit:
    Exit Sub

cmdPurReqClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurReqClosed_Click_Exit

End Sub

Private Sub cmdPurCaseDraft_Click()
On Error GoTo cmdPurCaseDraft_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Draft"

cmdPurCaseDraft_Click_Exit:
    Exit Sub

cmdPurCaseDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseDraft_Click_Exit
End Sub

Private Sub cmdPurCaseOpen_Click()
On Error GoTo cmdPurCaseOpen_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Open"

cmdPurCaseOpen_Click_Exit:
    Exit Sub

cmdPurCaseOpen_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseOpen_Click_Exit

End Sub


Private Sub cmdPurCaseClosed_Click()
On Error GoTo cmdPurCaseClosed_Click_Err

DoCmd.OpenForm "pur_purcases_u", acNormal, "", "", , acHidden, "Closed"

cmdPurCaseClosed_Click_Exit:
    Exit Sub

cmdPurCaseClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseClosed_Click_Exit

End Sub

Private Sub cmdPurRecClosed_Click()
On Error GoTo cmdPurRecClosed_Click_Err

DoCmd.OpenForm "pur_purreceipts_u", acNormal, "", "", , acNormal, "Closed"


cmdPurRecClosed_Click_Exit:
    Exit Sub

cmdPurRecClosed_Click_Err:
    MsgBox Error$
    Resume cmdPurRecClosed_Click_Exit

End Sub



Private Sub cmdAccStatus_Click()
Dim strFormName As String
On Error GoTo cmdAccStatus_Click_Err

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

If Me.cmbAccount >= 200000 Then
    strFormName = "fin_headstatus"
    Else
    strFormName = "fin_headstatus_cen"
    End If

DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden

cmdAccStatus_Click_Exit:
    Exit Sub

cmdAccStatus_Click_Err:
    MsgBox Error$
    Resume cmdAccStatus_Click_Exit
End Sub

Private Sub cmdAccStatusBrief_Click()
On Error GoTo cmdAccStatusBrief_Click_Err


DoCmd.OpenForm "fin_headstatus_brief", acNormal, "", "", , acNormal

cmdAccStatusBrief_Click_Exit:
    Exit Sub

cmdAccStatusBrief_Click_Err:
    MsgBox Error$
    Resume cmdAccStatusBrief_Click_Exit

End Sub
Private Sub cmdAccStatusDetails_Click()
On Error GoTo cmdAccStatusDetails_Click_Err

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

DoCmd.OpenForm "fin_headstatus_details", acNormal, "", "", , acNormal

cmdAccStatusDetails_Click_Exit:
    Exit Sub

cmdAccStatusDetails_Click_Err:
    MsgBox Error$
    Resume cmdAccStatusDetails_Click_Exit

End Sub

Private Sub cmdAccStatusMtss_Click()
On Error GoTo cmdAccStatusMtss_Click_Err

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

DoCmd.OpenForm "fin_headstatus_mtss", acNormal, "", "", , acNormal


cmdAccStatusMtss_Click_Exit:
    Exit Sub

cmdAccStatusMtss_Click_Err:
    MsgBox Error$
    Resume cmdAccStatusMtss_Click_Exit

End Sub

Private Sub cmdXExp_Click()

DoCmd.OpenReport "fin_loans", acViewReport, "", "", acNormal

End Sub

Private Sub cmdProjectShares_Click()

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesinstall_add", acNormal, , , , acHidden

End Sub

Private Sub cmdPayPending_Click()
On Error GoTo cmdPayPending_Click_Err
Dim strFormName As String

Select Case Me.frmPayments.Value
    Case 1: strFormName = "fin_commitments_u_pcs"
    Case 2: strFormName = "fin_commitments_u_so"
    End Select

DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden, "Open" & "~"

cmdPayPending_Click_Exit:
    Exit Sub

cmdPayPending_Click_Err:
    MsgBox Error$
    Resume cmdPayPending_Click_Exit

End Sub


Private Sub cmdPayClosed_Click()
On Error GoTo cmdPayClosed_Click_Err
Dim strFormName As String

Select Case Me.frmPayments.Value
    Case 1: strFormName = "fin_commitments_u_pcs"
    Case 2: strFormName = "fin_commitments_u_so"
    End Select

DoCmd.OpenForm strFormName, acNormal, "", "", , acHidden, "Closed" & "~"

cmdPayClosed_Click_Exit:
    Exit Sub

cmdPayClosed_Click_Err:
    MsgBox Error$
    Resume cmdPayClosed_Click_Exit

End Sub

Private Sub cmdPurchaseFirms_Click()
On Error GoTo cmdPurchaseFirms_Click_Err

DoCmd.OpenReport "fin_firms_overall", acViewReport, "", "", acNormal


cmdPurchaseFirms_Click_Exit:
    Exit Sub

cmdPurchaseFirms_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirms_Click_Exit

End Sub

Private Sub cmdPurchaseFirmsDetailed_Click()
On Error GoTo cmdPurchaseFirmsDetailed_Click_Err

DoCmd.OpenReport "fin_firms_projectwise", acViewReport, "", "", acNormal

cmdPurchaseFirmsDetailed_Click_Exit:
    Exit Sub

cmdPurchaseFirmsDetailed_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirmsDetailed_Click_Exit

End Sub

Private Sub cmdPurchaseFirmSingle_Click()
On Error GoTo cmdPurchaseFirmSingle_Click_Err

DoCmd.OpenForm "frm_firm_choose", acNormal, "", ""

cmdPurchaseFirmSingle_Click_Exit:
    Exit Sub

cmdPurchaseFirmSingle_Click_Err:
    MsgBox Error$
    Resume cmdPurchaseFirmSingle_Click_Exit

End Sub

Private Sub cmdAllocations_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenAllocationsReport "Open", Me.cmbUnit
End Sub

Private Sub cmdAccountsStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenAccountsStatusReport "Open", Me.cmbUnit
End Sub

Private Sub cmdProjSharesStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenProjectSharesReport "Open", Me.cmbUnit
End Sub

Private Sub cmdChrf_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select department.", vbCritical
    Exit Sub
    End If
OpenChrfReport "Open", Me.cmbUnit
End Sub

Private Sub cmdSubheadsStatus_Click()
If IsNull(Me.cmbUnit) Then
    MsgBox "Please select a department.", vbCritical
    Exit Sub
    End If
If Me.cmbUnit = 0 Then
    MsgBox "This report is not available for all departments. Please select one department.", vbCritical
    Exit Sub
    End If
OpenSubheadsStatusReport "Open", Me.cmbUnit
End Sub

Private Sub cmdMReturn_Click()
OpenMonthlyReport
End Sub

Private Sub cmdPCAwaited_Click()
On Error GoTo cmdPCAwaited_Click_Err

DoCmd.OpenReport "fin_purcases_awaited", acViewReport, "", "", acNormal

cmdPCAwaited_Click_Exit:
    Exit Sub

cmdPCAwaited_Click_Err:
    MsgBox Error$
    Resume cmdPCAwaited_Click_Exit

End Sub

Private Sub cmdFirmsView_Click()
On Error GoTo cmdFirmsView_Click_Err

DoCmd.OpenForm "frm_firms", acNormal, "", "", , acHidden, "Type~" & Me.cmbType

cmdFirmsView_Click_Exit:
    Exit Sub

cmdFirmsView_Click_Err:
    MsgBox Error$
    Resume cmdFirmsView_Click_Exit

End Sub

Private Sub cmdFirmsAddEdit_Click()
On Error GoTo cmdFirmsAddEdit_Click_Err
Dim dbsFirm As Database
Dim rstFirm As Recordset
Dim strFirmName As String
Dim lngFirmId As Long

strFirmName = InputBox("Please Enter Name of the firm to be added", "Add Firm")
If strFirmName = "" Then Exit Sub
Set dbsFirm = CurrentDb()
Set rstFirm = dbsFirm.OpenRecordset("frm_firmz", dbOpenDynaset, dbSeeChanges)
With rstFirm
    .AddNew
    !frm_name = strFirmName
    !frm_entity = "firm"
    !frm_type = "Private company"
    !frm_black = 0
    .Update
    .Bookmark = .LastModified
    lngFirmId = !frm_id
    End With
Me.Refresh

DoCmd.OpenForm "frm_firms_detail", acNormal, "", "", , acNormal, "View~" & lngFirmId

cmdFirmsAddEdit_Click_Exit:
    Exit Sub

cmdFirmsAddEdit_Click_Err:
    MsgBox Error$
    Resume cmdFirmsAddEdit_Click_Exit

End Sub

Private Sub cmbSearchFirms_AfterUpdate()
If Me.cmbSearchFirms = "frm_emp" Or cmbSearchFirms = "frm_points" Then
    Me.oprSearchFirms.Visible = True
    Else
    Me.oprSearchFirms.Visible = False
    End If
End Sub

Private Sub txtSearchInfoFirms_AfterUpdate()
Me.txtSearchInfoOffices = Me.txtSearchInfoFirms
Me.txtSearchInfoPersons = Me.txtSearchInfoFirms
End Sub

Private Sub cmbSearchProjects_AfterUpdate()
If Me.cmbSearchProjects = "prj_awarddt" Or Me.cmbSearchProjects = "prj_compdt" Then
    Me.txtSearchProjects.Format = "dd/mm/yyyy"
    Else
    Me.txtSearchProjects.Format = ""
    End If
End Sub

Private Sub oprSearchFirms_Click()

Select Case Me.oprSearchFirms.Caption
    Case "="
        Me.oprSearchFirms.Caption = ">"
    Case ">"
        Me.oprSearchFirms.Caption = "<"
    Case "<"
        Me.oprSearchFirms.Caption = "="
    End Select

End Sub

Private Sub lblReset_Click()
Dim ctlTxt As Control
For Each ctlTxt In Me.Controls
    If ctlTxt.Name Like "txtSearch*" Then
        ctlTxt = Null
        End If
    Next
    Me.chkAnyPart = True
End Sub

Private Sub cmdSearch_Click()

Dim strSearch As String
Dim strIds As String
Dim strSql As String
Dim dbs As Database
Dim rst As Recordset
Dim qry As QueryDef
Dim fld As Field
Dim rstSreach As Recordset

Me.Dirty = False

If IsNull(txtGlobalSearch) Then
    MsgBox "Please enter search word", vbCritical
    Exit Sub
    End If
    
Set dbs = CurrentDb()
strSearch = Me.txtGlobalSearch
For Each qry In dbs.QueryDefs
    If qry.Name Like "frm_search_*" Then
        For Each fld In qry.Fields
            If Not fld.Name Like "*_id" And Not fld.Name Like "*entity" And Not fld.Name Like "*black" Then
                strSql = "Select frm_id, " & fld.Name & " From " & qry.Name & " Where " & fld.Name & " like '*" & strSearch & "*'"
                'Debug.Print strSql
                Set rst = dbs.OpenRecordset(strSql, dbOpenForwardOnly)
                Do While Not rst.EOF
                    strIds = strIds & IIf(strIds Like "", "", ",") & rst!frm_id
                    rst.MoveNext
                    Loop
                rst.Close
                End If
            Next
        End If
    Next

If strIds = "" Then
    MsgBox "No match found"
    Exit Sub
    End If

DoCmd.OpenForm "frm_firms", acNormal, , , , acHidden, "Search~" & strIds


End Sub

Private Sub cmdAdvSearch_Click()

On Error GoTo cmdAdvSearch_Click_Err

Dim ctlTxt As Control
Dim arrSearch() As String
Dim nCol As Integer
Dim booIsNull As Boolean
Dim x As Integer, y As Integer
Dim strArray As String
Dim strElement As String


'Create a matrix -------------------------------------------------------------------
booIsNull = True
nCol = -1
For Each ctlTxt In Me.Controls
    If ctlTxt.Name Like "txtSearch*" Then  'TypeOf ctlTxt Is TextBox And
        If IsNull(ctlTxt) Then GoTo Next_Iteration
        booIsNull = False
        nCol = nCol + 1
        ReDim Preserve arrSearch(4, nCol)           'Data sets for max 7 (6+1) tables, each having 5 (4+1) members
        arrSearch(0, nCol) = LCase(Replace(ctlTxt.Name, "txtSearch", ""))     'Table name
        arrSearch(1, nCol) = Me.Controls(Replace(ctlTxt.Name, "txt", "cmb"))           'Field name
        arrSearch(2, nCol) = ctlTxt                                                    'Field data
        arrSearch(3, nCol) = Me.Controls(Replace(ctlTxt.Name, "txt", "cmb")).Column(2) 'Field data type
        If arrSearch(3, nCol) = 4 Then arrSearch(4, nCol) = Me.Controls(Replace(ctlTxt.Name, "txt", "opr")).Caption ' Opearator for numeric numbers
        End If
Next_Iteration:
    Next

If booIsNull = True Then
    MsgBox "Please enter any search criteria", vbCritical
    Exit Sub
    End If

PrintArray (arrSearch)

'Post entry data validation --------------------------------------------------------------
For y = 0 To nCol
    Select Case arrSearch(3, nCol)
        Case 1      'Boolean
            If arrSearch(2, nCol) <> "True" And arrSearch(2, nCol) <> "False" Then
                MsgBox "'" & arrSearch(2, nCol) & "' is not a true or false value. Please enter 'True' or 'False'", vbCritical
                Exit Sub
                End If
        Case 4      'Number
            If IsNumeric(arrSearch(2, nCol)) = False Then
                MsgBox "'" & arrSearch(2, nCol) & "' is not a number. Please replace it with a number.", vbCritical
                Exit Sub
                End If
        Case 8      'Date
            'MsgBox Day(arrSearch(2, nCol))
            If IsDate(arrSearch(2, nCol)) = False Then
                MsgBox "'" & arrSearch(2, nCol) & "' is not a date. Please replace it with a valid date.", vbCritical
                Exit Sub
                End If
        Case 10     'Short text
            'No validation
        Case 12     'Long text
            'No validation
        End Select
    Next
        
    
'Creation SQL string
Dim strSql As String, strSqlFrom As String, strSqlCriteria As String
Dim rstSearch As Recordset
Dim strIds As String, strIDsInfo As String

For y = 0 To nCol
    
    'Make criteria for SQL string
    Select Case arrSearch(3, y)
        Case 10, 12        'Short text, long text
            If Me.chkAnyPart = -1 Then
                strSqlCriteria = " LIKE '*" & arrSearch(2, y) & "*' "
                Else
                strSqlCriteria = " LIKE '" & arrSearch(2, y) & "' "
                End If
        Case 8          'Date
            strSqlCriteria = " = #" & arrSearch(2, y) & "# "
        Case 4          'Number
            strSqlCriteria = Me.Controls("oprSearch" & arrSearch(0, y)).Caption & arrSearch(2, y) & " "
        Case Else
            strSqlCriteria = " = " & arrSearch(2, y) & " "
        End Select
    'Combine all parts of SQL string
    strSql = "SELECT frm_id FROM frm_search_" & arrSearch(0, y) & " WHERE " & arrSearch(1, y) & strSqlCriteria & _
                IIf(strIds = "", "", "AND frm_id  IN (" & strIds & ")")
    'Debug.Print strSql
    
    'Make new list of firm IDs ----------------------------------------------
    Set rstSearch = CurrentDb.OpenRecordset(strSql)
    strIds = ""
    Do While Not rstSearch.EOF
        strIds = strIds & IIf(strIds = "", "", ",") & rstSearch!frm_id
        rstSearch.MoveNext
        Loop
    rstSearch.Close
    Set rstSearch = Nothing
    'MsgBox strIDs
    If strIds = "" Then
        MsgBox "No match found"
        Exit Sub
        End If
    Next y
        
DoCmd.OpenForm "frm_firms", acNormal, , , , acHidden, "Search~" & strIds

cmdAdvSearch_Click_Exit:
    Exit Sub

cmdAdvSearch_Click_Err:
    MsgBox Error$
    Resume cmdAdvSearch_Click_Exit

End Sub

Private Sub cmdTransamountDraft_Click()
On Error GoTo cmdTransamountDraft_Click_Err

DoCmd.OpenForm "fin_transfersamount_u", acNormal, "", "", , acNormal, "Draft"


cmdTransamountDraft_Click_Exit:
    Exit Sub

cmdTransamountDraft_Click_Err:
    MsgBox Error$
    Resume cmdTransamountDraft_Click_Exit

End Sub



Private Sub cmdTransamountClosed_Click()
On Error GoTo cmdTransamountClosed_Click_Err

DoCmd.OpenForm "fin_transfersamount_u", acNormal, "", "", , acNormal, "Closed"


cmdTransamountClosed_Click_Exit:
    Exit Sub

cmdTransamountClosed_Click_Err:
    MsgBox Error$
    Resume cmdTransamountClosed_Click_Exit

End Sub

Private Sub cmdTransamountAdd_Click()
On Error GoTo cmdTransamountAdd_Click_Err

DoCmd.OpenForm "fin_transfersamount_detail", acNormal, "", "", , acNormal, "DataEntry"


cmdTransamountAdd_Click_Exit:
    Exit Sub

cmdTransamountAdd_Click_Err:
    MsgBox Error$
    Resume cmdTransamountAdd_Click_Exit

End Sub

Private Sub cmdAddAccount_Click()
On Error GoTo cmdAddAccount_Click_Err

    DoCmd.OpenForm "cen_heads_add", acNormal, "", "", , acNormal


cmdAddAccount_Click_Exit:
    Exit Sub

cmdAddAccount_Click_Err:
    MsgBox Error$
    Resume cmdAddAccount_Click_Exit

End Sub

Private Sub cmdOpenAccounts_Click()

DoCmd.OpenForm "cen_heads_pa_u", acNormal, "", "", , acHidden, "Open"

cmdOpenAccounts_Click_Exit:
    Exit Sub

cmdOpenAccounts_Click_Err:
    MsgBox Error$
    Resume cmdOpenAccounts_Click_Exit
End Sub

Private Sub cmdClosedAccounts_Click()
On Error GoTo cmdClosedAccounts_Click_Err

DoCmd.OpenForm "cen_heads_pa_u", acNormal, "", "", , acHidden, "Closed"

cmdClosedAccounts_Click_Exit:
    Exit Sub

cmdClosedAccounts_Click_Err:
    MsgBox Error$
    Resume cmdClosedAccounts_Click_Exit

End Sub

Private Sub cmdCalcSalaries_Click()
Dim SalariesCalculated As Integer

DoCmd.OpenForm "hr_attendance_u", , "", "", , acHidden, "~" & Date
SalariesCalculated = CalcSalReqs()
DoCmd.Close acForm, "hr_attendance_u"

If SalariesCalculated > 0 Then
    DoCmd.OpenForm "hr_salreqs_temp", , "", "", , acWindowNormal
    Else
    MsgBox "No salaries calculated.", vbCritical
    End If
End Sub

Private Sub cmdAttachSummary_Click()
DoCmd.OpenReport "pur_attachsummary", acViewPreview, "", "", acNormal
End Sub

Private Sub cmdRevDraft_Click()
On Error GoTo cmdRevDraft_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Draft"

cmdRevDraft_Click_Exit:
    Exit Sub

cmdRevDraft_Click_Err:
    MsgBox Error$
    Resume cmdRevDraft_Click_Exit

End Sub

Private Sub cmdRevOpen_Click()
On Error GoTo cmdRevOpen_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Open"

cmdRevOpen_Click_Exit:
    Exit Sub

cmdRevOpen_Click_Err:
    MsgBox Error$
    Resume cmdRevOpen_Click_Exit

End Sub

Private Sub cmdRevClosed_Click()
On Error GoTo cmdRevClosed_Click_Err

DoCmd.OpenForm "aud_revs_u", acNormal, "", "", , acHidden, "Closed"

cmdRevClosed_Click_Exit:
    Exit Sub

cmdRevClosed_Click_Err:
    MsgBox Error$
    Resume cmdRevClosed_Click_Exit

End Sub

Private Sub boxFalsePoCommitsAwaited_Click()

DoCmd.OpenForm "fin_commitments_u_pcs", acNormal, "", "", , acHidden, "FalseAwaited" & "~"

cmdPayPending_Click_Exit:
    Exit Sub

End Sub

Private Sub cmdOnCharge_Click()
On Error GoTo cmdOnCharge_Click_Err

DoCmd.OpenForm "ina_invatitems_u", acNormal, "", "", , acHidden, "Inventory~OnCharge"

cmdOnCharge_Click_Exit:
    Exit Sub

cmdOnCharge_Click_Err:
    MsgBox Error$
    Resume cmdOnCharge_Click_Exit

End Sub

Private Sub cmdChargedOff_Click()
On Error GoTo cmdChargedOff_Click_Err

DoCmd.OpenForm "ina_invatitems_u", acNormal, "", "", , acHidden, "Inventory~ChargedOff"

cmdChargedOff_Click_Exit:
    Exit Sub

cmdChargedOff_Click_Err:
    MsgBox Error$
    Resume cmdChargedOff_Click_Exit

End Sub

Private Sub cmdAssetsOnCharge_Click()
On Error GoTo cmdAssetsOnCharge_Click_Err

DoCmd.OpenForm "ina_invatitems_u", acNormal, "", "", , acHidden, "Assets~OnCharge"


cmdAssetsOnCharge_Click_Exit:
    Exit Sub

cmdAssetsOnCharge_Click_Err:
    MsgBox Error$
    Resume cmdAssetsOnCharge_Click_Exit

End Sub

Private Sub cmdAssetsChargedOff_Click()
On Error GoTo cmdAssetsChargedOff_Click_Err

DoCmd.OpenForm "ina_invatitems_u", acNormal, "", "", , acHidden, "Assets~ChargedOff"


cmdAssetsChargedOff_Click_Exit:
    Exit Sub

cmdAssetsChargedOff_Click_Err:
    MsgBox Error$
    Resume cmdAssetsChargedOff_Click_Exit

End Sub

Private Sub cmdExtCompense_Click()
On Error GoTo cmdExtCompense_Click_Err

DoCmd.OpenForm "fin_extcompenses_u", acNormal, "", "", , acNormal


cmdExtCompense_Click_Exit:
    Exit Sub

cmdExtCompense_Click_Err:
    MsgBox Error$
    Resume cmdExtCompense_Click_Exit

End Sub

Private Sub cmdSalVaraibles_Click()
On Error GoTo cmdSalVaraibles_Click_Err

DoCmd.OpenForm "fin_globalvars_sal", acNormal, "", "", , acNormal


cmdSalVaraibles_Click_Exit:
    Exit Sub

cmdSalVaraibles_Click_Err:
    MsgBox Error$
    Resume cmdSalVaraibles_Click_Exit

End Sub

Private Sub cmdXExpNet_Click()
On Error GoTo cmdXExpNet_Click_Err

DoCmd.OpenReport "fin_loans_net", acViewReport, "", "", acNormal


cmdXExpNet_Click_Exit:
    Exit Sub

cmdXExpNet_Click_Err:
    MsgBox Error$
    Resume cmdXExpNet_Click_Exit

End Sub

Private Sub cmdLoans_Click()
OpenLoansForm
End Sub

Private Sub cmdEdit_Click()
On Error GoTo cmdEdit_Click_Err

DoCmd.OpenForm "ina_invatitems_u_temp", acNormal, "", "", , acHidden

cmdEdit_Click_Exit:
    Exit Sub

cmdEdit_Click_Err:
    MsgBox Error$
    Resume cmdEdit_Click_Exit

End Sub

Private Sub cmdTransfersExist_Click()
On Error GoTo cmdTransfersExist_Click_Err

DoCmd.OpenForm "fin_inttransfers_u", acNormal, "", "", , acNormal

cmdTransfersExist_Click_Exit:
    Exit Sub

cmdTransfersExist_Click_Err:
    MsgBox Error$
    Resume cmdTransfersExist_Click_Exit

End Sub

Private Sub cmdTrend_Click()

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

Dim dbsTrend As Database
Dim qdfTrend As QueryDef
On Error GoTo cmdTrend_Click_Err

StartWait
Set dbsTrend = CurrentDb()
dbsTrend.Execute "Delete From fin_headtrend_temp"
Set qdfTrend = dbsTrend.QueryDefs("fin_headtrend_so_tempadder")
qdfTrend.Parameters(0) = Forms!vars!Parameter1
qdfTrend.Execute
Set qdfTrend = dbsTrend.QueryDefs("fin_headtrend_pc_tempadder")
qdfTrend.Parameters(0) = Forms!vars!Parameter1
qdfTrend.Execute
EndWait

DoCmd.OpenForm "fin_headtrend_chart", acNormal, "", "", , acNormal

cmdTrend_Click_Exit:
    Exit Sub

cmdTrend_Click_Err:
    MsgBox Error$
    Resume cmdTrend_Click_Exit

End Sub

Private Sub cmdEmployeesActive_Click()
On Error GoTo cmdEmployeesActive_Click_Err

    DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal


cmdEmployeesActive_Click_Exit:
    Exit Sub

cmdEmployeesActive_Click_Err:
    MsgBox Error$
    Resume cmdEmployeesActive_Click_Exit

End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_start_all_single"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Text
Option Explicit

Private Sub Form_Open(Cancel As Integer)
StartupSteps Me.Name
Me.Visible = True
End Sub

Private Sub Form_Activate()
SetWarningsGatePass
SetWarningsContract
SetWarningsPersonalData
SetWarningsClearance
SetMessagesMprStatus
End Sub

Private Sub Form_Close()
CloseDownSteps Me.Name
End Sub

Private Sub lblUser_Click()
DoCmd.OpenForm "cen_accounts_u", acNormal, "", "", , acNormal
End Sub

Private Sub cmdEmpAll_Click()
On Error GoTo cmdEmpAll_Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , , "Current~"

cmdEmpAll_Click_Exit:
    Exit Sub

cmdEmpAll_Click_Err:
    MsgBox Error$
    Resume cmdEmpAll_Click_Exit

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

Private Sub cmdPayrol_Click()
On Error GoTo cmdPayrol_Click_Err

    DoCmd.OpenReport "payroll", acViewPreview, "", "", acNormal

cmdPayrol_Click_Exit:
    Exit Sub

cmdPayrol_Click_Err:
    MsgBox Error$
    Resume cmdPayrol_Click_Exit

End Sub

Private Sub cmdAttend_Click()
On Error GoTo cmdAttend_Click_Err
Dim dbsAttend As Database
Dim rstAttend As Recordset
Dim dtAttend As Date

Set dbsAttend = CurrentDb()
'Generate this month's sheet if not yet generated
dtAttend = FirstDateThisMonth(Date)
Set rstAttend = dbsAttend.OpenRecordset("Select att_id from hr_attendance Where att_startdt = #" & dtAttend & "#")
If rstAttend.EOF = True Then makeAttendanceSheet (dtAttend)
rstAttend.Close

'If last person of dept left last month, open att_sheet in last month
Set rstAttend = dbsAttend.OpenRecordset("Select att_id from hr_attendance_u Where att_startdt = #" & dtAttend & "#")
If rstAttend.EOF = True Then dtAttend = FirstDatePrevMonth(dtAttend)
rstAttend.Close

'Open attendance sheet
DoCmd.OpenForm "hr_attendance_u", acNormal, "", "", , , "~" & Date

cmdAttend_Click_Exit:
    Exit Sub

cmdAttend_Click_Err:
    MsgBox Error$
    Resume cmdAttend_Click_Exit

End Sub

Private Sub boxGatePassNone_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnGatePassNone~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$
End Sub

Private Sub boxGatePassExpiry_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnGatePassExpiry~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$
End Sub

Private Sub boxContractNone_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnContractNone~"


Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxContractExpiry_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnContractExpiry~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxPersonala_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnPersonala~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxPersonalb_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnPersonalb~"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub boxClearance_Click()
On Error GoTo Click_Err

DoCmd.OpenForm "hr_emps_u", acNormal, "", "", , acHidden, "warnClearance"

Click_Exit:
    Exit Sub
Click_Err:
    MsgBox Error$

End Sub

Private Sub cmdPrepReturn_Click()
On Error GoTo cmdPrepReturn_Click_Err

CreateMprGroupIfRequired
DoCmd.OpenForm "prj_projects_mproverview", acNormal, "", "", , acNormal

cmdPrepReturn_Click_Exit:
    Exit Sub

cmdPrepReturn_Click_Err:
    MsgBox Error$
    Resume cmdPrepReturn_Click_Exit

End Sub

Private Sub cmdCompleteness_Click()
On Error GoTo cmdCompleteness_Click_Err

DoCmd.OpenForm "hr_completeness_u", acNormal, "", "", , acNormal

cmdCompleteness_Click_Exit:
    Exit Sub

cmdCompleteness_Click_Err:
    MsgBox Error$
    Resume cmdCompleteness_Click_Exit

End Sub

Private Sub cmdProjOpen_Click()

DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Open"


cmdProjOpen_Click_Exit:
    Exit Sub

cmdProjOpen_Click_Err:
    MsgBox Error$
    Resume cmdProjOpen_Click_Exit
End Sub

Private Sub cmdProjClosed_Click()
On Error GoTo cmdProjClosed_Click_Err

DoCmd.OpenForm "prj_projects", acNormal, "", , , acHidden, "Closed"


cmdProjClosed_Click_Exit:
    Exit Sub

cmdProjClosed_Click_Err:
    MsgBox Error$
    Resume cmdProjClosed_Click_Exit

End Sub

Private Sub cmdProjAdd_Click()
Dim ctl As Control
On Error GoTo cmdProjAdd_Click_Err

DoCmd.OpenForm "prj_projects_add", acNormal
cmdProjAdd_Click_Exit:
    Exit Sub

cmdProjAdd_Click_Err:
    MsgBox Error$
    Resume cmdProjAdd_Click_Exit

End Sub

Private Sub cmdSalReqsDraft_Click()
On Error GoTo cmdSalReqsDraft_Click_Err

DoCmd.OpenForm "hr_salreqs_u", acNormal, , , , acHidden, "Draft"

cmdSalReqsDraft_Click_Exit:
    Exit Sub
cmdSalReqsDraft_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsDraft_Click_Exit
    
End Sub

Private Sub cmdSalReqsInprocess_Click()
On Error GoTo cmdSalReqsInprocess_Click_Err

DoCmd.OpenForm "hr_salreqs_u", , "", "", , acHidden, "In Process"

cmdSalReqsInprocess_Click_Exit:
    Exit Sub
cmdSalReqsInprocess_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsInprocess_Click_Exit
    
End Sub

Private Sub cmdSalReqsCompleted_Click()
On Error GoTo cmdSalReqsCompleted_Click_Err

DoCmd.OpenForm "hr_salreqs_u", , "", "", , acHidden, "Closed"

cmdSalReqsCompleted_Click_Exit:
    Exit Sub
cmdSalReqsCompleted_Click_Err:
    MsgBox Error$
    Resume cmdSalReqsCompleted_Click_Exit
    
End Sub

Private Sub cmdDeptLead_Click()
On Error GoTo cmdDeptLead_Click_Err

DoCmd.OpenForm "cen_unit_u", acNormal, "", "", , acNormal

cmdDeptLead_Click_Exit:
    Exit Sub

cmdDeptLead_Click_Err:
    MsgBox Error$
    Resume cmdDeptLead_Click_Exit

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

Private Sub cmdGrades_Click()
On Error GoTo cmdGrades_Click_Err

DoCmd.OpenReport "hr_emps_gradewise_doc", acViewReport, "", "", acNormal

cmdGrades_Click_Exit:
    Exit Sub

cmdGrades_Click_Err:
    MsgBox Error$
    Resume cmdGrades_Click_Exit

End Sub

Private Sub cmdQualifications_Click()
On Error GoTo cmdQualifications_Click_Err

DoCmd.OpenReport "hr_emps_qualifwise_doc", acViewReport, "", "", acNormal

cmdQualifications_Click_Exit:
    Exit Sub

cmdQualifications_Click_Err:
    MsgBox Error$
    Resume cmdQualifications_Click_Exit

End Sub

Private Sub CmdPersonnel_Click()
On Error GoTo CmdPersonnel_Click_Err

DoCmd.OpenReport "hr_emps_u_active_doc", acViewReport, "", "", acNormal

CmdPersonnel_Click_Exit:
    Exit Sub

CmdPersonnel_Click_Err:
    MsgBox Error$
    Resume CmdPersonnel_Click_Exit

End Sub

Private Sub cmdSalariesPaid_Click()
On Error GoTo cmdSalariesPaid_Click_Err

DoCmd.OpenReport "fin_salariespaid-u_doc", acViewReport, "", "", acNormal

cmdSalariesPaid_Click_Exit:
    Exit Sub

cmdSalariesPaid_Click_Err:
    MsgBox Error$
    Resume cmdSalariesPaid_Click_Exit

End Sub

Private Sub cmdPurReqDraft_Click()
On Error GoTo cmdPurReqDraft_Click_Err

DoCmd.OpenForm "pur_purreqs_u", acNormal, "", "", , acHidden, "Draft"

cmdPurReqDraft_Click_Exit:
    Exit Sub

cmdPurReqDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurReqDraft_Click_Exit

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

Private Sub cmdPurReqNew_Click()
On Error GoTo cmdPurReqNew_Click_Err

DoCmd.OpenForm "pur_purreqs_detail", acNormal, "", "", acFormAdd, acWindowNormal, "DataEntry~"


cmdPurReqNew_Click_Exit:
    Exit Sub

cmdPurReqNew_Click_Err:
    MsgBox Error$
    Resume cmdPurReqNew_Click_Exit

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

Private Sub mdItemsStatus_Click()
On Error GoTo mdItemsStatus_Click_Err

    DoCmd.OpenReport "pur_itemsstatus", acViewReport, "", "", acNormal


mdItemsStatus_Click_Exit:
    Exit Sub

mdItemsStatus_Click_Err:
    MsgBox Error$
    Resume mdItemsStatus_Click_Exit

End Sub

Private Sub cmdPurRecDraft_Click()
On Error GoTo cmdPurRecDraft_Click_Err

    DoCmd.OpenForm "pur_purreceipts_u", acNormal, "", "", , acHidden, "Draft"


cmdPurRecDraft_Click_Exit:
    Exit Sub

cmdPurRecDraft_Click_Err:
    MsgBox Error$
    Resume cmdPurRecDraft_Click_Exit

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
On Error GoTo cmdAccStatus_Click_Err

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

DoCmd.OpenForm "fin_headstatus", acNormal, "", "", , acHidden

cmdAccStatus_Click_Exit:
    Exit Sub

cmdAccStatus_Click_Err:
    MsgBox Error$
    Resume cmdAccStatus_Click_Exit

End Sub

Private Sub cmdAccStatusBrief_Click()
On Error GoTo cmdAccStatusBrief_Click_Err

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

DoCmd.OpenForm "fin_headstatus_brief", acNormal, "", "", , acNormal


cmdAccStatusBrief_Click_Exit:
    Exit Sub

cmdAccStatusBrief_Click_Err:
    MsgBox Error$
    Resume cmdAccStatusBrief_Click_Exit

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

Private Sub cmdXExp_Click()
DoCmd.OpenReport "fin_loans", acViewReport, "", "", acNormal
End Sub


Private Sub cmdLoans_Click()
OpenLoansForm
End Sub

Private Sub cmdProjectShares_Click()

If IsNull(Me.cmbAccount) Then
    MsgBox "Please select an account.", vbCritical
    Exit Sub
    End If
Forms!vars!Parameter1 = Me.cmbAccount

DoCmd.OpenForm "fin_sharesinstall", acNormal, "", "", , acNormal

End Sub

Private Sub cmdPurCaseNew_Click()
On Error GoTo cmdPurCaseNew_Click_Err

DoCmd.OpenForm "pur_purcases_source", acNormal, "", "", , acNormal

cmdPurCaseNew_Click_Exit:
    Exit Sub

cmdPurCaseNew_Click_Err:
    MsgBox Error$
    Resume cmdPurCaseNew_Click_Exit

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

Private Sub cmdAllocations_Click()
OpenAllocationsReport "Open"
End Sub

Private Sub cmdAccountsStatus_Click()
OpenAccountsStatusReport "Open"
End Sub

Private Sub cmdProjSharesStatus_Click()
OpenProjectSharesReport "Open"
End Sub

Private Sub cmdChrf_Click()
OpenChrfReport "Open"
End Sub

Private Sub cmdSubheadsStatus_Click()
OpenSubheadsStatusReport "Open", getVar("varUnitId")
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

    Dim ctlSF As Control
    DoCmd.OpenForm "frm_firms", acNormal, "", "", , acNormal
    Forms!Firms.AllowEdits = True
    Forms!Firms.AllowAdditions = True
    Forms!Firms.AllowDeletions = True
    
    For Each ctlSF In Forms!Firms.Controls
        If ctlSF.ControlType = acSubform Then
            ctlSF.Form.AllowEdits = True
            ctlSF.Form.AllowAdditions = True
            ctlSF.Form.AllowDeletions = True
            End If
    Next
    
    Forms!Firms.lblTitle.Caption = "Firms (Edit / Add)"


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
Dim fld As field
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

'Only for generation of combo box row sources
Function PopulateACombo(strTableName As String) As String
Dim dbs As Database
Dim tbl As TableDef
Dim fld As field
Dim pty As Property
Dim strValues As String
Set dbs = CurrentDb
Set tbl = dbs.TableDefs(strTableName)

On Error Resume Next    'Because Value property doesn't have a value.
For Each fld In tbl.Fields
    For Each pty In fld.Properties
        If pty.Name = "Caption" Then strValues = strValues & fld.Name & ";" & pty.Value & ";" & fld.type & ";"
    Next
Next
PopulateACombo = strValues
On Error GoTo 0

End Function

Private Sub cmdMissing_Click()
On Error GoTo cmdMissing_Click_Err

    DoCmd.OpenForm "zpur_purcases_detail", acNormal, "", "", , acNormal


cmdMissing_Click_Exit:
    Exit Sub

cmdMissing_Click_Err:
    MsgBox Error$
    Resume cmdMissing_Click_Exit

End Sub

Private Sub cmdProg_Click()
On Error GoTo cmdProg_Click_Err

    DoCmd.OpenReport "zpur_purcases_current_summary", acViewReport, "", "", acNormal


cmdProg_Click_Exit:
    Exit Sub

cmdProg_Click_Err:
    MsgBox Error$
    Resume cmdProg_Click_Exit

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

Private Sub cmdEdit_Click()
On Error GoTo cmdEdit_Click_Err

    DoCmd.OpenForm "ina_invatitems_u_temp", acNormal, "", "", , acHidden


cmdEdit_Click_Exit:
    Exit Sub

cmdEdit_Click_Err:
    MsgBox Error$
    Resume cmdEdit_Click_Exit

End Sub

Private Sub cmdSharedAssets_Click()
On Error GoTo cmdSharedAssets_Click_Err

    DoCmd.OpenReport "ina_sharedassets", acViewReport, "", "", acNormal


cmdSharedAssets_Click_Exit:
    Exit Sub

cmdSharedAssets_Click_Err:
    MsgBox Error$
    Resume cmdSharedAssets_Click_Exit

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

Private Sub CmdCtrCasesDraft_Click()
On Error GoTo CmdCtrCasesDraft_Click_Err

DoCmd.OpenForm "hr_ctrcases_u", acNormal, "", "", , acNormal, "Draft~"

CmdCtrCasesDraft_Click_Exit:
    Exit Sub

CmdCtrCasesDraft_Click_Err:
    MsgBox Error$
    Resume CmdCtrCasesDraft_Click_Exit

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



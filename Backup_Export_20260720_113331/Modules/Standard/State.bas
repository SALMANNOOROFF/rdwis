Attribute VB_Name = "State"
Option Compare Database
Option Explicit

Public Sub SetStaticVariables(strAcc As Integer)
On Error GoTo Error_Handler
Dim dbsState As Database
Dim tdfState As TableDef
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set tdfState = dbsState.TableDefs("cen_units")
If tdfState.Connect Like "*DATABASE=rdw*" Then Forms!vars!varDatabase = "Production"
If tdfState.Connect Like "*DATABASE=dev*" Then Forms!vars!varDatabase = "Development"
If tdfState.Connect Like "*DATABASE=trn*" Then Forms!vars!varDatabase = "Training"
    
Set rstState = CurrentDb.OpenRecordset("SELECT cen_accounts.* FROM cen_accounts WHERE cen_accounts.acc_id = " & strAcc, dbOpenSnapshot)
Forms!vars!varAccId = rstState!acc_id
Forms!vars!varAccType = rstState!acc_type
Forms!vars!varAccUsername = rstState!acc_username
Forms!vars!varAccName = rstState!acc_name
Forms!vars!varAccTitle = rstState!acc_title
Forms!vars!varAccRank = rstState!acc_rank

Forms!vars!varRoleLevel = rstState!acc_level
Forms!vars!varRoleReportLevel = rstState!acc_reportlevel
Forms!vars!varRoleDesig = rstState!acc_desig
Forms!vars!varRoleDesigShort = rstState!acc_desigshort
Forms!vars!varRoleDesigType = rstState!acc_desigtype
Forms!vars!varRoleAccess = rstState!acc_access
Forms!vars!varRoleAuth = rstState!acc_auth

Forms!vars!varUnitId = rstState!acc_unt_id
Forms!vars!varUnitName = rstState!acc_untname
Forms!vars!varUnitNameShort = rstState!acc_untnamesh
Forms!vars!varUnitType = rstState!acc_unttype
Forms!vars!varUnitArea = rstState!acc_untarea
If rstState!acc_access = "multiple" Then
    Forms!vars!varLowerM = rstState!acc_lowerm
    Forms!vars!varUpperM = rstState!acc_upperm
    Else
    Forms!vars!varLowerM = rstState!acc_lowers
    Forms!vars!varUpperM = rstState!acc_uppers
    End If
Forms!vars!varLowerS = rstState!acc_lowers
Forms!vars!varUpperS = rstState!acc_uppers

Set rstState = CurrentDb.OpenRecordset("SELECT cen_units.* FROM cen_units WHERE unt_id = " & Forms!vars!varUnitId, dbOpenSnapshot)
Forms!vars!varUnitLeadName = rstState!unt_leadname
Forms!vars!varUnitLeadTitle = rstState!unt_leadtitle
Forms!vars!varUnitLeadRank = rstState!unt_leadrank
Forms!vars!varUnitLeadDesig = rstState!unt_leaddesig
Forms!vars!varUnitLeadDesigShort = rstState!unt_leaddesigshort
rstState.Close
Set rstState = Nothing
The_End:
Exit Sub

Error_Handler:
Resume Next
End Sub

Public Sub SetDynamicVariables(strLimits As String)
On Error GoTo Error_Handler

Select Case strLimits
    Case "s"
        Forms!vars!varmode = getVar("varRoleAuth") & "-s"
        Forms!vars!varLower = getVar("varLowerS")
        Forms!vars!varUpper = getVar("varUpperS")
    Case "m"
        Forms!vars!varmode = getVar("varRoleAuth") & "-m"
        Forms!vars!varLower = getVar("varLowerM")
        Forms!vars!varUpper = getVar("varUpperM")
    End Select

The_End:
Exit Sub

Error_Handler:
Resume Next
End Sub

Public Sub SetWarningsGatePass()
Dim dbsGPass As Database
Dim rstGPass As Recordset
Dim frmGPass As Form
On Error Resume Next

Set dbsGPass = CurrentDb()
Set rstGPass = dbsGPass.OpenRecordset("hr_warnGatePassNone")
Set frmGPass = Screen.ActiveForm
If rstGPass.EOF = False Then frmGPass.boxGatePassNone.BackColor = RGB(186, 20, 25)

Set rstGPass = dbsGPass.OpenRecordset("hr_warnGatePassExpiryNear")
If rstGPass.EOF = False Then frmGPass.boxGatePassExpiry.BackColor = RGB(255, 217, 102)
Set rstGPass = dbsGPass.OpenRecordset("hr_warnGatePassExpiryPassed")
If rstGPass.EOF = False Then frmGPass.boxGatePassExpiry.BackColor = RGB(186, 20, 25)

End Sub

Public Sub SetWarningsContract()
Dim dbsCtr As Database
Dim rstCtr As Recordset
Dim frmCtr As Form
On Error Resume Next

Set dbsCtr = CurrentDb()
Set rstCtr = dbsCtr.OpenRecordset("hr_warnContractNone")
Set frmCtr = Screen.ActiveForm
If rstCtr.EOF = False Then frmCtr.boxContractNone.BackColor = RGB(186, 20, 25)

Set rstCtr = dbsCtr.OpenRecordset("hr_warnContractExpiryNear")
If rstCtr.EOF = False Then frmCtr.boxContractExpiry.BackColor = RGB(255, 217, 102)
Set rstCtr = dbsCtr.OpenRecordset("hr_warnContractExpiryPassed")
If rstCtr.EOF = False Then frmCtr.boxContractExpiry.BackColor = RGB(186, 20, 25)

End Sub

Public Sub SetWarningsPersonalData()
Dim dbsPData As Database
Dim rstPData As Recordset
Dim frmPData As Form
On Error Resume Next

Set dbsPData = CurrentDb()
Set rstPData = dbsPData.OpenRecordset("hr_warnPersonala")
Set frmPData = Screen.ActiveForm
If rstPData.EOF = False Then frmPData.boxPersonala.BackColor = RGB(186, 20, 25)

Set rstPData = dbsPData.OpenRecordset("hr_warnPersonalb")
If rstPData.EOF = False Then frmPData.boxPersonalb.BackColor = RGB(186, 20, 25)

End Sub

Public Sub SetWarningsClearance()
Dim dbsClear As Database
Dim rstClear As Recordset
Dim frmClear As Form
On Error Resume Next

Set dbsClear = CurrentDb()
Set rstClear = dbsClear.OpenRecordset("hr_warnClearance")
Set frmClear = Screen.ActiveForm
If rstClear.EOF = False Then frmClear.boxClearance.BackColor = RGB(186, 20, 25)

End Sub

Public Sub SetWarningsFinance()
Dim dbsFin As Database
Dim rstFin As Recordset
Dim frmFin As Form
On Error Resume Next

Set dbsFin = CurrentDb()
Set rstFin = dbsFin.OpenRecordset("fin_warnunverifiedcontracts")
Set frmFin = Screen.ActiveForm
If rstFin.EOF = False Then
    frmFin.boxUnverifiedContracts.BackStyle = 1
    frmFin.boxUnverifiedContracts.BackColor = RGB(186, 20, 25)
    Else
    frmFin.boxUnverifiedContracts.BackStyle = 0
    End If

Set rstFin = dbsFin.OpenRecordset("fin_warnunassignedsalheads")
If rstFin.EOF = False Then
    frmFin.boxUnassignedSalHeads.BackStyle = 1
    frmFin.boxUnassignedSalHeads.BackColor = RGB(186, 20, 25)
    Else
    frmFin.boxUnassignedSalHeads.BackStyle = 0
    End If

Set rstFin = dbsFin.OpenRecordset("fin_warnunassignedsalheads")
If rstFin.EOF = False Then
    frmFin.boxUnassignedSalHeads.BackStyle = 1
    frmFin.boxUnassignedSalHeads.BackColor = RGB(186, 20, 25)
    Else
    frmFin.boxUnassignedSalHeads.BackStyle = 0
    End If

Set rstFin = dbsFin.OpenRecordset("fin_warn_falsepocommits_awaited")
If rstFin.EOF = False Then
    frmFin.boxFalsePoCommitsAwaited.BackStyle = 1
    frmFin.boxFalsePoCommitsAwaited.BackColor = RGB(186, 20, 25)
    Else
    frmFin.boxFalsePoCommitsAwaited.BackStyle = 0
    End If


End Sub

Public Sub SetMessagesMprStatus()
Dim dbsMprStatus As Database
Dim rstMprStatus As Recordset
Dim frmMprStatus As Form
Dim strStatus As String
Dim intTotal As Integer, intFinalized As Integer, intFinAwaited As Integer


Set dbsMprStatus = CurrentDb()
Set rstMprStatus = dbsMprStatus.OpenRecordset("Select * From prj_projects_mprsummary Order by semi_status Desc", dbOpenForwardOnly)
If rstMprStatus.EOF = True Then Exit Sub 'No Projects at all
Do While rstMprStatus.EOF = False
    strStatus = IIf(rstMprStatus!semi_status <> "Finalized", rstMprStatus!semi_status & "    " & rstMprStatus!project_count & vbCrLf & strStatus, strStatus)
    intTotal = intTotal + rstMprStatus!project_count
    intFinalized = IIf(rstMprStatus!semi_status = "Finalized", intFinalized + rstMprStatus!project_count, intFinalized)
    rstMprStatus.MoveNext
    Loop
intFinAwaited = intTotal - intFinalized
rstMprStatus.Close

strStatus = Replace(strStatus, "Receipt Awaited", "Receipt Awaited    ")
strStatus = Replace(strStatus, "Action Awaited", "Action Awaited       ")
strStatus = Replace(strStatus, "Forwarded", "Forwarded                ")
strStatus = Replace(strStatus, "Finalized", "Finalized                    ")

Set frmMprStatus = Screen.ActiveForm

If intFinAwaited <> 0 Then
    frmMprStatus.txtStatusSummary = "Finalization awaited   " & intFinAwaited & " of " & intTotal
    frmMprStatus.txtStatusDetails = strStatus
    frmMprStatus.txtStatusSummary.Visible = True
    frmMprStatus.txtStatusDetails.Visible = True
    Else
    frmMprStatus.txtStatusSummary.Visible = False
    frmMprStatus.txtStatusDetails.Visible = False
    End If

The_End:
Exit Sub
Error_Handler:
GoTo The_End

End Sub

Public Function getVar(varVarName As Variant) As Variant
getVar = Forms!vars(varVarName)
End Function

Public Function getVarGlobal(strVarName As String) As Variant
Dim dbsAtt As Database
Dim rstAtt As Recordset

Set dbsAtt = CurrentDb()
Set rstAtt = dbsAtt.OpenRecordset("Select * From cen_globalvars Where gvar_name = '" & strVarName & "'", dbOpenSnapshot)
Select Case rstAtt!gvar_type
    Case "int2": getVarGlobal = Nz(CInt(rstAtt!gvar_value), 0)
    Case "int4": getVarGlobal = Nz(CLng(rstAtt!gvar_value), 0)
    Case "date": getVarGlobal = Nz(CDate(rstAtt!gvar_value), 0)
    Case "boolean": getVarGlobal = Nz(CBool(rstAtt!gvar_value), 0)
    Case Else: getVarGlobal = Nz(rstAtt!gvar_value, "")
    End Select

End Function

'Used manually to set or create & set 'FrontendType' property of this frontend
Private Sub SetFrontEndType()
Dim dbs As Database
Dim prp As Property
Set dbs = CurrentDb

'Use this code...
Set prp = dbs.CreateProperty("FrontendType", dbText, "fe_projects")
dbs.Properties.Append prp
'-----------------

'...or this code
'dbs.Properties("FrontEndType") = "fe_projects"
'-----------------

MsgBox "Done."
End Sub

'Used manually to see value of 'FrontendType' property of this frontendPrivate
Private Sub getFrontEndType()
Dim dbsProp As Database
Dim strProp As String

Set dbsProp = CurrentDb
strProp = dbsProp.Properties("FrontendType").Name & " = " & dbsProp.Properties("FrontendType")
Debug.Print strProp
End Sub

'Used manually to delete a database property
Private Sub DeleteFrontEndType()
Dim dbs As Database
Dim prp As Property
Set dbs = CurrentDb
dbs.Properties.Delete ("xxxx")
MsgBox "Done."
End Sub




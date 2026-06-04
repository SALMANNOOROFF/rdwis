Attribute VB_Name = "Openings"
Option Compare Database
Option Explicit

Public Sub OpenLoansForm()
Dim dbsLoans As Database
Dim qdfLoans As QueryDef

StartWait
Set dbsLoans = CurrentDb()
dbsLoans.Execute "Delete From fin_loans_detail1_temp"
Set qdfLoans = dbsLoans.QueryDefs("fin_loans_detail1_tempadder")
qdfLoans.Execute
EndWait

DoCmd.OpenForm "fin_loans_detail", acNormal, "", "", , acNormal
End Sub

Private Sub SetBaseQueries(status As String, Unit As Long)
Dim dbsBase As Database
Dim qdfBase As QueryDef
Dim strSql As String

' Set cen_heads_base
strSql = "Select * From cen_heads Where hed_type = 'Project'"
Select Case status
    Case "Open"
    strSql = strSql & " And IsNull([hed_closedt])"
    Case "Closed"
    strSql = strSql & " And Not IsNull([hed_closedt])"
    End Select
Select Case Unit
    Case 0
    strSql = strSql & " And hed_unt_id >= " & getVar("varLower") & " And hed_unt_id <= " & getVar("varUpper")
    Case Else
    strSql = strSql & " And hed_unt_id = " & Unit
    End Select
Set dbsBase = CurrentDb()
Set qdfBase = dbsBase.QueryDefs("cen_heads_base")
qdfBase.sql = strSql

' Set cen_units_base
strSql = "Select * From cen_units Where unt_type = 'Division'"
Select Case Unit
    Case 0
    strSql = strSql & " And unt_id >= " & getVar("varLower") & " And unt_id <= " & getVar("varUpper")
    Case Else
    strSql = strSql & " And unt_id = " & Unit
    End Select
Set qdfBase = dbsBase.QueryDefs("cen_units_base")
qdfBase.sql = strSql

End Sub

Sub Doo()
SetBaseQueries "Closed", 250000
End Sub

Public Sub OpenAllocationsReport(status As String, Optional Unit As Long)
Dim dbsAllocStatus As Database
Dim qdfAllocStatus As QueryDef

StartWait
SetBaseQueries status, Unit
Set dbsAllocStatus = CurrentDb()
dbsAllocStatus.Execute "Delete From fin_headstatusall_alloc_temp"
Set qdfAllocStatus = dbsAllocStatus.QueryDefs("fin_headstatusall_open_alloc_temp_adder")
qdfAllocStatus.Execute
EndWait

Select Case Unit
    Case 0: DoCmd.OpenReport "fin_headstatusall_open_alloc", acViewReport, , , acWindowNormal, "Exact"
    Case Else: DoCmd.OpenReport "fin_headstatusall_open_alloc_div", acViewReport, , , acWindowNormal, "Exact"
    End Select
End Sub

Public Sub OpenAccountsStatusReport(status As String, Optional Unit As Long)
Dim dbsAccStatus As Database
Dim qdfAccStatus As QueryDef

StartWait
SetBaseQueries status, Unit
Set dbsAccStatus = CurrentDb()
dbsAccStatus.Execute "Delete From fin_headstatusall_temp"
Set qdfAccStatus = dbsAccStatus.QueryDefs("fin_headstatusall_open_temp_acc_adder")
qdfAccStatus.Execute
EndWait

If getVar("varRoleAccess") = "Multiple" And Unit = 0 Then
    DoCmd.OpenReport "fin_headstatusall_open_acc", acViewReport, , , acWindowNormal, "Exact"
    Else
    DoCmd.OpenReport "fin_headstatusall_open_acc_div", acViewReport, , , acWindowNormal, "Exact"
    End If
End Sub

Public Sub OpenProjectSharesReport(status As String, Optional Unit As Long)
Dim dbsPccStatus As Database
Dim qdfPccStatus As QueryDef

StartWait
SetBaseQueries status, Unit
Set dbsPccStatus = CurrentDb()
dbsPccStatus.Execute "Delete From fin_headstatusall_temp"
Set qdfPccStatus = dbsPccStatus.QueryDefs("fin_headstatusall_open_temp_pcc_adder")
qdfPccStatus.Execute
EndWait

If getVar("varRoleAccess") = "Multiple" And Unit = 0 Then
    DoCmd.OpenReport "fin_headstatusall_open_pcc", acViewReport, , , acWindowNormal, "Exact"
    Else
    DoCmd.OpenReport "fin_headstatusall_open_pcc_div", acViewReport, , , acWindowNormal, "Exact"
    End If
End Sub

Public Sub OpenChrfReport(status As String, Optional Unit As Long)
Dim dbsChrf As Database
Dim qryChrf As QueryDef
Dim n As Integer

StartWait
SetBaseQueries status, Unit
Set dbsChrf = CurrentDb()
dbsChrf.Execute "Delete From fin_headstatusall_temp"
Set qryChrf = dbsChrf.QueryDefs("fin_headstatusall_open_temp_chrf_adder")
qryChrf.Execute
EndWait

If getVar("varRoleAccess") = "Multiple" And Unit = 0 Then
    DoCmd.OpenReport "fin_headstatusall_open_chrf", acViewReport, , , acWindowNormal, "Exact"
    Else
    DoCmd.OpenReport "fin_headstatusall_open_chrf_div", acViewReport, , , acWindowNormal, "Exact"
    End If
End Sub

Public Sub OpenSubheadsStatusReport(status As String, Unit As Long)
Dim dbsShStatus As Database
Dim qdfShStatus As QueryDef

StartWait
Set dbsShStatus = CurrentDb()
dbsShStatus.Execute "Delete From fin_headstatusall_temp"
Set qdfShStatus = dbsShStatus.QueryDefs("fin_headstatusall_open_temp_subheads_adder")
qdfShStatus.Parameters(0) = status
qdfShStatus.Parameters(1) = Unit
qdfShStatus.Execute
Set qdfShStatus = dbsShStatus.QueryDefs("fin_headstatusall_open_temp_ua_adder")
qdfShStatus.Parameters(0) = Unit
qdfShStatus.Execute
EndWait

DoCmd.OpenReport "fin_headstatusall_open_subheads_a_div", acViewReport, , , acWindowNormal, "Exact"
End Sub

Public Sub OpenMonthlyReport()
Forms!vars!Parameter1 = FirstDatePrevMonth(Date)
Forms!vars!Parameter2 = LastDatePrevMonth(Date)
DoCmd.OpenReport "fin_headstatusall_mreturn", acViewReport, "", "", acNormal
End Sub

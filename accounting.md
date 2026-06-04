Option Compare Database
Option Explicit

'*********************************************************************************
'Summary**************************************************************************
''***General
'GetAccAllocation(HeadId)
'GetAccMtssShare(HeadId)
'GetAccShares(HeadId)
'    (1) = pcc
'    (2) = cf
'    (3) = prj
'    (4) = prj_sal
'    (5) = prj_pur
'    (6) = transtype

'GetPccCfReceived(HeadId)
'    (4) = pcc
'    (5) = cf

''***Account
'GetAccReceived(HeadId)
'GetAccExpenditure(HeadId)
'GetAccOutstandingCommits(HeadId)
'GetAccInProcess(HeadId)

''***Recievables
'GetAccReceivableMsnCompleted(HeadId)
'GetAccReceivableMsnCurrent(HeadId)

''***Loans
'GetPccLoansGiven(HeadId)
'GetOthersLoansTaken(HeadId)
'GetPccOwnExp(HeadId)

''***P-Account
'GetPccReceived(HeadId)     'Duplication here. See
'GetPccExpenditure(HeadId)
'GetPccOutstandingCommits(HeadId)
'GetPccInProcess(HeadId)

''***CSRF
'GetCfReceived(HeadId)      'Duplication here. See GetPccCfReceived
'GetCfExpenditure(HeadId)
'GetCfOutstandingCommits(HeadId)
'GetCfInProcess(HeadId)

''***Project
'GetPrjExpenditure(HeadId)
'GetPrjOutstandingCommits(HeadId)
'GetPrjInProcess(HeadId)

''***Project Subheads
'GetPrjAllocationsShd(HeadId)
'GetPrjExpenditureShd(HeadId)
'GetPrjOutstandingCommitsShd(HeadId)
'GetPrjInProcessShd(HeadId)

''***Salary Forecast
'GetPrjSalForecast(HeadUntId)
'GetUaSalForecast(UnitId)
'GetPrjSalUnderway(HeadUntId)
'GetCfSalForecast()
'GetCfSalUnderway()

''*********************************************************************************
'GetA16Element(FuncName, Head, intPos)
'GetSubHeadFigure(FuncName, Head, Subhead)
'GetHeadStatus(HeadId, Scope) As Scripting.Dictionary
''Scope - "gavpxcljzmro"
'' g - General
'' a - Account
'' v - Receivables
'' l - Loans
'' p - Pcc
'' c - CSRF
'' j - Project
'' s - Project Subheads
'' o - Others

'Public SalaryTax(CtrSalary, BaseSalary As Long) As Long

'***********************************************************************************
'***********************************************************************************


'General ****************************************************************************

Function GetAccAllocation(HeadId As Long) As Double
Dim dbsState As Database
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set rstState = dbsState.OpenRecordset("Select trf_amount from fin_transfers where trf_type = 'FI' and trf_title = 'Project Funding' and trf_tohed = " & HeadId)
If Not rstState.EOF Then GetAccAllocation = rstState!trf_amount
rstState.Close
Set rstState = Nothing
End Function

Function GetAccMtssShare(HeadId As Long) As Double
Dim dbsState As Database
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set rstState = dbsState.OpenRecordset("Select trf_amount from fin_transfers where trf_type = 'FO' and trf_title = 'MTSS Share' and trf_fromhed = " & HeadId)
If rstState.EOF Then Exit Function
GetAccMtssShare = rstState!trf_amount
rstState.Close
Set rstState = Nothing
End Function

Function GetAccShares(HeadId As Long) As Variant()
Dim dbsState As Database
Dim rstState As Recordset
Dim arrOutput(1 To 6) As Variant

Set dbsState = CurrentDb()
Set rstState = dbsState.OpenRecordset("Select * from fin_sharesalloc where sha_hed_id = " & HeadId, dbOpenSnapshot)
If Not rstState.EOF Then
    arrOutput(1) = rstState!sha_pcc
    arrOutput(2) = rstState!sha_cf
    arrOutput(3) = rstState!sha_prj
    arrOutput(6) = rstState!sha_transtype
End If
GetAccShares = arrOutput
rstState.Close
Set rstState = Nothing
End Function

Function GetPccCfReceived(HeadId As Long) As Variant()
Dim dbsState As Database
Dim rstState As Recordset
Dim arrOutput(1 To 6) As Variant

Set dbsState = CurrentDb()
Set rstState = dbsState.OpenRecordset("SELECT Sum(shi_pcc) AS SumPcc, Sum(shi_cf) AS SumCf " & _
                                      "FROM fin_sharesinstall WHERE shi_hed_id = " & HeadId, dbOpenSnapshot)
arrOutput(4) = Nz(rstState!SumPcc, 0)
arrOutput(5) = Nz(rstState!SumCf, 0)
GetPccCfReceived = arrOutput
rstState.Close
Set rstState = Nothing
End Function

'Account ****************************************************************************

Function GetAccReceived(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sto_acc_received")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccReceived = rstState!sumOfAmount
rstState.Close
Set rstState = Nothing
End Function

Function GetAccExpenditure(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_acc_exp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccExpenditure = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetAccOutstandingCommits(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_acc_commitsoutst")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccOutstandingCommits = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetAccInProcess(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_acc_ipc")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccInProcess = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing
End Function

'Recievables ****************************************************************************

Function GetAccReceivableMsnCompleted(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_acc_rcvmsncompleted")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccReceivableMsnCompleted = Nz(rstState!amount, 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetAccReceivableMsnCurrent(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_acc_rcvmsncurrent")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetAccReceivableMsnCurrent = Nz(rstState!amount, 0)
rstState.Close
Set rstState = Nothing
End Function

'Loans ****************************************************************************

Function GetPccLoansGiven(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_pcc_loansgiven")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccLoansGiven = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetOthersLoansTaken(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_others_loanstaken")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetOthersLoansTaken = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

'P-Account ************************************************************************

Function GetPccReceived(HeadId As Long) As Double   'Duplication here. See GetPccCfReceived
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sto_pcc_received")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccReceived = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing

End Function

Function GetPccExpenditure(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_pcc_exp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccExpenditure = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetPccOutstandingCommits(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sto_pcc_commitsoutst")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccOutstandingCommits = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetPccInProcess(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_pcc_ipc")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccInProcess = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetPccOwnExp(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_pcc_ownexp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPccOwnExp = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

'CSRF ******************************************************************************************

Function GetCfReceived(HeadId As Long) As Double        'Duplication here. See GetPccCfReceived
Dim dbsState As Database
Dim rstState As Recordset

Set dbsState = CurrentDb()
Set rstState = dbsState.OpenRecordset("SELECT Sum(shi_cf) AS SumOfamount FROM fin_sharesinstall WHERE shi_hed_id = " & HeadId, dbOpenSnapshot)
If rstState.EOF Then Exit Function
GetCfReceived = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetCfExpenditure(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_cf_exp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetCfExpenditure = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetCfOutstandingCommits(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_cf_commitsoutst")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetCfOutstandingCommits = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetCfInProcess(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_cf_ipc")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetCfInProcess = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing
End Function

'Project **************************************************************************

Function GetPrjExpenditure(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_prj_exp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPrjExpenditure = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetPrjOutstandingCommits(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_prj_commitsoutst")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPrjOutstandingCommits = Nz(rstState!sumOfAmount * (-1), 0)
rstState.Close
Set rstState = Nothing
End Function

Function GetPrjInProcess(HeadId As Long) As Double
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Set dbsState = CurrentDb()

Set qdfState = dbsState.QueryDefs("fin_sto_prj_ipc")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then GetPrjInProcess = Nz(rstState!sumOfAmount, 0)
rstState.Close
Set rstState = Nothing
End Function

'Project Subheads ******************************************************************

Function GetPrjAllocationsShd(HeadId As Long) As Variant()
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Dim arrOutput(1, 1 To 5) As Variant
Dim n As Integer

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_subheads_sorted")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then
    n = 1
    Do While Not rstState.EOF
        arrOutput(0, n) = rstState!sbh_name
        arrOutput(1, n) = rstState!sbh_alloc
        n = n + 1
        rstState.MoveNext
        Loop
    End If
rstState.Close
Set rstState = Nothing
GetPrjAllocationsShd = arrOutput
End Function

Function GetPrjExpenditureShd(HeadId As Long) As Variant()
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Dim arrOutput(1, 1 To 5) As Variant
Dim n As Integer

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sts_prj_exp")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then
    n = 1
    Do While Not rstState.EOF
        arrOutput(0, n) = rstState!Subhead
        arrOutput(1, n) = rstState!sumOfAmount * (-1)
        n = n + 1
        rstState.MoveNext
        Loop
    End If
rstState.Close
Set rstState = Nothing
GetPrjExpenditureShd = arrOutput
End Function

Function GetPrjOutstandingCommitsShd(HeadId As Long) As Variant()
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Dim arrOutput(1, 1 To 5) As Variant
Dim n As Integer

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sts_prj_commitsoutst")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then
    n = 1
    Do While Not rstState.EOF
        arrOutput(0, n) = rstState!Subhead
        arrOutput(1, n) = rstState!sumOfAmount * (-1)
        n = n + 1
        rstState.MoveNext
        Loop
    End If
rstState.Close
Set rstState = Nothing
GetPrjOutstandingCommitsShd = arrOutput
End Function

Function GetPrjInProcessShd(HeadId As Long) As Variant()
Dim dbsState As Database
Dim qdfState As QueryDef
Dim rstState As Recordset
Dim arrOutput(1, 1 To 5) As Variant
Dim n As Integer

Set dbsState = CurrentDb()
Set qdfState = dbsState.QueryDefs("fin_sts_prj_ipc")
qdfState.Parameters(0) = HeadId
Set rstState = qdfState.OpenRecordset(dbOpenSnapshot, dbReadOnly)
If Not rstState.EOF Then
    n = 1
    Do While Not rstState.EOF
        arrOutput(0, n) = rstState!Subhead
        arrOutput(1, n) = rstState!sumOfAmount
        n = n + 1
        rstState.MoveNext
        Loop
    End If
rstState.Close
Set rstState = Nothing
GetPrjInProcessShd = arrOutput
End Function

'Salary Forecast *******************************************************************************
Function GetPrjSalForecast(HeadUntId As Long) As Double
Dim dbsState As Database
Dim rstState As Recordset
Dim rstSal As Recordset
Dim qdfSal As QueryDef
Dim arrSal() As Variant
Dim lngSal As Long
Dim dtLastSalMonth As Date, dtMonth As Date
Dim n As Integer
Dim s As Integer

Set dbsState = CurrentDb()
dbsState.Execute "Delete From fin_salforecast_temp"
Set rstState = dbsState.OpenRecordset("fin_salforecast_temp")

Set qdfSal = dbsState.QueryDefs("fin_lastsalarymonth")
qdfSal.Parameters(0) = HeadUntId
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)
dtLastSalMonth = IIf(IsNull(rstSal!max_month), #1/1/1900#, rstSal!max_month)
    
Set qdfSal = dbsState.QueryDefs("fin_salforecast")
qdfSal.Parameters(0) = dtLastSalMonth
qdfSal.Parameters(1) = HeadUntId
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)

s = 0
Do While Not rstSal.EOF
    arrSal = GetSalaryMatrix(rstSal!ctr_num, rstSal!cpn_effenddt)
    For n = 8 To 10
        If IsEmpty(arrSal(n, 1)) Then Exit For
        lngSal = arrSal(n, 13)
        s = s + 1
        If s = 1 Then lngSal = lngSal + CalculateArrDues(rstSal!ctr_num, rstSal!cpn_effenddt)
        With rstState
            .AddNew
            !id = s
            !ctr_id = rstSal!ctr_id
            !ctr_num = rstSal!ctr_num
            !emp_name = rstSal!emp_name
            !cpn_effstartdt = rstSal!cpn_effstartdt
            !cpn_effenddt = rstSal!cpn_effenddt
            !sal_month = rstSal!sal_month
            !cpn_hed_id = rstSal!cpn_hed_id
            !ctr_salary = rstSal!ctr_salary
            !Salary = lngSal
            .Update
            End With
        Next n
    rstSal.MoveNext
    Loop

Set rstState = dbsState.OpenRecordset("Select Sum(salary) AS sum_salary " & _
                                        "From fin_salforecast_temp", dbOpenSnapshot)
If Not rstState.EOF Then GetPrjSalForecast = Nz(rstState!sum_salary, 0)
End Function

Function GetUaSalForecast(UnitId As Long) As Double
Dim dbsState As Database
Dim rstState As Recordset
Dim rstSal As Recordset
Dim qdfSal As QueryDef
Dim arrSal() As Variant
Dim lngSal As Long
Dim dtMonth As Date
Dim n As Integer
Dim s As Integer

Set dbsState = CurrentDb()
dbsState.Execute "Delete From fin_salforecast_temp"
Set rstState = dbsState.OpenRecordset("fin_salforecast_temp")

Set qdfSal = dbsState.QueryDefs("fin_salforecast_ua")
qdfSal.Parameters(0) = UnitId
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)

s = 0
Do While Not rstSal.EOF
    arrSal = GetSalaryMatrix(rstSal!ctr_num, rstSal!cpn_effenddt)
    For n = 8 To 10
        If IsEmpty(arrSal(n, 1)) Then Exit For
        lngSal = arrSal(n, 13)
        s = s + 1
        'If s = 1 Then lngSal = lngSal + CalculateArrDues(rstSal!ctr_num, rstSal!cpn_effenddt)
        With rstState
            .AddNew
            !id = s
            !ctr_id = rstSal!ctr_id
            !ctr_num = rstSal!ctr_num
            !emp_name = rstSal!emp_name
            !cpn_effstartdt = rstSal!cpn_effstartdt
            !cpn_effenddt = rstSal!cpn_effenddt
            !cpn_hed_id = rstSal!cpn_hed_id
            '!today = rstSal!today
            !ctr_salary = rstSal!ctr_salary
            !Salary = lngSal
            .Update
            End With
        Next n
    rstSal.MoveNext
    Loop

Set rstState = dbsState.OpenRecordset("Select Sum(salary) AS sum_salary " & _
                                        "From fin_salforecast_temp", dbOpenSnapshot)
If Not rstState.EOF Then GetUaSalForecast = Nz(rstState!sum_salary, 0)
End Function

Function GetPrjSalUnderway(HeadUntId As Long) As Double
Dim dbsState As Database
Dim rstState As Recordset
Dim rstSal As Recordset
Dim qdfSal As QueryDef
Dim arrSal() As Variant
Dim lngSal As Long
Dim dtMonth As Date
Dim n As Integer
Dim s As Integer

Set dbsState = CurrentDb()
dbsState.Execute "Delete From fin_salforecast_temp"
Set rstState = dbsState.OpenRecordset("fin_salforecast_temp")
Set qdfSal = dbsState.QueryDefs("fin_salunderway")
qdfSal.Parameters(0) = HeadUntId
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)

s = 0
Do While Not rstSal.EOF
        With rstState
            .AddNew
            !id = s
            !ctr_id = rstSal!ctc_id
            !ctr_num = rstSal!ctc_emp_id
            !emp_name = rstSal!ctc_empnamecomp
            !cpn_effstartdt = rstSal!ccp_effstartdt
            !cpn_effenddt = rstSal!ccp_effenddt
            !sal_month = rstSal!sal_month
            !cpn_hed_id = rstSal!ccp_hed_id
            !ctr_salary = rstSal!ctc_newsalary
            !Salary = rstSal!ctc_newsalary / _
                      (LastDateThisMonth(rstSal!ccp_effstartdt) - FirstDateThisMonth(rstSal!ccp_effstartdt) + 1) * _
                      (rstSal!ccp_effenddt - rstSal!ccp_effstartdt + 1)
            .Update
            End With
    rstSal.MoveNext
    Loop

Set rstState = dbsState.OpenRecordset("Select Sum(salary) AS sum_salary " & _
                                        "From fin_salforecast_temp", dbOpenSnapshot)
If Not rstState.EOF Then GetPrjSalUnderway = Nz(rstState!sum_salary, 0)
End Function

Function GetCfSalForecast() As Double
Dim dbsState As Database
Dim rstState As Recordset
Dim rstSal As Recordset
Dim qdfSal As QueryDef
Dim arrSal() As Variant
Dim lngSal As Long
Dim dtLastSalMonth As Date, dtMonth As Date
Dim n As Integer
Dim s As Integer

Set dbsState = CurrentDb()
dbsState.Execute "Delete From fin_salforecast_temp"
Set rstState = dbsState.OpenRecordset("fin_salforecast_temp")

Set qdfSal = dbsState.QueryDefs("fin_lastsalarymonth_cf")
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)
dtLastSalMonth = IIf(IsNull(rstSal!max_month), #1/1/1900#, rstSal!max_month)

Set qdfSal = dbsState.QueryDefs("fin_salforecast_cf")
qdfSal.Parameters(0) = dtLastSalMonth
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)

s = 0
Do While Not rstSal.EOF
    dtMonth = FirstDateThisMonth(qdfSal.Parameters(0))
    Do While dtMonth <= rstSal!ctr_effenddt
        arrSal = GetSalaryMatrix(rstSal!ctr_num, dtMonth)
        lngSal = arrSal(11, 13)
        s = s + 1
        If s = 1 Then lngSal = lngSal + CalculateArrDues(rstSal!ctr_num, dtMonth)
        With rstState
            .AddNew
            !id = s
            !ctr_id = rstSal!ctr_id
            !ctr_num = rstSal!ctr_num
            !emp_name = rstSal!emp_name
            !cpn_effstartdt = IIf(rstSal!ctr_effstartdt > dtMonth, rstSal!ctr_effstartdt, dtMonth)
            !cpn_effenddt = IIf(rstSal!ctr_effenddt < LastDateThisMonth(dtMonth), rstSal!ctr_effenddt, LastDateThisMonth(dtMonth))
            !sal_month = dtMonth
            !today = rstSal!today
            !ctr_salary = rstSal!ctr_salary
            !Salary = lngSal
            .Update
            End With
        dtMonth = DateAdd("m", 1, dtMonth)
        Loop
    rstSal.MoveNext
    Loop

Set rstState = dbsState.OpenRecordset("Select Sum(salary) AS sum_salary " & _
                                        "From fin_salforecast_temp", dbOpenSnapshot)
If Not rstState.EOF Then GetCfSalForecast = Nz(rstState!sum_salary, 0)
End Function

Function GetCfSalUnderway() As Double
Dim dbsState As Database
Dim rstState As Recordset
Dim rstSal As Recordset
Dim qdfSal As QueryDef
Dim arrSal() As Variant
Dim lngSal As Long
Dim dtMonth As Date
Dim n As Integer
Dim s As Integer

Set dbsState = CurrentDb()
dbsState.Execute "Delete From fin_salforecast_temp"
Set rstState = dbsState.OpenRecordset("fin_salforecast_temp")
Set qdfSal = dbsState.QueryDefs("fin_salunderway_cf")
Set rstSal = qdfSal.OpenRecordset(dbOpenSnapshot, dbReadOnly)

s = 0
Do While Not rstSal.EOF
        With rstState
            .AddNew
            !id = s
            !ctr_id = rstSal!ctc_id
            !ctr_num = rstSal!ctc_emp_id
            !emp_name = rstSal!ctc_empnamecomp
            !cpn_effstartdt = rstSal!ccp_effstartdt
            !cpn_effenddt = rstSal!ccp_effenddt
            !sal_month = rstSal!sal_month
            !cpn_hed_id = rstSal!ccp_hed_id
            !ctr_salary = rstSal!ctc_newsalary
            !Salary = rstSal!ctc_newsalary / _
                      (LastDateThisMonth(rstSal!ccp_effstartdt) - FirstDateThisMonth(rstSal!ccp_effstartdt) + 1) * _
                      (rstSal!ccp_effenddt - rstSal!ccp_effstartdt + 1)
            .Update
            End With
    rstSal.MoveNext
    Loop

Set rstState = dbsState.OpenRecordset("Select Sum(salary) AS sum_salary " & _
                                        "From fin_salforecast_temp", dbOpenSnapshot)
If Not rstState.EOF Then GetCfSalUnderway = Nz(rstState!sum_salary, 0)
End Function


'************************************************************************************
'************************************************************************************

Public Function GetA16Element(FuncName As String, head As Long, intPos As Integer) As Variant
Dim arrGArray() As Variant
arrGArray = Application.Run(FuncName, head)
If IsNull(arrGArray(intPos)) Then Exit Function
GetA16Element = CLng(arrGArray(intPos))
End Function

Public Function GetSubHeadFigure(FuncName As String, head As Long, Subhead As String) As Variant
Dim arrSArray() As Variant
Dim n As Integer

arrSArray = Application.Run(FuncName, head)
For n = LBound(arrSArray, 2) To UBound(arrSArray, 2)
    If arrSArray(0, n) = Subhead Then
        GetSubHeadFigure = arrSArray(1, n)
        Exit For
        End If
    Next n
End Function

Sub Temp()
Dim arr As Variant
arr = GetPrjSalUnderway(200014)
Debug.Print arr
End Sub

Public Function GetHeadStatus(HeadId As Long, Scope As String) As Scripting.Dictionary
Dim arrInput() As Variant
Dim arrSubheads(4, 1 To 5) As Variant
Dim x As Long, y As Long
Dim dcnStatus As Scripting.Dictionary

'Scope - "gavlpcjso"
' g - General
' a - Account
' v - Receivables
' l - Loans
' p - Pcc
' c - CSRF
' j - Project
' s - Project Subheads
' o - Others

Set dcnStatus = New Scripting.Dictionary

General:
If Not Scope Like "*g*" Then GoTo Account
dcnStatus.Add "HeadId", HeadId
dcnStatus.Add "HeadCode", CodeFromHeadId(HeadId)
dcnStatus.Add "AccAllocation", GetAccAllocation(HeadId)
dcnStatus.Add "MtssShare", GetAccMtssShare(HeadId)
dcnStatus.Add "RdwShare", dcnStatus("AccAllocation") - dcnStatus("MtssShare")
dcnStatus.Add "AccShare", dcnStatus("AccAllocation") - dcnStatus("MtssShare")

arrInput = GetAccShares(HeadId)
dcnStatus.Add "PccShare", arrInput(1)
dcnStatus.Add "CfShare", arrInput(2)
dcnStatus.Add "PrjShare", arrInput(3)
dcnStatus.Add "TransType", arrInput(6)
Erase arrInput()

If Scope Like "*p*" Or Scope Like "*c*" Then
    arrInput = GetPccCfReceived(HeadId)
    dcnStatus.Add "PccReceived", arrInput(4)
    dcnStatus.Add "CfReceived", arrInput(5)
    End If
    
Account:
If Not Scope Like "*a*" Then GoTo Receivables
dcnStatus.Add "AccReceived", GetAccReceived(HeadId)
dcnStatus.Add "AccExpenditure", GetAccExpenditure(HeadId)
dcnStatus.Add "AccCommitment", GetAccOutstandingCommits(HeadId)
dcnStatus.Add "AccInProcess", GetAccInProcess(HeadId)
dcnStatus.Add "AccBalance", Round(dcnStatus("AccReceived") - dcnStatus("AccExpenditure"), 2)
dcnStatus.Add "AccAvailable", Round(dcnStatus("AccReceived") - dcnStatus("AccExpenditure") - dcnStatus("AccCommitment") - dcnStatus("AccInProcess"), 2)
dcnStatus.Add "AccYetToBeRec", Round(dcnStatus("AccShare") - dcnStatus("AccReceived"), 2)
dcnStatus.Add "AccCanBeSpent", Round(dcnStatus("AccShare") - dcnStatus("AccExpenditure") - dcnStatus("AccCommitment") - dcnStatus("AccInProcess"), 2)

Receivables:
If Not Scope Like "*v*" Then GoTo Loans
dcnStatus.Add "AccReceivableMsnCompleted", GetAccReceivableMsnCompleted(HeadId)
dcnStatus.Add "AccReceivableMsnCurrent", GetAccReceivableMsnCurrent(HeadId)

Loans:
If Not Scope Like "*l*" Then GoTo Pcc
dcnStatus.Add "PccLoansGiven", GetPccLoansGiven(HeadId)
dcnStatus.Add "OthersLoansTaken", GetOthersLoansTaken(HeadId)
dcnStatus.Add "PccOwnExp", GetPccOwnExp(HeadId)

Pcc:
If Not Scope Like "*p*" Then GoTo CSRF
dcnStatus.Add "PccExpenditure", GetPccExpenditure(HeadId)
dcnStatus.Add "PccCommitment", GetPccOutstandingCommits(HeadId)
dcnStatus.Add "PccInProcess", GetPccInProcess(HeadId)
dcnStatus.Add "PccBalance", Round(dcnStatus("PccReceived") - dcnStatus("PccExpenditure"), 2)
dcnStatus.Add "PccAvailable", Round(dcnStatus("PccReceived") - dcnStatus("PccExpenditure") - dcnStatus("PccCommitment") - dcnStatus("PccInProcess"), 2)
dcnStatus.Add "PccYetToBeRec", Round(dcnStatus("PccShare") - dcnStatus("PccReceived"), 2)
dcnStatus.Add "PccCanBeSpent", Round(dcnStatus("PccShare") - dcnStatus("PccExpenditure") - dcnStatus("PccCommitment") - dcnStatus("PccInProcess"), 2)

CSRF:
If Not Scope Like "*c*" Then GoTo project
dcnStatus.Add "CfExpenditure", GetCfExpenditure(HeadId)
dcnStatus.Add "CfCommitment", GetCfOutstandingCommits(HeadId)
dcnStatus.Add "CfInProcess", GetCfInProcess(HeadId)
dcnStatus.Add "CfBalance", Round(dcnStatus("CfReceived") - dcnStatus("CfExpenditure"), 2)
dcnStatus.Add "CfAvailable", Round(dcnStatus("CfReceived") - dcnStatus("CfExpenditure") - dcnStatus("CfCommitment") - dcnStatus("CfInProcess"), 2)
dcnStatus.Add "CfYetToBeRec", Round(dcnStatus("CfShare") - dcnStatus("CfReceived"), 2)
dcnStatus.Add "CfCanBeSpent", Round(dcnStatus("CfShare") - dcnStatus("CfExpenditure") - dcnStatus("CfCommitment") - dcnStatus("CfInProcess"), 2)

project:
If Not Scope Like "*j*" Then GoTo Project_Subheads
dcnStatus.Add "PrjExpenditure", GetPrjExpenditure(HeadId)
dcnStatus.Add "PrjCommitment", GetPrjOutstandingCommits(HeadId)
dcnStatus.Add "PrjInProcess", GetPrjInProcess(HeadId)
dcnStatus.Add "PrjCanBeSpent", Round(dcnStatus("PrjShare") - dcnStatus("PrjExpenditure") - dcnStatus("PrjCommitment") - dcnStatus("PrjInProcess"), 2)

Project_Subheads:
If Not Scope Like "*s*" Then GoTo Others

arrInput = GetPrjAllocationsShd(HeadId)
For x = LBound(arrInput, 1) To UBound(arrInput, 1)
    For y = LBound(arrInput, 2) To UBound(arrInput, 2)
        arrSubheads(x, y) = arrInput(x, y)
        Next y
    Next x

arrInput = GetPrjExpenditureShd(HeadId)
For x = LBound(arrInput, 2) To UBound(arrInput, 2)
    For y = LBound(arrSubheads, 2) To UBound(arrSubheads, 2)
        If arrInput(0, x) = arrSubheads(0, y) Then arrSubheads(2, y) = arrInput(1, x)
        Next y
    Next x

arrInput = GetPrjOutstandingCommitsShd(HeadId)
For x = LBound(arrInput, 2) To UBound(arrInput, 2)
    For y = LBound(arrSubheads, 2) To UBound(arrSubheads, 2)
        If arrInput(0, x) = arrSubheads(0, y) Then arrSubheads(3, y) = arrInput(1, x)
        Next y
    Next x

arrInput = GetPrjInProcessShd(HeadId)
For x = LBound(arrInput, 2) To UBound(arrInput, 2)
    For y = LBound(arrSubheads, 2) To UBound(arrSubheads, 2)
        If arrInput(0, x) = arrSubheads(0, y) Then arrSubheads(4, y) = arrInput(1, x)
        Next y
    Next x

For y = LBound(arrSubheads, 2) To UBound(arrSubheads, 2)
    If Not IsEmpty(arrSubheads(0, y)) Then
            dcnStatus.Add "PrjShdName" & Chr(y + 64), arrSubheads(0, y)
            dcnStatus.Add "PrjShdAllocation" & Chr(y + 64), arrSubheads(1, y)
            dcnStatus.Add "PrjShdExpenditure" & Chr(y + 64), IIf(IsEmpty(arrSubheads(2, y)), 0, arrSubheads(2, y))
            dcnStatus.Add "PrjShdCommitment" & Chr(y + 64), IIf(IsEmpty(arrSubheads(3, y)), 0, arrSubheads(3, y))
            dcnStatus.Add "PrjShdInProcess" & Chr(y + 64), IIf(IsEmpty(arrSubheads(4, y)), 0, arrSubheads(4, y))
            dcnStatus.Add "PrjShdCanBeSpent" & Chr(y + 64), arrSubheads(1, y) - arrSubheads(2, y) - arrSubheads(3, y) - arrSubheads(4, y)
        End If
    Next y

Others:
If Not Scope Like "*o*" Then GoTo Last_Step:

Last_Step:
Set GetHeadStatus = dcnStatus

'Dim z As Variant
'For Each z In dcnStatus.Keys
'    Debug.Print z & "-" & dcnStatus(z)
'    Next z

End Function


Public Function SalaryTax(CtrSalary As Long, BaseSalary As Long) As Long
Dim dbsTax As Database
Dim rstTax As Recordset

Set dbsTax = CurrentDb()
Set rstTax = dbsTax.OpenRecordset("Select * From fin_salary_tax Where slt_from <= (" & CtrSalary & "*12) and slt_to >= (" & CtrSalary & "*12)", dbOpenSnapshot)
SalaryTax = (rstTax!slt_inttax + (CtrSalary * 12 - rstTax!slt_midamount) * rstTax!slt_midtax / 100) / 12 * (BaseSalary / CtrSalary)
rstTax.Close

End Function

Sub Doit()
Dim arr() As Variant
Dim lng As Long
Dim num As Variant
'lng = GetA16Element("GetAccCfReceivedCat", 350005, 1)
Debug.Print Chr(65)
End Sub






Option Compare Database
Option Explicit

Private Sub Detail_DblClick(Cancel As Integer)
Dim var1
Dim var2
var1 = Me.prj_commit
var2 = Nz(Me.prj_commit_a) + Nz(Me.prj_commit_b) + Nz(Me.prj_commit_c) + Nz(Me.prj_commit_d) + Nz(Me.prj_commit_e)
MsgBox var1 & vbCrLf & var2 & vbCrLf & (var1 = var2)
End Sub

Private Sub Form_Open(Cancel As Integer)
Dim dbsShd As Database
Dim rstShd As Recordset
Dim dcnStat As Scripting.Dictionary
Dim n As Integer
Dim c As Integer

Me.cmbAccount = Forms!vars.Parameter1
Set dcnStat = GetHeadStatus(Me.cmbAccount, "gvpljs")

'Basic Shares
Me.acc_allocation = dcnStat("AccAllocation")
Me.mtss_share = dcnStat("MtssShare")
Me.rdw_share = dcnStat("RdwShare")
Me.cf_share = dcnStat("CfShare")
Me.pcc_share = dcnStat("PccShare")
Me.prj_share = dcnStat("PrjShare")
Me.lblTax.Caption = IIf(dcnStat("TransType") = 1, "(Million Rupees without GST)", "(Million Rupees with GST)")
If Me.acc_allocation = 0 Then MsgBox "Project allocation not defined."
If Me.mtss_share = 0 Then MsgBox "MTSS allocation not defined."

'Pcc figures
Me.pcc_received = dcnStat("PccReceived")
Me.pcc_exp = dcnStat("PccExpenditure")
Me.pcc_commit = dcnStat("PccCommitment")
Me.pcc_ipc = dcnStat("PccInProcess")
Me.pcc_balance = dcnStat("PccBalance")
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.pcc_yettoberec = dcnStat("PccYetToBeRec")
Me.pcc_canbespent = dcnStat("PccCanBeSpent")

'Receivables
Me.pcc_avlbl = dcnStat("PccAvailable")
Me.acc_rcvmsncompleted = dcnStat("AccReceivableMsnCompleted")
Me.acc_rcvmsncurrent = dcnStat("AccReceivableMsnCurrent")

'Loans
Me.pcc_ownexp = dcnStat("PccOwnExp")
Me.others_loanstaken = dcnStat("OthersLoansTaken")
Me.pcc_loansgiven = dcnStat("PccLoansGiven")

'Project
Me.prj_exp = dcnStat("PrjExpenditure")
Me.prj_commit = dcnStat("PrjCommitment")
Me.prj_ipc = dcnStat("PrjInProcess")
Me.prj_canbespent = dcnStat("PrjCanBeSpent")

'Project subhead and graph source (fin_stateshd_temp)
Set dbsShd = CurrentDb()
dbsShd.Execute "Delete From fin_stateshd_temp"
Set rstShd = dbsShd.OpenRecordset("fin_stateshd_temp")
For n = 1 To 5
    If dcnStat.Exists("PrjShdName" & Chr(64 + n)) Then
        Me.Controls("prj_name_" & Chr(96 + n)) = dcnStat("PrjShdName" & Chr(64 + n))
        Me.Controls("prj_alloc_" & Chr(96 + n)) = dcnStat("PrjShdAllocation" & Chr(64 + n))
        Me.Controls("prj_exp_" & Chr(96 + n)) = dcnStat("PrjShdExpenditure" & Chr(64 + n))
        Me.Controls("prj_commit_" & Chr(96 + n)) = dcnStat("PrjShdCommitment" & Chr(64 + n))
        Me.Controls("prj_ipc_" & Chr(96 + n)) = dcnStat("PrjShdInProcess" & Chr(64 + n))
        Me.Controls("prj_canbespent_" & Chr(96 + n)) = dcnStat("PrjShdCanBeSpent" & Chr(64 + n))
        Me.Controls("prj_name_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_alloc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_ipc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_canbespent_" & Chr(96 + n)).Visible = True
        Me.Controls("box_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("btn_prj_ipc_" & Chr(96 + n)).Visible = True
        Me.Controls("graph_" & Chr(96 + n)).Visible = True
        With rstShd
            .AddNew
            !alphabet = Chr(96 + n)
            !type = dcnStat("PrjShdName" & Chr(64 + n))
            !allocation = dcnStat("PrjShdAllocation" & Chr(64 + n))
            !expenditure = dcnStat("PrjShdExpenditure" & Chr(64 + n))
            !Commitment = dcnStat("PrjShdCommitment" & Chr(64 + n))
            ![In Process] = dcnStat("PrjShdInProcess" & Chr(64 + n))
            ![Can be Spent] = dcnStat("PrjShdCanBeSpent" & Chr(64 + n))
            !Sorter = IIf(dcnStat("PrjShdName" & Chr(64 + n)) = "Misc", "zzz", dcnStat("PrjShdName" & Chr(64 + n)))
            .Update
            End With
        If Me.Controls("prj_name_" & Chr(96 + n)) = "HR" Then Me.txtHrSubhead = Chr(96 + n)
        c = c + 1
        End If
    Next n

'Visibilities
If Me.prj_share = Me.pcc_share Then Me.prj_share.Visible = False
If Me.pcc_exp = Me.pcc_ownexp And Me.others_loanstaken = 0 Then
    Me.txtWarnCat.Visible = False
    Me.lblGreen.Visible = False
    Me.boxGreen.Visible = False
    Me.prj_exp.Visible = False
    Me.prj_commit.Visible = False
    Me.prj_ipc.Visible = False
    Me.prj_canbespent.Visible = False
    Me.cmd_prj_exp.Visible = False
    Me.cmd_prj_commit.Visible = False
    Me.cmd_prj_ipc.Visible = False
    Me.txtg1.Visible = False
    Me.txtg2.Visible = False
    Me.boxExpbd.Visible = False
    Me.pcc_ownexp.Visible = False
    Me.others_loanstaken.Visible = False
    Me.pcc_loansgiven.Visible = False
    Me.cmd_pcc_ownexp.Visible = False
    Me.cmd_others_loanstaken.Visible = False
    Me.cmd_pcc_loansgiven.Visible = False
    Me.graph.Left = Me.graph.Left - 300
    Me.lblProject.Left = Me.lblProject.Left + 1500
    Me.pcc_share.Left = Me.pcc_share.Left + 1500
    Me.boxBlue.Left = Me.boxBlue.Left + 1500
    Me.pcc_received.Left = Me.pcc_received.Left + 1500
    Me.pcc_exp.Left = Me.pcc_exp.Left + 1500
    Me.pcc_balance.Left = Me.pcc_balance.Left + 1500
    Me.pcc_commit.Left = Me.pcc_commit.Left + 1500
    Me.pcc_ipc.Left = Me.pcc_ipc.Left + 1500
    Me.pcc_avlbl.Left = Me.pcc_avlbl.Left + 1500
    Me.pcc_yettoberec.Left = Me.pcc_yettoberec.Left + 1500
    Me.pcc_canbespent.Left = Me.pcc_canbespent.Left + 1500
    Me.cmd_pcc_received.Left = Me.cmd_pcc_received.Left + 1500
    Me.cmd_pcc_exp.Left = Me.cmd_pcc_exp.Left + 1500
    Me.cmd_pcc_commit.Left = Me.cmd_pcc_commit.Left + 1500
    Me.cmd_pcc_ipc.Left = Me.cmd_pcc_ipc.Left + 1500
    Me.boxRcv.Left = Me.boxRcv.Left + 1500
    Me.acc_rcvmsncompleted.Left = Me.acc_rcvmsncompleted.Left + 1500
    Me.acc_rcvmsncurrent.Left = Me.acc_rcvmsncurrent.Left + 1500
    Me.acc_availableafter.Left = Me.acc_availableafter.Left + 1500
    Me.lbl_received.Left = Me.lbl_received.Left + 1500
    Me.lbl_exp.Left = Me.lbl_exp.Left + 1500
    Me.lbl_balance.Left = Me.lbl_balance.Left + 1500
    Me.lbl_commit.Left = Me.lbl_commit.Left + 1500
    Me.lbl_ipc.Left = Me.lbl_ipc.Left + 1500
    Me.lbl_avlbl.Left = Me.lbl_avlbl.Left + 1500
    Me.lbl_yettoberec.Left = Me.lbl_yettoberec.Left + 1500
    Me.lbl_canbespent.Left = Me.lbl_canbespent.Left + 1500
    Me.lbl_rcvmsncompleted.Left = Me.lbl_rcvmsncompleted.Left + 1500
    Me.lbl_loansnet.Left = Me.lbl_loansnet.Left + 1500
    Me.lbl_availableafter.Left = Me.lbl_availableafter.Left + 1500
    End If

'If c > 1 Then
'    Me.graphSubheads.Width = Me.graphSubheads.Width + (c - 1) * 3.148 * 567
'    Me.cmdECBreakdown.Left = Me.cmdECBreakdown.Left + (c - 1) * 3.148 * 567
'    Me.cmdECTimeline.Left = Me.cmdECTimeline.Left + (c - 1) * 3.148 * 567
'    Me.cmdSalForecast.Left = Me.cmdSalForecast.Left + (c - 1) * 3.148 * 567
'    Me.cmdTimelineCtr.Left = Me.cmdTimelineCtr.Left + (c - 1) * 3.148 * 567
'    End If

Me.Visible = True

End Sub

Private Sub cmdExact_Click()
Dim objCtl As Control
For Each objCtl In Me.Controls
    If TypeOf objCtl Is TextBox Then
        objCtl.Format = "Standard"
        objCtl.DecimalPlaces = 2
        End If
    Next objCtl
    Me.lblTax.Caption = "(" & Right(Me.lblTax.Caption, Len(Me.lblTax.Caption) - 9)
    Me.txtBlank.SetFocus
    Me.cmdExact.Visible = False
End Sub

Private Sub cmdECBreakdown_Click()
Dim dbsEcb As Database
Dim qdfEcb As QueryDef
On Error GoTo cmdECBreakdown_Click_Err

DoCmd.Hourglass True
Set dbsEcb = CurrentDb()
dbsEcb.Execute "Delete From fin_ecbreakdown_temp"
Set qdfEcb = dbsEcb.QueryDefs("fin_ecbreakdown_so_tempadder")
qdfEcb.Parameters(0) = Forms!vars!Parameter1
qdfEcb.Execute
Set qdfEcb = dbsEcb.QueryDefs("fin_ecbreakdown_pc_tempadder")
qdfEcb.Parameters(0) = Forms!vars!Parameter1
qdfEcb.Execute
DoCmd.Hourglass False

DoCmd.OpenForm "fin_ecbreakdown_chart", acNormal, "", "", , acNormal

cmdECBreakdown_Click_Exit:
    Exit Sub

cmdECBreakdown_Click_Err:
    MsgBox Error$
    Resume cmdECBreakdown_Click_Exit
End Sub

Private Sub cmdECTimeline_Click()
Dim dbsEct As Database
Dim qdfEct As QueryDef
On Error GoTo cmdECTimeline_Click_Err

DoCmd.Hourglass True
Set dbsEct = CurrentDb()
dbsEct.Execute "Delete From fin_ectimeline_temp"
Set qdfEct = dbsEct.QueryDefs("fin_ectimeline_so_tempadder")
qdfEct.Parameters(0) = Forms!vars!Parameter1
qdfEct.Execute
Set qdfEct = dbsEct.QueryDefs("fin_ectimeline_pc_tempadder")
qdfEct.Parameters(0) = Forms!vars!Parameter1
qdfEct.Execute
DoCmd.Hourglass False

DoCmd.OpenForm "fin_ectimeline_chart", acNormal, "", "", , acNormal

cmdECTimeline_Click_Exit:
    Exit Sub

cmdECTimeline_Click_Err:
    MsgBox Error$
    Resume cmdECTimeline_Click_Exit

End Sub

'---------------------------------------------------------------------------------

Private Sub cmd_pcc_received_Click()
Forms!vars!Parameter1 = Me.cmbAccount
DoCmd.OpenForm "fin_sharesinstall", acNormal, , , , acHidden
End Sub

Private Sub cmd_pcc_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_exp1"
End Sub

Private Sub cmd_pcc_commit_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_pcc_commitsoutst1"
End Sub

Private Sub cmd_pcc_ipc_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_ipc1"
End Sub
'---------------------------------------------------------------------------------
Private Sub cmd_pcc_loansgiven_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_loansgiven1"
End Sub

Private Sub cmd_pcc_ownexp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_pcc_ownexp1"
End Sub

Private Sub cmd_others_loanstaken_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_others_loanstaken1"
End Sub

'---------------------------------------------------------------------------------

Private Sub cmd_prj_exp_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_prj_exp1"
End Sub

Private Sub cmd_prj_commit_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sto_prj_commitsoutst1"
End Sub

Private Sub cmd_prj_ipc_Click()
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sto_prj_ipc1"
End Sub
'---------------------------------------------------------------------------------

Private Sub btn_prj_exp_a_Click()
ShowSubheadExp "a"
End Sub

Private Sub btn_prj_exp_b_Click()
ShowSubheadExp "b"
End Sub

Private Sub btn_prj_exp_c_Click()
ShowSubheadExp "c"
End Sub

Private Sub btn_prj_exp_d_Click()
ShowSubheadExp "d"
End Sub

Private Sub btn_prj_exp_e_Click()
ShowSubheadExp "e"
End Sub

Sub ShowSubheadExp(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_exp1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_exp1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sts_prj_exp1_subhead"
End Sub

Private Sub btn_prj_ipc_a_Click()
ShowSubheadIPC "a"
End Sub

Private Sub btn_prj_ipc_b_Click()
ShowSubheadIPC "b"
End Sub

Private Sub btn_prj_ipc_c_Click()
ShowSubheadIPC "c"
End Sub

Private Sub btn_prj_ipc_d_Click()
ShowSubheadIPC "d"
End Sub

Private Sub btn_prj_ipc_e_Click()
ShowSubheadIPC "e"
End Sub

Sub ShowSubheadIPC(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_ipc1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_ipc1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_record", acNormal, , , , acHidden, "fin_sts_prj_ipc1_subhead"
End Sub

Private Sub btn_prj_commit_a_Click()
ShowSubheadCommits "a"
End Sub

Private Sub btn_prj_commit_b_Click()
ShowSubheadCommits "b"
End Sub

Private Sub btn_prj_commit_c_Click()
ShowSubheadCommits "c"
End Sub

Private Sub btn_prj_commit_d_Click()
ShowSubheadCommits "d"
End Sub

Private Sub btn_prj_commit_e_Click()
ShowSubheadCommits "e"
End Sub

Sub ShowSubheadCommits(Subhead As String)
Dim dbsSub As Database
Dim qdfSub As QueryDef

Set dbsSub = CurrentDb()
Set qdfSub = dbsSub.QueryDefs("fin_sts_prj_commitsoutst1_subhead")
qdfSub.sql = "Select * From fin_sts_prj_commitsoutst1 Where subhead = '" & Me.Controls("prj_name_" & Subhead) & "'"
DoCmd.OpenForm "wait"
DoCmd.OpenForm "fin_remcom", acNormal, , , , acHidden, "fin_sts_prj_commitsoutst1_subhead"
End Sub

'---------------------------------------------------------------------------------
'---------------------------------------------------------------------------------

Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err

DoCmd.OpenReport "fin_headstatus_details", acViewReport, "", "", acNormal, IIf(Me.cmdExact.Visible = True, "Rounded", "Exact")

cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub

Private Sub cmdReverse_Click()
DoCmd.OpenForm "fin_headstatus_rev", acNormal, "", "", , acHidden
End Sub


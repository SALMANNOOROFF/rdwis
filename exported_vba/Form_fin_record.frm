VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_record"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim strSqlFrom As String
Dim strSqlFromOuter As String
Dim strSqlFromOuterFinal As String
Dim strSqlFor As String
Dim strSqlForOuter As String
Dim strSqlForOuterFinal As String

'*** Sql - From
strSqlFrom = "Select fin_recordfrom.* From fin_recordfrom Inner Join " & Me.OpenArgs & " On fin_recordfrom.trn_id = " & Me.OpenArgs & ".trn_id"

strSqlFromOuter = "SELECT IIf([doctype]='Sa',0,[trn_id]) AS nsum_id, First(docid) AS ndocid,First(doctype) AS ndoctype, " & _
                    "rsdate AS nrsdate, IIf([doctype]='Sa','Monthly Salary',[title]) AS ntitle, First(effhed_id) AS neffhed_id, " & _
                    "First(effunt_id) AS neffunt_id, First(IIf([doctype]='Sa','',[hed_id])) AS nhed_id, " & _
                    "First(IIf([doctype]='Sa','',[unt_id])) AS nunt_id, " & _
                    "Sum(amount1) AS namount1, Sum(tax1) AS ntax1, Sum(amount2) AS namount2, First(trn_id) AS ntrn_id " & _
                    "FROM (" & strSqlFrom & ") " & _
                    "GROUP BY IIf([doctype]='Sa',0,[trn_id]), rsdate, IIf([doctype]='Sa','Monthly Salary',[title]) " & _
                    "ORDER BY rsdate"

strSqlFromOuterFinal = "SELECT nsum_id As sum_id, ndocid As docid, ndoctype As doctype, nrsdate As rsdate, ntitle As title, neffhed_id As effhed_id, neffunt_id As effunt_id, " & _
                        "nhed_id As hed_id, nunt_id As unt_id, namount1 As amount1, ntax1 As tax1, namount2 As amount2, ntrn_id As trn_id " & _
                        "From (" & strSqlFromOuter & ")"

'Debug.Print strSqlFromOuter
'Debug.Print strSqlFromOuterFinal

'*** Sql - For
strSqlFor = "Select fin_recordfor.* From fin_recordfor Inner Join " & Me.OpenArgs & " On fin_recordfor.trn_id = " & Me.OpenArgs & ".trn_id"

strSqlForOuter = "SELECT IIf([doctype]='Sa',0,[trn_id]) AS nsum_id, First(docid) AS ndocid, First(doctype) AS ndoctype, " & _
                    "rsdate AS nrsdate, IIf([doctype]='Sa','Monthly Salary',[title]) AS ntitle, First(IIf([doctype]='Sa','',[effhed_id])) AS neffhed_id, " & _
                    "First(IIf([doctype]='Sa','',[effunt_id])) AS neffunt_id, First(hed_id) AS nhed_id, " & _
                    "First(unt_id) AS nunt_id, Sum(amount1) AS namount1, " & _
                    "Sum(tax1) AS ntax1, Sum(amount2) AS namount2, First(trn_id) AS ntrn_id " & _
                    "FROM (" & strSqlFor & ") " & _
                    "GROUP BY IIf([doctype]='Sa',0,[trn_id]), rsdate, IIf([doctype]='Sa','Monthly Salary',[title]) " & _
                    "ORDER BY rsdate"


strSqlForOuterFinal = "SELECT nsum_id As sum_id, ndocid As docid, ndoctype As doctype, nrsdate As rsdate, ntitle As title, neffhed_id As effhed_id, neffunt_id As effunt_id, " & _
                        "nhed_id As hed_id, nunt_id As unt_id, namount1 As amount1, ntax1 As tax1, namount2 As amount2, ntrn_id As trn_id " & _
                        "From (" & strSqlForOuter & ")"

'Debug.Print strSqlForOuter
'Debug.Print strSqlForOuterFinal

Select Case Me.OpenArgs
    
    Case "fin_sto_acc_exp1"
        Me.RecordSource = strSqlFromOuterFinal
        Me.lbl_title.Caption = "Expenditure - From " & Me.cmbUnit.Column(1)

    Case "fin_sto_pcc_exp1"
        Me.RecordSource = strSqlFromOuterFinal
        Me.lbl_title.Caption = "Expenditure (Project) - From " & Me.cmbUnit.Column(1)

    Case "fin_sto_cf_exp1"
        Me.RecordSource = strSqlFromOuterFinal
        Me.lbl_title.Caption = "Expenditure (CSRF) - From " & Me.cmbUnit.Column(1)
        
    Case "fin_sto_prj_exp1"
        Me.RecordSource = strSqlForOuterFinal
        Me.lbl_title.Caption = "Expenditure - For " & Me.cmbUnit.Column(1)
        
    Case "fin_sto_pcc_loansgiven1"
        Me.RecordSource = strSqlFromOuterFinal
        Me.lbl_title.Caption = "Loans Given - From " & Me.cmbUnit.Column(1)
        
    Case "fin_sto_pcc_ownexp1"
        Me.RecordSource = strSqlForOuterFinal
        Me.lbl_title.Caption = "Project Expenditure For Other Accounts - For " & Me.cmbUnit.Column(1)
        
    Case "fin_sto_others_loanstaken1"
        Me.RecordSource = strSqlForOuterFinal
        Me.lbl_title.Caption = "Project Expenditure From Other Accounts - For " & Me.cmbUnit.Column(1)
        
    Case "fin_sts_prj_exp1_subhead"
        Me.RecordSource = strSqlForOuterFinal
        Me.lbl_title.Caption = "Project Subhead Expenditure - For " & Me.cmbUnit.Column(1)
    
    Case "fin_sto_acc_ipc1"
        Me.RecordSource = "fin_sto_acc_ipc1"
        Me.lbl_title.Caption = "In Process - From " & Me.cmbUnit.Column(1)
        Me.cmdDetail.Visible = False

    Case "fin_sto_pcc_ipc1"
        Me.RecordSource = "fin_sto_pcc_ipc1"
        Me.lbl_title.Caption = "In Process (Project) - From " & Me.cmbUnit.Column(1)
        Me.cmdDetail.Visible = False

    Case "fin_sto_cf_ipc1"
        Me.RecordSource = "fin_sto_cf_ipc1"
        Me.lbl_title.Caption = "In Process - (CSRF) From " & Me.cmbUnit.Column(1)
        Me.cmdDetail.Visible = False

    Case "fin_sto_prj_ipc1"
        Me.RecordSource = "fin_sto_prj_ipc1"
        Me.lbl_title.Caption = "In Process - For " & Me.cmbUnit.Column(1)
        Me.cmdDetail.Visible = False

    Case "fin_sts_prj_ipc1_subhead"
        Me.RecordSource = "fin_sts_prj_ipc1_subhead"
        Me.lbl_title.Caption = "In Process - For " & Me.cmbUnit.Column(1)
        Me.cmdDetail.Visible = False
    
    End Select

Me.OrderBy = "rsdate"
Me.OrderByOn = True
Me.Visible = True
DoCmd.Close acForm, "wait"
End Sub

Private Sub cmdDetail_Click()
Dim strSqlDetFrom As String
Dim strSqlDetFor As String

strSqlDetFrom = "Select fin_recordfrom.* From fin_recordfrom Inner Join " & Me.OpenArgs & " On fin_recordfrom.trn_id= " & Me.OpenArgs & ".trn_id"
strSqlDetFor = "Select fin_recordfor.* From fin_recordfor Inner Join " & Me.OpenArgs & " On fin_recordfor.trn_id= " & Me.OpenArgs & ".trn_id"

Select Case Me.OpenArgs
    Case "fin_sto_others_loanstaken1", "fin_sto_prj_exp1", "fin_sts_prj_exp1_subhead"
        Me.RecordSource = strSqlDetFor
    Case Else
        Me.RecordSource = strSqlDetFrom
    End Select
    
Me.txtSumAmount.SetFocus
Me.cmdDetail.Visible = False

End Sub

Private Sub cmdReport_Click()
Dim strReportName As String
On Error GoTo cmdReport_Click_Err


If Me.lbl_title.Caption Like "*From*" Then
    strReportName = "fin_record_from"
    Else
    strReportName = "fin_record_for"
    End If
    
DoCmd.OpenReport strReportName, acViewReport, "", "", acNormal


cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub

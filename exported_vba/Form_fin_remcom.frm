VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_remcom"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsCommit As Database
Dim rstCommit As Recordset
Dim dblCommit As Double
Dim lngDocid As Long
Dim lngDocidPrev As Long
Dim strSqlFrom As String
Dim strSqlFor As String

strSqlFrom = "Select fin_remcomfrom.* From fin_remcomfrom Inner Join " & Me.OpenArgs & " On fin_remcomfrom.cmt_id= " & Me.OpenArgs & ".cmt_id"
strSqlFor = "Select fin_remcomfor.* From fin_remcomfor Inner Join " & Me.OpenArgs & " On fin_remcomfor.cmt_id= " & Me.OpenArgs & ".cmt_id"

Select Case Me.OpenArgs
    Case "fin_sto_acc_commitsoutst1"
        Me.RecordSource = strSqlFrom
        Me.lbl_title.Caption = "Commitments - From " & Me.cmbUnit.Column(1)
        
    Case "fin_sto_pcc_commitsoutst1"
        Me.RecordSource = strSqlFrom
        Me.lbl_title.Caption = "Commitments - From " & Me.cmbUnit.Column(1)
    
    Case "fin_sto_cf_commitsoutst1"
        Me.RecordSource = strSqlFrom
        Me.lbl_title.Caption = "Commitments (CSRF) - From " & Me.cmbUnit.Column(1)
    
    Case "fin_sto_prj_commitsoutst1"
        Me.RecordSource = strSqlFrom
        Me.lbl_title.Caption = "Commitments - For " & Me.cmbUnit.Column(1)
    
    Case "fin_sts_prj_commitsoutst1_subhead"
        Me.RecordSource = strSqlFor
        Me.lbl_title.Caption = "Project Subhead Commitments - For " & Me.cmbUnit.Column(1)
        
    End Select

Me.OrderBy = "rsdate"
Me.OrderByOn = True

Set rstCommit = Me.RecordsetClone
If rstCommit.EOF Then GoTo Last_Step
rstCommit.MoveFirst
Do While Not rstCommit.EOF
    lngDocid = rstCommit!docid
    If lngDocidPrev <> lngDocid Then dblCommit = dblCommit + rstCommit!commit
    lngDocidPrev = rstCommit!docid
    rstCommit.MoveNext
    Loop
Me.sum_commit = dblCommit

Last_Step:
Me.Visible = True
DoCmd.Close acForm, "wait"
End Sub



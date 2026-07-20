VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcaseminutes"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim dbsMin As Database
Dim rstMin As Recordset
Dim qdfMin As QueryDef
Dim rstSource As Recordset
Dim strSql As String
Dim lngProject As Long
Dim ctlMin As Control
Dim dcnFigures As Scripting.Dictionary
Dim n As Integer, x As Integer
Dim arrPCs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From hr_ctrcaseminutes where ccm_id =" & arrArgs(0)
Me.this_minute = arrArgs(1)
Me.Type = arrArgs(2)
Me.Status = arrArgs(3)
Me.price = CDec(arrArgs(4))
If Not (arrArgs(5) >= 200000 And arrArgs(5) < 800000) Then Me.chrf = "chrf"
If arrArgs(6) <> "" Then Me.prj_id = CLng(arrArgs(6))

'Populate project Status
Set dbsMin = CurrentDb
If Not IsNull(Me.prj_id) Then
    Set rstSource = dbsMin.OpenRecordset("Select * From prj_projects where prj_id = " & Me.prj_id, dbOpenSnapshot)
    Me.prj_code = rstSource!prj_code
    Me.project_title = rstSource!prj_title
    Me.start_date = rstSource!prj_startdt
    End If

'Populate financial status
dbsMin.Execute "Delete From hr_ctrcaseminute_status_temp"
If Nz(Me.chrf, "") = "" Then
    strSql = dbsMin.QueryDefs("hr_ctrcaseminute_status1").sql
    dbsMin.QueryDefs("hr_ctrcaseminute_status1").sql = Left(strSql, InStr(strSql, "In (") + 3) & Me.ccm_ctrcases & ")))"
    Set qdfMin = dbsMin.QueryDefs("hr_ctrcaseminute_status_temp_adder")
    qdfMin.Execute
    Else
    StartWait
    Set qdfMin = dbsMin.QueryDefs("hr_ctrcaseminute_status_temp_chrf_adder")
    qdfMin.Execute
    EndWait
    End If
Me.subFin.Requery

strSql = ""
Set rstSource = dbsMin.OpenRecordset("Select Distinct head_code From hr_ctrcaseminute_status1", dbOpenSnapshot)
Do While Not rstSource.EOF
    If rstSource!head_code <> "Unassigned" Then
    strSql = strSql & IIf(strSql = "", "", ",") & rstSource!head_code
    Else
    Me.undefined = "Unassigned"
    End If
    rstSource.MoveNext
    Loop
Me.heads = strSql

'Modifications in different scenarios
Select Case Nz(Me.chrf, "")
    Case "chrf": Me.subFin.Form.lblalloc.Caption = "Allocation"
    Case Else: Me.subFin.Form.lblalloc.Caption = "HR Subhead"
    End Select
Select Case Me.Type
    Case "Cr"
    Case "Ce"
        Me.lbl_para_3.Caption = "Reason"
        Me.lbl_para_3e.Visible = False
    Case "Hg"
        Me.lbl_para_2.Caption = "Project HR"
        Me.lbl_para_3.Caption = "Hiring Activities"
        
    End Select
If getVar("varUnitLeadName") = getVar("varAccName") Then Me.chkSelf = -1

UpdateMinutes
Me.Visible = True

End Sub

Private Sub chkOmit_AfterUpdate()
UpdateMinutes
End Sub

Private Sub ccm_textb_AfterUpdate()
UpdateMinutes
End Sub

Private Sub ccm_textc_AfterUpdate()
UpdateMinutes
End Sub

Private Sub ccm_textd_AfterUpdate()
UpdateMinutes
End Sub
Private Sub cmdMinute_Click()
Dim strReportName As String
Dim strSql As String
Me.Dirty = False
strSql = CurrentDb().QueryDefs("hr_ctrcases_minutes").sql
CurrentDb().QueryDefs("hr_ctrcases_minutes").sql = Left(strSql, InStr(strSql, "In (") + 3) & Me.ccm_ctrcases & ")))"
Select Case Me.Type
    Case "Cr"
        If Nz(Me.chrf, "") = "" Then
            DoCmd.OpenReport "hr_ctrcaseminutes", acViewReport
            Else
            DoCmd.OpenReport "hr_ctrcaseminutes_chrf", acViewReport
            End If
    Case "Ce"
        If Nz(Me.chrf, "") = "" Then
            DoCmd.OpenReport "hr_ctrcaseextminutes", acViewReport
            Else
            DoCmd.OpenReport "hr_ctrcaseextminutes_chrf", acViewReport
            End If
    Case "Hg"
        If Nz(Me.chrf, "") = "" Then
            DoCmd.OpenReport "hr_ctrcasehgminutes", acViewReport
            Else
            DoCmd.OpenReport "hr_ctrcasehgminutes_chrf", acViewReport
            End If
    End Select
End Sub

Sub UpdateMinutes()
Dim dbsMinRefs As Database
Dim rstMinRefs As Recordset
Dim rstMinRefsFil As Recordset
Dim intMinNum As Integer
Dim n As Integer
On Error Resume Next

'Project Proposal
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Project Proposal'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.pp_encl = rstMinRefsFil!cmr_encl
    Me.pp_flag = rstMinRefsFil!cmr_flag
    Else
    Me.pp_encl = Null
    Me.pp_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'In-Principle Approval
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'In-Principle Approval'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.ia_encl = rstMinRefsFil!cmr_encl
    Me.ia_flag = rstMinRefsFil!cmr_flag
    Else
    Me.ia_encl = Null
    Me.ia_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Work order
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Work Order'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.wo_encl = rstMinRefsFil!cmr_encl
    Me.wo_flag = rstMinRefsFil!cmr_flag
    Else
    Me.wo_encl = Null
    Me.wo_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Project HR Status
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Project HR Status'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.hs_encl = rstMinRefsFil!cmr_encl
    Me.hs_flag = rstMinRefsFil!cmr_flag
    Else
    Me.hs_encl = Null
    Me.hs_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Contract Case
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title like 'Contract*Case' Or cmr_title = 'Hiring Case'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.cc_encl = rstMinRefsFil!cmr_encl
    Me.cc_flag = rstMinRefsFil!cmr_flag
    Else
    Me.cc_encl = Null
    Me.cc_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Financial Status
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Financial Status'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.fs_encl = rstMinRefsFil!cmr_encl
    Me.fs_flag = rstMinRefsFil!cmr_flag
    Else
    Me.fs_encl = Null
    Me.fs_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Performance Appraisal
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Performance Appraisal'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.pa_encl = rstMinRefsFil!cmr_encl
    Me.pa_flag = rstMinRefsFil!cmr_flag
    Else
    Me.pa_encl = Null
    Me.pa_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Selection Board Proceedings
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "cmr_title = 'Selection Board Proceedings'"
rstMinRefs.Sort = "cmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.sb_encl = rstMinRefsFil!cmr_encl
    Me.sb_flag = rstMinRefsFil!cmr_flag
    Else
    Me.sb_encl = Null
    Me.sb_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'This minute number  ---No more start minute
'Set rstMinRefs = Me.subRef.Form.RecordsetClone
'rstMinRefs.MoveFirst
'Do While Not rstMinRefs.EOF
'    If rstMinRefs!cmr_min > intMinNum Then intMinNum = rstMinRefs!cmr_min
'    rstMinRefs.MoveNext
'    Loop
'Me.this_minute = IIf(rstMinRefs.RecordCount = 0, Me.start_minute, intMinNum + 1)
'rstMinRefs.Close

'Para Numbers
If Me.chkOmit = -1 Then
    Me.para_1 = "X"
    Me.para_2 = 1
    Me.para_3 = 2
    Me.para_4 = 3
    n = 4
    If Nz(Me!ccm_textc, "") <> "" Then
        Me.para_5 = n
        n = n + 1
        Else
        Me.para_5 = "X"
        End If
    If Nz(Me!ccm_textd, "") <> "" Then
        Me.para_6 = n
        n = n + 1
        Else
        Me.para_6 = "X"
       End If
    Me.para_7 = n
    Else
    Me.para_1 = 1
    Me.para_2 = 2
    Me.para_3 = 3
    Me.para_4 = 4
    n = 5
    If Nz(Me!ccm_textc, "") <> "" Then
        Me.para_5 = n
        n = n + 1
        Else
        Me.para_5 = "X"
        End If
    If Nz(Me!ccm_textd, "") <> "" Then
        Me.para_6 = n
        n = n + 1
        Else
        Me.para_6 = "X"
        End If
    Me.para_7 = n
    End If

End Sub

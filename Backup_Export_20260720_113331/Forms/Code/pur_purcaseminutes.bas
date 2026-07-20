VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcaseminutes"
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
Dim rstPC As Recordset
Dim qdfMin As QueryDef
Dim lngProject As Long
Dim ctlMin As Control
Dim dcnFigures As Scripting.Dictionary
Dim n As Integer, x As Integer
Dim arrPCs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From pur_purcaseminutes where pcm_id =" & arrArgs(0)
Me.this_minute = arrArgs(1)
Me.hed_id = arrArgs(2)
Me.effhed_id = arrArgs(3)
Me.transtype = arrArgs(4)
Me.pcs_type = arrArgs(5)
Me.pcs_status = arrArgs(6)

'Set head codes
Set dbsMin = CurrentDb()
Set rstMin = dbsMin.OpenRecordset("Select hed_id, hed_code From cen_heads Where hed_id In (" & IIf(Me.hed_id = "", 0, Me.hed_id) & "," & Me.effhed_id & ")", dbOpenSnapshot)
If Me.hed_id <> "" Then
    rstMin.FindFirst ("[hed_id] = " & Me.hed_id)
    Me!hed_code = rstMin!hed_code
    rstMin.MoveFirst
    Else
    Me!hed_code = "RDW"
    End If
rstMin.FindFirst ("[hed_id] = " & Me.effhed_id)
Me!effhed_code = rstMin!hed_code
rstMin.Close

'Set project data
If Me.hed_id <> "" Then
    Set rstMin = dbsMin.OpenRecordset("Select hed_prj_id From cen_heads Where hed_id = " & Me.hed_id, dbOpenSnapshot)
    lngProject = rstMin!hed_prj_id
    rstMin.Close
    Set rstMin = dbsMin.OpenRecordset("Select prj_title, prj_code, prj_startdt From prj_projects Where prj_id = " & lngProject, dbOpenSnapshot)
    Me.project_title = rstMin!prj_title
    Me.prj_code = rstMin!prj_code
    Me.start_date = Nz(rstMin!prj_startdt, "")
    rstMin.Close
    End If

'Set Price
Set rstMin = dbsMin.OpenRecordset("SELECT Sum(pcs_intprice) AS sum_intprice, Sum(pcs_inttax) AS sum_inttax, " & _
                                         "Sum(pcs_midprice) AS sum_midprice, Sum(pcs_midtax) AS sum_midtax, " & _
                                         "Sum(pcs_price) AS sum_finprice " & _
                                         "FROM pur_purcases Where pcs_id In (" & Me.pcm_purcases & ")", dbOpenSnapshot)
Me.sum_intprice = rstMin!sum_intprice
Me.sum_inttax = rstMin!sum_inttax
Me.sum_midprice = rstMin!sum_midprice
Me.sum_midtax = rstMin!sum_midtax
Me.sum_finprice = rstMin!sum_finprice
rstMin.Close

Me.Required = IIf(Me.transtype = 1, Me.sum_midprice, sum_finprice)

'Set figures
Set dcnFigures = GetHeadStatus(Me.effhed_id, "gvpljs")

Me.acc_allocation = dcnFigures("AccAllocation")
Me.mtss_share = dcnFigures("MtssShare")
Me.cf_share = dcnFigures("CfShare")
Me.pcc_share = dcnFigures("PccShare")
Me.prj_share = dcnFigures("PrjShare")

Me.pcc_received = dcnFigures("PccReceived")
Me.pcc_exp = dcnFigures("PccExpenditure")
Me.pcc_commit = dcnFigures("PccCommitment")
Me.pcc_ipc = dcnFigures("PccInProcess")
Me.pcc_avlbl = dcnFigures("PccAvailable")
Me.acc_rcvmsncompleted = dcnFigures("AccReceivableMsnCompleted")
Me.acc_rcvmsncurrent = dcnFigures("AccReceivableMsnCurrent")

Me.prj_exp = dcnFigures("PrjExpenditure")
Me.prj_commit = dcnFigures("PrjCommitment")
Me.prj_ipc = dcnFigures("PrjInProcess")
Me.prj_canbespent = dcnFigures("PrjCanBeSpent")

arrPCs = Split(Me.pcm_purcases, ",")
For n = 1 To 5
    If dcnFigures.Exists("PrjShdName" & Chr(64 + n)) Then
        Me.Controls("prj_name_" & Chr(96 + n)) = dcnFigures("PrjShdName" & Chr(64 + n))
        Me.Controls("prj_alloc_" & Chr(96 + n)) = dcnFigures("PrjShdAllocation" & Chr(64 + n))
        Me.Controls("prj_exp_" & Chr(96 + n)) = dcnFigures("PrjShdExpenditure" & Chr(64 + n))
        Me.Controls("prj_commit_" & Chr(96 + n)) = dcnFigures("PrjShdCommitment" & Chr(64 + n))
        Me.Controls("prj_ipc_" & Chr(96 + n)) = dcnFigures("PrjShdInProcess" & Chr(64 + n))
        Me.Controls("prj_canbespent_" & Chr(96 + n)) = dcnFigures("PrjShdCanBeSpent" & Chr(64 + n))
        Me.Controls("required_" & Chr(96 + n)) = 0
        For x = LBound(arrPCs) To UBound(arrPCs)
            Set rstPC = dbsMin.OpenRecordset("Select pcs_transtype, pcs_midprice,pcs_price From pur_purcases Where pcs_id = " & arrPCs(x), dbOpenSnapshot)
            Set qdfMin = dbsMin.QueryDefs("pur_purcaseitems_byshd")
            qdfMin.Parameters("PcsId") = arrPCs(x)
            Set rstMin = qdfMin.OpenRecordset(dbOpenSnapshot)
            Do While Not rstMin.EOF
                If rstMin!pci_subhead = dcnFigures("PrjShdName" & Chr(64 + n)) Then
                    Me.Controls("required_" & Chr(96 + n)) = Nz(Me.Controls("required_" & Chr(96 + n)), 0) + _
                                                             IIf(rstPC!pcs_transtype = 1, rstPC!pcs_midprice * rstMin!ratio1, rstPC!pcs_price * rstMin!ratio2)
                    End If
                rstMin.MoveNext
                Loop
            rstMin.Close
            Next x
        Me.Controls("prj_name_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_alloc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_exp_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_commit_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_ipc_" & Chr(96 + n)).Visible = True
        Me.Controls("prj_canbespent_" & Chr(96 + n)).Visible = True
        End If
    Next n

If Me.pcs_status = "Draft" Then
    Me.pcc_ipc = Me.pcc_ipc + Me.Required
    Me.pcc_avlbl = Me.pcc_avlbl - Me.Required
    Me.prj_ipc = Me.prj_ipc + Me.Required
    Me.prj_canbespent = Me.prj_canbespent - Me.Required
    For n = 1 To 5
        If dcnFigures.Exists("PrjShdName" & Chr(64 + n)) Then
            Me.Controls("prj_ipc_" & Chr(96 + n)) = Me.Controls("prj_ipc_" & Chr(96 + n)) + Me.Controls("required_" & Chr(96 + n))
            Me.Controls("prj_canbespent_" & Chr(96 + n)) = Me.Controls("prj_canbespent_" & Chr(96 + n)) - Me.Controls("required_" & Chr(96 + n))
            End If
        Next n
    End If
    
Me.pcc_ipc_remarks = IIf(Me.Required = Me.pcc_ipc, "(Current Case)", "(Including " & FormatAsPerType(Me.Required) & " current case)")

'Format as per case type
Select Case arrArgs(5)
    Case "Ps"
    Case "Rb"
        Me.FormHeader.BackColor = RGB(191, 178, 207)
    Case "Pt"
        Me.FormHeader.BackColor = RGB(189, 197, 255)
        If Me.Required < 100000 Then
            Me.pcm_textc.SetFocus
            Me.lblTitle.Caption = "Form"
            Me.subRef.Visible = False
            Me.this_minute.Visible = False
            Me.chkOmit.Visible = False
            Me.pcm_lwoamount.Visible = False
            Me.pcm_texta.Visible = False
            Me.pcm_textd.Visible = False
            Me.pcm_texte.Visible = False
            Me.para_1.Visible = False
            Me.para_2.Visible = False
            Me.para_3.Visible = False
            Me.para_4.Visible = False
            Me.para_5.Visible = False
            Me.para_6.Visible = False
            Me.para_7.Visible = False
            Me.Label828.Visible = False
            Me.Label177.Visible = False
            'MsgBox Me.pcm_purcases.Top
'            For Each ctlMin In Me.Controls
'                If ctlMin.Top > 3840 Then ctlMin.Move ctlMin.Left, ctlMin.Top - 2600
'                If ctlMin.Top > 5100 Then ctlMin.Move ctlMin.Left, ctlMin.Top - 850
'                If ctlMin.Top > 8160 Then ctlMin.Move ctlMin.Left, ctlMin.Top - 1870
'                If ctlMin.Top > 11000 Then ctlMin.Move ctlMin.Left, ctlMin.Top - 1250
'                Next
            End If
    End Select


UpdateMinutes
Me.Visible = True

End Sub



Private Sub chkOmit_AfterUpdate()
UpdateMinutes
End Sub

Private Sub pcm_textc_AfterUpdate()
If Me.pcm_textc Like "for*" Then Me.pcm_textc = " " & Me.pcm_textc
End Sub

Private Sub pcm_textd_AfterUpdate()
UpdateMinutes
End Sub

Private Sub pcm_texte_AfterUpdate()
UpdateMinutes
End Sub

Private Sub cmdFinStat_Click()
On Error GoTo cmdFinStat_Click_Err

Forms!vars!Parameter1 = Me.effhed_id

DoCmd.OpenForm "fin_headstatus_brief", acNormal, "", "", , acNormal
cmdFinStat_Click_Exit:
    Exit Sub

cmdFinStat_Click_Err:
    MsgBox Error$
    Resume cmdFinStat_Click_Exit

End Sub

Private Sub cmdMinute_Click()
Dim strReportName As String

Me.Dirty = False
CurrentDb.QueryDefs("pur_purcaseitems_minutes").sql = _
    "SELECT * FROM pur_purcaseitems_minutes1 WHERE pcs_id In (" & Forms!pur_purcaseminutes!pcm_purcases & ")"
CurrentDb.QueryDefs("pur_purcases_minutes").sql = _
    "SELECT * FROM pur_purcases WHERE pcs_id In (" & Forms!pur_purcaseminutes!pcm_purcases & ")"


Select Case True
    Case Me.effhed_id >= 200000 And Nz(Me.hed_id, "") <> "" 'Regular
        strReportName = ""
    Case Me.effhed_id >= 200000 And Nz(Me.hed_id, "") = ""  'CSRF
        MsgBox "CSRF Case error. Please Contact IS department."
        Exit Sub
    Case Me.effhed_id < 200000                              'Central
        strReportName = "_cen"
    Case Else                                               'Unknown
        MsgBox "Unknown type error. Please Contact IS department."
        Exit Sub
    End Select
    
Select Case Me.pcs_type
    Case "Ps": strReportName = "pur_purcaseminutes" & strReportName
    Case "Pt": strReportName = "pur_purcasepettyminutes" & strReportName
    Case "Rb": strReportName = "pur_purcasetadaminutes" & strReportName
    End Select

DoCmd.OpenReport strReportName, acViewReport

End Sub

Sub UpdateMinutes()
Dim dbsMinRefs As Database
Dim rstMinRefs As Recordset
Dim rstMinRefsFil As Recordset
Dim intMinNum As Integer
Dim n As Integer
On Error Resume Next

'Work order
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Work Order'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.wo_encl = rstMinRefsFil!pmr_encl
    Me.wo_flag = rstMinRefsFil!pmr_flag
    Else
    Me.wo_encl = Null
    Me.wo_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Purchase Order
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Purchase Order'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.po_encl = rstMinRefsFil!pmr_encl
    Me.po_flag = rstMinRefsFil!pmr_flag
    Else
    Me.po_encl = Null
    Me.po_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Project Proposal
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Project Proposal'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.pp_encl = rstMinRefsFil!pmr_encl
    Me.pp_flag = rstMinRefsFil!pmr_flag
    Else
    Me.pp_encl = Null
    Me.pp_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Financial Status
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Financial Status'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.fs_encl = rstMinRefsFil!pmr_encl
    Me.fs_flag = rstMinRefsFil!pmr_flag
    Else
    Me.fs_encl = Null
    Me.fs_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Purchase Case
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Purchase Case' Or pmr_title = 'TA/DA Case'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.pc_encl = rstMinRefsFil!pmr_encl
    Me.pc_flag = rstMinRefsFil!pmr_flag
    Else
    Me.pc_encl = Null
    Me.pc_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close

'Market research report
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Market Research Report'"
rstMinRefs.Sort = "pmr_min"
Set rstMinRefsFil = rstMinRefs.OpenRecordset
If Not rstMinRefsFil.EOF Then
    Me.ms_encl = rstMinRefsFil!pmr_encl
    Me.ms_flag = rstMinRefsFil!pmr_flag
    Else
    Me.ms_encl = Null
    Me.ms_flag = Null
    End If
rstMinRefsFil.Close
rstMinRefs.Close


'This minute number  ---No more start minute
'Set rstMinRefs = Me.subRef.Form.RecordsetClone
'rstMinRefs.MoveFirst
'Do While Not rstMinRefs.EOF
'    If rstMinRefs!pmr_min > intMinNum Then intMinNum = rstMinRefs!pmr_min
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
    If Not IsNull(Me!pcm_textd) Then
        Me.para_5 = n
        n = n + 1
        Else
        Me.para_5 = "X"
        End If
    If Not IsNull(Me!pcm_texte) Then
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
    If Nz(Me!pcm_textd, "") <> "" Then
        Me.para_5 = n
        n = n + 1
        Else
        Me.para_5 = "X"
        End If
    If Nz(Me!pcm_texte, "") <> "" Then
        Me.para_6 = n
        n = n + 1
        Else
        Me.para_6 = "X"
        End If
    Me.para_7 = n
    End If

End Sub


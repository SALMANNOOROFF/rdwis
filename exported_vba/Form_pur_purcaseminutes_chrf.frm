VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcaseminutes_chrf"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String
Dim dbsMin As Database
Dim qdfMin As QueryDef
Dim arrArray()

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From pur_purcaseminutes where pcm_id =" & arrArgs(0)
Me.this_minute = arrArgs(1)
'Me.hed_id = arrArgs(2)
If Not arrArgs(3) Like "*,*" Then Me.effhed_id = arrArgs(3)
Me.transtype = arrArgs(4)
Me.pcs_type = arrArgs(5)
If arrArgs(5) = "Rb" Then Me.FormHeader.BackColor = RGB(191, 178, 207)
Forms!vars!Parameter2 = arrArgs(2)
Me!hed_code = "RDW"

'Create Temporary table for CHRF status
Set dbsMin = CurrentDb()
Set qdfMin = dbsMin.QueryDefs("pur_purcasechrf_status1")
qdfMin.sql = "SELECT pcs_id, pcs_effhed_id, pcs_midprice FROM pur_purcases WHERE pcs_id In(" & Me.pcm_purcases & ")"
dbsMin.Execute "Delete From pur_purcasechrf_status_temp"
Set qdfMin = dbsMin.QueryDefs("pur_purcasechrf_status_tempadder")
qdfMin.Execute
Me.subFin.Form.Requery

arrArray() = PcsPriceByCat(Me.pcm_purcases)
Me.price = Nz(arrArray(4), 0) + Nz(arrArray(5), 0) + Nz(arrArray(6), 0)
Erase arrArray()

UpdateMinutes
Me.Visible = True

End Sub

Private Sub pcm_textc_AfterUpdate()
If Me.pcm_textc Like "*." Then Me.pcm_textc = Left(Me.pcm_textc, Len(Me.pcm_textc) - 1)
End Sub

Private Sub pcm_textd_AfterUpdate()
UpdateMinutes
End Sub

Private Sub pcm_texte_AfterUpdate()
UpdateMinutes
End Sub

Private Sub cmdFinStat_Click()
On Error GoTo cmdFinStat_Click_Err

Forms!vars!Parameter1 = Me.subFin.Form!hed_id

DoCmd.OpenForm "fin_headstatus_csrf", acNormal, "", "", , acNormal

cmdFinStat_Click_Exit:
    Exit Sub

cmdFinStat_Click_Err:
    MsgBox Error$
    Resume cmdFinStat_Click_Exit

End Sub

Private Sub cmdMinute_Click()
Me.Dirty = False
CurrentDb.QueryDefs("pur_purcaseitems_minutes").sql = _
    "SELECT * FROM pur_purcaseitems_minutes1 WHERE pcs_id In (" & Forms!pur_purcaseminutes_chrf!pcm_purcases & ")"
CurrentDb.QueryDefs("pur_purcases_minutes").sql = _
    "SELECT * FROM pur_purcases WHERE pcs_id In (" & Forms!pur_purcaseminutes_chrf!pcm_purcases & ")"

Select Case Me.pcs_type
    Case "Ps": DoCmd.OpenReport "pur_purcaseminutes_chrf", acViewReport
    Case "Rb": DoCmd.OpenReport "pur_purcasetadaminutes_chrf", acViewReport
    End Select
End Sub

Sub UpdateMinutes()
Dim dbsMinRefs As Database
Dim rstMinRefs As Recordset
Dim rstMinRefsFil As Recordset
Dim intMinNum As Integer
Dim n As Integer
On Error Resume Next

'Purchase Case
Set rstMinRefs = Me.subRef.Form.RecordsetClone
rstMinRefs.Filter = "pmr_title = 'Purchase Case'"
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


'CHRF
'Set rstMinRefs = Me.subRef.Form.RecordsetClone
'rstMinRefs.Filter = "pmr_title = 'CHRF Policy'"
'rstMinRefs.Sort = "pmr_min"
'Set rstMinRefsFil = rstMinRefs.OpenRecordset
'If Not rstMinRefsFil.EOF Then
'    Me.cf_encl = rstMinRefsFil!pmr_encl
'    Me.cf_flag = rstMinRefsFil!pmr_flag
'    Else
'    Me.cf_encl = Null
'    Me.cf_flag = Null
'    End If
'rstMinRefsFil.Close
'rstMinRefs.Close

'Para Numbers
Me.para_1 = "X"
Me.para_2 = 1
Me.para_3 = 2
Me.para_4 = 3

n = 4
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

End Sub


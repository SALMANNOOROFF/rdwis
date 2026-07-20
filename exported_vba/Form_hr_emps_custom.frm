VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_emps_custom"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit
Public dcnFields As Scripting.Dictionary

Private Sub Form_Open(Cancel As Integer)
Me.lstFlds.selected(0) = True
Me.txtCount = 1

If getVar("varMode") Like "*-s" Then
    Me.cmbUnits = getVar("varUnitId")
    Me.cmbUnits.Visible = False
    End If

End Sub

Private Sub lstFlds_AfterUpdate()
If Me.lstFlds.selected(0) = False Then Me.lstFlds.selected(0) = True
If Me.lstFlds.selected(Me.lstFlds.ListIndex) = True And Me.lstFlds.Column(0, Me.lstFlds.ListIndex) Like "---*" Then
    Me.lstFlds.selected(Me.lstFlds.ListIndex) = False
    End If
If Me.lstFlds.selected(Me.lstFlds.ListIndex) = True And Me.lstFlds.Column(0, Me.lstFlds.ListIndex) = "" Then
    Me.lstFlds.selected(Me.lstFlds.ListIndex) = False
    End If
If Me.txtCount = 15 And Me.lstFlds.selected(Me.lstFlds.ListIndex) = True Then
    Me.lstFlds.selected(Me.lstFlds.ListIndex) = False
    MsgBox "Maximum 15 fields can be selected.", vbCritical
    End If
UpdateSql
End Sub

Private Sub cmbStatus_AfterUpdate()
UpdateSql
End Sub

Private Sub cmbUnits_AfterUpdate()
UpdateSql
End Sub

Private Sub UpdateSql()
Dim key As Variant
Dim strSql As String
Dim count As Integer
Dim n As Integer

Set dcnFields = New Scripting.Dictionary
For n = 0 To Me.lstFlds.ListCount - 1
    If Me.lstFlds.selected(n) Then
        dcnFields.Add Me.lstFlds.Column(0, n), Array(Me.lstFlds.Column(1, n), Me.lstFlds.Column(2, n), Me.lstFlds.Column(3, n), Me.lstFlds.Column(4, n), Me.lstFlds.Column(5, n))
        count = count + 1
        End If
    Next n
Me.txtCount = count

For Each key In dcnFields.Keys
    strSql = strSql & IIf(strSql = "", "", ", ") & dcnFields(key)(0) & " As " & "[" & key & "]"
    Next key
strSql = "Select " & strSql & " From hr_emps_u_grand "

If Me.cmbStatus <> "All" Then
    Select Case Me.cmbStatus
        Case "Current":    strSql = strSql & " Where emp_status like 'Active*'"
        Case "Previous":   strSql = strSql & " Where Not emp_status like 'Active*'"
        End Select
    End If
    
If Me.cmbUnits <> 0 Then
    strSql = strSql & IIf(strSql Like "*Where*", " And ", " Where ") & "emp_unt_id = " & Me.cmbUnits
    End If

strSql = strSql & " Order By emp_unt_id, emp_id"

Me.txtSql = strSql

End Sub

Private Sub cmdReport_Click()
On Error GoTo cmdReport_Click_Err
Dim qdfCustom As QueryDef

If Me.txtCount = 1 Then
    MsgBox "Atleast two field are to be selected.", vbCritical
    Exit Sub
    End If

Set qdfCustom = CurrentDb.QueryDefs("hr_emps_custom")
qdfCustom.sql = Me.txtSql

DoCmd.OpenReport "hr_emps_custom", acViewReport, "", "", acNormal, Me.cmbUnits.Column(1)


cmdReport_Click_Exit:
    Exit Sub

cmdReport_Click_Err:
    MsgBox Error$
    Resume cmdReport_Click_Exit

End Sub



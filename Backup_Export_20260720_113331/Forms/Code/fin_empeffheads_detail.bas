VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_empeffheads_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Detail_DblClick(Cancel As Integer)
MsgBox Me.AllowEdits
End Sub

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Me.RecordSource = "Select * From fin_empeffheads_u Where emp_id = '" & arrArgs(0) & "'"

Select Case arrArgs(1)
    Case "Open"
        Me.eeh_status.Locked = False
        Me.eeh_status.BorderStyle = 1
        Me.box_eeh_status.Visible = False
        Me.eeh_emphed_id.Locked = False
        Me.eeh_emphed_id.BorderStyle = 1
        Me.box_eeh_emphed_id.Visible = False
    Case "Closed"
        Me.cmdReverse.Visible = True
    End Select

If Not (arrArgs(2) = "Project" And arrArgs(3) = "False") Then
    Me.eeh_emphed_id.Visible = True
    Me.eeh_sudohed.Visible = True
    Me.dtg.Visible = True
    End If
    
Me.Visible = True

End Sub

Private Sub eeh_emphed_id_BeforeUpdate(Cancel As Integer)
Dim strEmpId As String
strEmpId = Me!emp_id

If IsNull(Me!eeh_emphed_id) Then
    Cancel = 0
    Me.Undo
    MsgBox "Please assign a project.", vbCritical
    Exit Sub
    End If

'If RecordBusDataAudit(strEmpId) = False Then
'    Cancel = 0
'    Me.Undo
'    MsgBox "The change cannot be saved.", vbCritical
'    End If
End Sub

Private Sub eeh_emphed_id_AfterUpdate()
If IsNull(Me!eeh_emphed_id) Then
    Me!eeh_sudohed = Null
    Else
    Me!eeh_sudohed = "CHRF"
    End If
Me!eeh_dtg = GetNow()
Me.Refresh
End Sub

Private Sub eeh_sudohed_AfterUpdate()
Me!eeh_dtg = GetNow()
End Sub

Private Sub cmdEmp_Click()
DoCmd.OpenForm "hr_contracts", acNormal, , , , acHidden, "OneRecord~" & Me!emp_id
End Sub

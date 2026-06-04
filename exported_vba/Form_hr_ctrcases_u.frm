VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcases_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft"
        Me.lblHeader.Caption = "Contract Cases - Draft"
        Me.RecordSource = "hr_ctrcases_u_draft"
        If getVar("varUnitArea") = "prj" And getVar("varMode") <> "viewer*" Then
            Me.cmdMinute.Visible = True
            End If
    Case "Open"
        Me.lblHeader.Caption = "Contract Cases - Open"
        Me.RecordSource = "hr_ctrcases_u_open"
    Case "Closed"
        Me.lblHeader.Caption = "Contract Cases - Closed"
        Me.RecordSource = "hr_ctrcases_u_closed"
    End Select
Me.Visible = True

End Sub

Private Sub Form_Close()
If Me.RecordSource Like "*draft*" Or Me.RecordSource Like "*open*" Then CurrentDb.Execute "Delete From selected"
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub cmdCtr_Click()
Dim strFormName As String

If getVar("varMode") Like "*m" And Me!ctc_status = "Under Revision" Then
    MsgBox "The contract case is under revision. It cannot be viewed.", vbCritical
    Exit Sub
    End If

Select Case Me!ctc_type
    Case "Cr": strFormName = "hr_ctrcase_detail"
    Case "Ce": strFormName = "hr_ctrcaseext_detail"
    Case "Hg": strFormName = "hr_ctrcasehg_detail"
    End Select
    
DoCmd.OpenForm strFormName, acNormal, , , , acHidden, Me!ctc_status & "~" & Me!ctc_id

End Sub

Private Sub cmdMinute_Click()
DoCmd.OpenForm "hr_ctrcases_choose", acNormal
End Sub



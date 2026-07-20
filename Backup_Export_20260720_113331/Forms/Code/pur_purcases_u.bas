VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_u"
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
        Me.lblHeader.Caption = "Purchase Cases - Draft"
        Me.RecordSource = "pur_purcases_u_draft"
        AllowEditsAdvanced Me.Name, False, False
        If getVar("varUnitArea") = "prj" Then
            Me.cmdMinute.Visible = True
            End If
    Case "Open"
        Me.lblHeader.Caption = "Purchase Cases - Open"
        Me.RecordSource = "pur_purcases_u_open"
        AllowEditsAdvanced Me.Name, False, False
        If getVar("varUnitArea") = "prj" And getVar("varMode") <> "viewer*" Then
            Me.cmdMinute.Visible = True
            End If
    Case "Closed"
        Me.lblHeader.Caption = "Purchase Cases - Closed"
        Me.RecordSource = "pur_purcases_u_closed"
    Case "ProjectWise"
        Me.lblHeader.Caption = "Purchase Cases - Single Project"
        Me.RecordSource = "Select * From pur_purcases_u Where pcs_effhed_id = " & arrArgs(1)
    End Select
Me.Visible = True

End Sub

Private Sub Form_Close()
If Me.RecordSource Like "*draft*" Or Me.RecordSource Like "*open*" Then CurrentDb.Execute "Delete From selected"
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub
Private Sub cmdPcs_Click()
Dim strFormName As String
Dim strPcsStatus As String

If getVar("varMode") Like "*m" And Me.pcs_status = "Under Revision" Then
    MsgBox "The purchase case is under revision. It cannot be viewed.", vbCritical
    Exit Sub
    End If

Select Case Me!pcs_type
    Case "Ps"
    strFormName = "pur_purcases_detail"
    Case "Rb"
    strFormName = "pur_purcasestada_detail"
    Case "Pt"
    strFormName = "pur_purcasespetty_detail"
    End Select
strPcsStatus = IIf(Me!pcs_intunt_id <> getVar("varUnitId") And getVar("varMode") Like "*-s", "OtherInitUnit", Me!pcs_status)
DoCmd.OpenForm strFormName, acNormal, , , , acHidden, strPcsStatus & "~" & Me!pcs_id
End Sub


Private Sub cmdMinute_Click()
DoCmd.OpenForm "pur_purcases_choose", acNormal
End Sub



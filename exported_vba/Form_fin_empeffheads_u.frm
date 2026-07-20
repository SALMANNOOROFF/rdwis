VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_empeffheads_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.chkSetting = getVarGlobal("salhead_applicable")
End Sub

Private Sub Form_Activate()
Me.Requery
End Sub

Private Sub chkSetting_AfterUpdate()
MsgBox Me.chkSetting
End Sub

Private Sub cmdEmp_Click()
DoCmd.OpenForm "fin_empeffheads_detail", acNormal, , , , acHidden, _
                Me.emp_id & "~" & _
                Me.eeh_status & "~" & _
                IIf(Me.emp_unt_id >= 200000 And Me.emp_unt_id < 800000, "Project", "Central") & "~" & _
                Me.chkSetting
End Sub

Private Sub cmdFinStatClosed_Click()
Me.RecordSource = "fin_empeffheads_u_closed"
Me.cmdFinStatClosed.Visible = False
Me.Refresh
End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcaseminutes_subm"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdDelete_Click()
DoCmd.RunCommand acCmdDeleteRecord
End Sub

Private Sub Form_AfterUpdate()
Parent.UpdateMinutes
End Sub

Private Sub Form_AfterDelConfirm(status As Integer)
Parent.UpdateMinutes
End Sub

Private Sub pmr_min_AfterUpdate()
'If Me.Recordset.RecordCount = 0 Then Me.pmr_min = (Parent.start_minute)
End Sub

Private Sub pmr_title_AfterUpdate()

Select Case Me!pmr_title
    Case "Purchase Case", "TA/DA Case"
        Me!pmr_from = getVar("varUnitLeadDesigShort")
        Me!pmr_ref = FormatWithAmpersand(Parent!pcm_purcases)
        Me.pmr_date.SetFocus
    Case "Financial Status", "Market Research Report"
        Me!pmr_from = getVar("varUnitLeadDesigShort")
        Me!pmr_ref = Null
        Me.pmr_ref.SetFocus
    End Select

End Sub

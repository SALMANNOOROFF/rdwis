VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcaseminutes_subm"
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

Private Sub Form_AfterDelConfirm(Status As Integer)
Parent.UpdateMinutes
End Sub

Private Sub pmr_min_AfterUpdate()
'If Me.Recordset.RecordCount = 0 Then Me.pmr_min = (Parent.start_minute)
End Sub

Private Sub cmr_title_AfterUpdate()

Select Case Me!cmr_title
    Case "Contract Renewal Case", "Contract Extension Case"
        Me!cmr_from = getVar("varUnitLeadDesigShort")
        Me!cmr_ref = FormatWithAmpersand(Parent!ccm_ctrcases)
        Me.cmr_date.SetFocus
    Case "Financial Status", "Market Research Report"
        Me!cmr_from = getVar("varUnitLeadDesigShort")
        Me!cmr_ref = Null
        Me.cmr_ref.SetFocus
    End Select

End Sub

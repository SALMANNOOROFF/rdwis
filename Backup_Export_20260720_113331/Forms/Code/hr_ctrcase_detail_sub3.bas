VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcase_detail_sub3"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub ccp_hed_id_AfterUpdate()
Me.Dirty = False
Me.Parent!ctc_prj_id = GetContractCaseProject(Me.ccp_ctc_id)
End Sub


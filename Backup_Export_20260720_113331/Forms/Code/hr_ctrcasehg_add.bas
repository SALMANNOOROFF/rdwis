VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_ctrcasehg_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub ctc_newstartdt_AfterUpdate()
Me!ctc_approvedstartdt = Me!ctc_newstartdt
End Sub

Private Sub ctc_newenddt_AfterUpdate()
Me!ctc_approvedenddt = Me!ctc_newenddt
End Sub

Private Sub ctc_newctrtype_AfterUpdate()
Me!ctc_approvedctrtype = Me!ctc_newctrtype
End Sub

Private Sub ctc_newgrade_AfterUpdate()
Me!ctc_approvedgrade = Me!ctc_newgrade
End Sub

Private Sub ctc_newjobtitle_AfterUpdate()
Me!ctc_approvedjobtitle = Me!ctc_newjobtitle
End Sub

Private Sub ctc_newprob_AfterUpdate()
Me!ctc_approvedprob = Me!ctc_newprob
End Sub

Private Sub ctc_newprobsal_AfterUpdate()
Me!ctc_approvedprobsal = Me!ctc_newprobsal
End Sub

Private Sub ctc_newsalary_AfterUpdate()
Me!ctc_approvedsalary = Me!ctc_newsalary
End Sub

Private Sub ctc_newunt_id_AfterUpdate()
Me!ctc_approvedunt_id = Me!ctc_newunt_id
End Sub

Private Sub cmdCancel_Click()
Me.Undo
DoCmd.Close
End Sub

Private Sub cmdSave_Click()
Me.Dirty = False
GeneratePlan "CtrCase", Me!ctc_newstartdt, Me!ctc_newenddt, Me!ctc_id
Me!ctc_price = CalculateCcPrice(Me!ctc_id)
Me.Dirty = False
DoCmd.OpenForm "hr_ctrcasehg_detail", acNormal, , , , acHidden, Me!ctc_status & "~" & Me!ctc_id
DoCmd.Close acForm, "hr_ctrcasehg_add"
End Sub

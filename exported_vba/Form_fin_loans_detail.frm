VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_loans_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub lblLoansReturnPolicy_Click()
On Error GoTo Err_Sub
FollowHyperlink FileBankDest() & "\pur\loans_return_policy_2022.pdf"
Exit Sub
Err_Sub:
MsgBox "The file cannot be opened", vbCritical
End Sub

Private Sub cmdLoans_Click()
DoCmd.OpenForm "fin_loans_detail_one", acNormal, , , , acHidden, Me.loan_first
End Sub

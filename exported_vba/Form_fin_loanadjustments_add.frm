VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_loanadjustments_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub optAdjustment_AfterUpdate()
Select Case Me.optAdjustment
    Case 1
        Me.remark1 = "Added to CSRF."
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    Case 2
        Me.remark1 = "Added to CSRF of ..."
        Me.cmbAccount.Visible = True
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    Case 3
        Me.remark1 = "Adjusted against ..."
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = True
    Case 4
        Me.remark1 = "Regularized."
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    Case 5
        Me.remark1 = "Tax adjustment."
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    Case 6
        Me.remark1 = "Residual amount."
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    Case 7
        Me.remark1 = ""
        Me.cmbAccount.Visible = False
        Me.cmbAccount = ""
        Me.cmbLoan.Visible = False
        Me.cmbLoan = ""
    End Select
End Sub

Private Sub cmbAccount_AfterUpdate()
Me.remark1 = "Added to CSRF of " & Me.cmbAccount & " account."
End Sub

Private Sub cmbLoan_AfterUpdate()
Me.remark1 = "Adjusted against " & Me.cmbLoan & " loan."
End Sub

Private Sub cmdAdjust_Click()
Dim dbsAdjust As Database
Dim rstAdjust As Recordset

'Checks
If Nz(Me.amount, "") = "" Then
        MsgBox "Please enter an amount.", vbCritical
        Me.amount.SetFocus
        Exit Sub
        End If

If IsNull(Me.optAdjustment) Then
    MsgBox "Please select an option.", vbCritical
    Exit Sub
    End If

If Me.optAdjustment.Value = 2 And Nz(Me.cmbAccount, "") = "" Then
        MsgBox "Please choose an account.", vbCritical
        Me.cmbAccount.SetFocus
        Exit Sub
        End If

If Me.optAdjustment.Value = 3 And Nz(Me.cmbLoan, "") = "" Then
        MsgBox "Please choose a loan.", vbCritical
        Me.cmbLoan.SetFocus
        Exit Sub
        End If

If Me.optAdjustment.Value = 7 And Nz(Me.remark2, "") = "" Then
        MsgBox "Please define the loan adjustment in 'Additional Remarks' field.", vbCritical
        Me.remark2.SetFocus
        Exit Sub
        End If
        
If Me.optAdjustment.Value = 1 Or Me.optAdjustment.Value = 2 Then
    If Not Me.loan_string Like "RGA-*" Then
        MsgBox "The option selected is not relevant to this loan.", vbCritical
        Exit Sub
        End If
    End If

'Add record
Set dbsAdjust = CurrentDb()
Set rstAdjust = dbsAdjust.OpenRecordset("fin_loanadjustments")
rstAdjust.AddNew
rstAdjust!lad_string = Me.loan_string
rstAdjust!lad_amount = Me.amount
rstAdjust!lad_remarks = Trim(Nz(Me.remark1, "") & " " & Nz(Me.remark2))
rstAdjust!lad_dtg = GetNow()
rstAdjust.Update

Forms!fin_loans_detail_one.Refresh
MsgBox "Loan adjustment added.", vbInformation
DoCmd.Close

End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

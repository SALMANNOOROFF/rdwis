VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasemilestone"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub frmPayment_AfterUpdate()
Select Case Me.frmPayment
    Case 1
    Me.txtPayment = Me.payment_due & " %"
    Case 2
    Me.txtPayment = "Rs " & Me.payment_due
    End Select
End Sub

Private Sub payment_due_AfterUpdate()
Select Case Me.frmPayment
    Case 1
    Me.txtPayment = Me.payment_due & " %"
    Case 2
    Me.txtPayment = "Rs " & Me.payment_due
    End Select

End Sub

Private Sub cmdCertificate_Click()
On Error GoTo cmdCertificate_Click_Err

Me.Dirty = False
DoCmd.OpenReport "pur_purcasemilestone", acViewReport, "", "", acNormal


cmdCertificate_Click_Exit:
    Exit Sub

cmdCertificate_Click_Err:
    MsgBox Error$
    Resume cmdCertificate_Click_Exit

End Sub



VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_ctrcasehg_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Load()
If Me!ctc_status = "Approved" Or Me!ctc_status = "Fulfilled" Then
    Me.txtBlank.Visible = True
    Me.approvedunt.Visible = True
    Me.lblApproved.Visible = True
    Me.approveddates.Visible = True
    Me.approvedgrade.Visible = True
    Me.approvedjobtitle.Visible = True
    Me.approvedsalary.Visible = True
    Me.approvedctrtype.Visible = True
    End If
End Sub



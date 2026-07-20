VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_extcompenses_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub ecp_emp_id_AfterUpdate()
Me.ecp_empnamecomp = Me.ecp_emp_id.Column(1)
End Sub

Private Sub ecp_month_AfterUpdate()
Me.ecp_month = LastDateThisMonth(Me.ecp_month)
End Sub

Private Sub cmdAdd_Click()
Dim dbsExpense As Database
Dim rstExpense As Recordset

If IsNull(Me.ecp_date) Or Nz(Me.ecp_emp_id, "") = "" Or Nz(Me.ecp_empnamecomp, "") = "" Or IsNull(Me.ecp_unt_id) _
   Or Nz(Me.ecp_type, "") = "" Or IsNull(Me.ecp_month) Or IsNull(Me.ecp_amount) Then
    MsgBox "Incomplete data.", vbCritical
    Exit Sub
    End If

Set dbsExpense = CurrentDb()
Set rstExpense = dbsExpense.OpenRecordset("fin_extcompenses")
With rstExpense
    .AddNew
    !ecp_date = Me.ecp_date
    !ecp_emp_id = Me.ecp_emp_id
    !ecp_empnamecomp = Me.ecp_empnamecomp
    !ecp_unt_id = Me.ecp_unt_id
    !ecp_type = Me.ecp_type
    !ecp_month = Me.ecp_month
    !ecp_amount = Me.ecp_amount
    !ecp_remarks = Me.ecp_remarks
    .Update
    End With
DoCmd.Close
Forms!fin_extcompenses_u.Refresh
End Sub



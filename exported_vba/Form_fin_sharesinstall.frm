VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_sharesinstall"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsFunding  As Database
Dim rstFunding As Recordset
On Error GoTo The_End

Set dbsFunding = CurrentDb()
Set rstFunding = dbsFunding.OpenRecordset("SELECT Sum(mct_cost) AS cost_total From fin_msncosts " & _
                                           "WHERE fin_msncosts.mct_hed_id = " & Me.sha_hed_id, dbOpenSnapshot)
Me.cost_total = rstFunding!cost_total

The_End:
Me.Visible = True


End Sub




VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_salorderminute"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsLcl As Database
Dim rstLcl As Recordset
Dim rstSumm As Recordset


Set dbsLcl = CurrentDb()
Set rstLcl = dbsLcl.OpenRecordset("fin_salorderminute_temp", dbOpenDynaset)


Set rstSumm = dbsLcl.OpenRecordset("fin_salorderminute_empsdue", dbOpenSnapshot)
rstLcl.Edit
rstLcl!empsdue = rstSumm!count_empsdue
rstLcl.Update
rstSumm.Close

Set rstSumm = dbsLcl.OpenRecordset("fin_salorderminute_months")
rstLcl.Edit
rstLcl!salmonths = rstSumm!concat_month
rstLcl.Update
rstSumm.Close

Set rstSumm = dbsLcl.OpenRecordset("fin_salorderminute_emps", dbOpenSnapshot)
rstLcl.Edit
rstLcl!empspaid = rstSumm!count_empspaid
rstLcl.Update
rstSumm.Close

Set rstSumm = dbsLcl.OpenRecordset("fin_salorderminute_payments", dbOpenSnapshot)
rstLcl.Edit
rstLcl!paymentsmade = rstSumm!payments
rstLcl!parentorders = rstSumm!parents
rstLcl!childorders = rstSumm!Children
rstLcl.Update
rstSumm.Close
rstLcl.Close


'Make Summary ----------------------------------------------------------------
Set rstLcl = dbsLcl.OpenRecordset("fin_salarysummary_temp", dbOpenDynaset)
Set rstSumm = Me.subSummary.Form.RecordsetClone

'Unmark all rows
rstLcl.MoveFirst
Do While Not rstLcl.EOF
    rstLcl.Edit
    rstLcl!marked = 0
    rstLcl.Update
    rstLcl.MoveNext
    Loop

'If a head exists, mark it otherwise create new
rstSumm.MoveFirst
Do While Not rstSumm.EOF
    rstLcl.MoveFirst
    rstLcl.FindFirst "effheadid = " & rstSumm!sor_effhed_id
    If Not rstLcl.NoMatch Then
        rstLcl.Edit
        rstLcl!marked = True
        rstLcl.Update
        Else
        rstLcl.AddNew
        rstLcl!effheadid = rstSumm!sor_effhed_id
        rstLcl!marked = True
        rstLcl.Update
        End If
    rstSumm.MoveNext
    Loop

'Delete all unmarked
rstLcl.MoveFirst
rstLcl.FindFirst "marked = False"
Do While Not rstLcl.NoMatch
    rstLcl.Delete
    rstLcl.FindNext "marked = False"
    Loop
    
End Sub
Private Sub cmdReport_Click()
Me.Dirty = False
DoCmd.OpenReport "fin_salorderminute", acViewReport
End Sub


VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreqitems_appr_tempchoose"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Me.effhed = Me.OpenArgs
Me.appeffhead = Me.OpenArgs
End Sub
Private Sub cmdApprove_Click()
Dim dbsApprove As Database
Dim rstApprove As Recordset
Set dbsApprove = CurrentDb()
Set rstApprove = Me.RecordsetClone

Me.Dirty = False
dbsApprove.Execute "Update pur_purreqs Set prq_status = 'Approved', prq_appeffhed_id = " & Me!appeffhead & _
                   " Where prq_id = " & rstApprove!pri_prq_id
rstApprove.MoveFirst
Do While Not rstApprove.EOF
    dbsApprove.Execute "Update pur_purreqitems Set pri_appqty = " & rstApprove!pri_appqty & " Where pri_id = " & rstApprove!pri_id
    rstApprove.MoveNext
    Loop
DoCmd.Close
DoCmd.Close acForm, "pur_purreqs_detail"
End Sub



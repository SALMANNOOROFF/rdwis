VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_transfersamount_detail"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit



Private Sub Form_Open(Cancel As Integer)
Dim arrArgs() As String

arrArgs = Split(Me.OpenArgs, "~")
Select Case arrArgs(0)
    Case "Draft"
        Me.RecordSource = "Select * From fin_transfersamount_u Where trf_id = " & arrArgs(1)
        AllowEditsAdvanced Me.Name, False, False
    Case "Closed"
        Me.RecordSource = "Select * From fin_transfersamount_u Where trf_id = " & arrArgs(1)
    Case "DataEntry"
        AllowEditsAdvanced Me.Name, True, False
        Me.AllowAdditions = True
        Me.DataEntry = True
    End Select
Me.Visible = True
End Sub

Private Sub trf_fromhed_AfterUpdate()
Me.trf_fromunt = UnitFromHead(Me.trf_fromhed)
End Sub

Private Sub trf_tohed_AfterUpdate()
Me.trf_tount = UnitFromHead(Me.trf_tohed)
End Sub

Private Sub cmdFinalize_Click()
Dim dbsFin As Database
Dim rstFin As Recordset

Set dbsFin = CurrentDb()

'Add commitment
Set rstFin = dbsFin.OpenRecordset("fin_commitments", dbOpenDynaset, dbSeeChanges)
With rstFin
    .AddNew
    !cmt_docid = Me!trf_id
    !cmt_type = "TO"
    !cmt_date = DateValue(GetNow())
    !cmt_amount = Me!trf_amount
    !cmt_effhed_id = Me!trf_fromhed
    !cmt_effunt_id = UnitFromHead(Me!trf_fromhed)
    !cmt_hed_id = Me!trf_tohead
    !cmt_unt_id = UnitFromHead(Me!trf_tohed)
    !cmt_status = "Awaited"
    .Update
    End With
rstFin.Close
Set rstFin = Nothing
End Sub

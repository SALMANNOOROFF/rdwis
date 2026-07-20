VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcases_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsRev As Database
Dim rstRev As Recordset
Set dbsRev = CurrentDb()

Me.object_id = Forms!pur_purcases_detail!pcs_id
Me.unit_id = Forms!pur_purcases_detail!pcs_intunt_id

Me.pcs_inttax = Forms!pur_purcases_detail!pcs_inttax
Me.pcs_inttax_old = Forms!pur_purcases_detail!pcs_inttax

Me.pcs_midtax = Forms!pur_purcases_detail!pcs_midtax
Me.pcs_midtax_old = Forms!pur_purcases_detail!pcs_midtax

Me.pcs_remarks = Forms!pur_purcases_detail!pcs_remarks
Me.pcs_remarks_old = Forms!pur_purcases_detail!pcs_remarks

Me.pcs_price = Forms!pur_purcases_detail!pcs_price
Me.pcs_price_old = Forms!pur_purcases_detail!pcs_price

Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intResponse As Integer
Dim lngRevId As Long
Dim intRevType As Integer

Me.Dirty = False
Select Case Me.frmOption
    Case 1
        If AnyChangeOnForm = False Then
            MsgBox "No change in data requested.", vbCritical
            Exit Sub
            End If
        intRevType = 2
    Case 2
        intRevType = 1
    End Select

intResponse = MsgBox("A Data Revision Request for purchase case " & Me.object_id & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Purchase Case", Me.object_id, Me.unit_id, intRevType)
DoCmd.Close acForm, "pur_purcases_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "pur_purcases_detail"
DoCmd.Close acForm, "pur_purcases_u"
End Sub

Private Sub pcs_midtax_AfterUpdate()
Me.pcs_price = Me.pcs_price_old - pcs_midtax_old + pcs_midtax
End Sub
Private Sub cmdCancel_Click()
DoCmd.Close
End Sub




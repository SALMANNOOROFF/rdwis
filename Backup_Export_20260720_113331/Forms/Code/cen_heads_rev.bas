VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_heads_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsDR As Database
Dim rstDR As Recordset
Dim strTransType As String
Dim lngHeadId As Long

lngHeadId = Forms!cen_heads_pa_u!hed_id
strTransType = IIf(Forms!cen_heads_pa_u!hed_transtype = 1, "Without GST", "With GST")

Me.unit_id = Forms!cen_heads_pa_u!hed_unt_id
Me.object_code = Forms!cen_heads_pa_u!hed_code
Me.object_id = lngHeadId


Me.hed_opendt = Forms!cen_heads_pa_u!hed_opendt
Me.hed_opendt_old = Forms!cen_heads_pa_u!hed_opendt

Me.hed_transtype = strTransType
Me.hed_transtype_old = strTransType

Me.sha_transtype = strTransType
Me.sha_transtype_old = strTransType

Me.pcs_transtype = strTransType
Me.pcs_transtype_old = strTransType

Me.sor_transtype = strTransType
Me.sor_transtype_old = strTransType

Me.trn_transtype = strTransType
Me.trn_transtype_old = strTransType

Me.cmt_amount = "(" & strTransType & ")"
Me.cmt_amount_old = "(" & strTransType & ")"

If Me.OpenArgs = "Closed" Then Me.Option2.Enabled = True

Me.Visible = True

End Sub

Private Sub hed_transtype_AfterUpdate()
Me.sha_transtype = Me.hed_transtype
Me.pcs_transtype = Me.hed_transtype
Me.sor_transtype = Me.hed_transtype
Me.trn_transtype = Me.hed_transtype
Me.cmt_amount = "(" & Me.hed_transtype & ")"
End Sub

Private Sub cmdGenerate_Click()

Dim dbsGen As Database
Dim rstGen As Recordset
Dim intRevType As Integer
Dim intresponse As Integer
Dim lngRevId As Long

Me.Dirty = False
Select Case Me.frmOption
    Case 1
        If AnyChangeOnForm = False Then
            MsgBox "No change in data requested.", vbCritical
            Exit Sub
            End If
        intRevType = 2
    Case 2
        Exit Sub        'xxxxxxx
        intRevType = 1
    End Select

intresponse = MsgBox("A Data Revision Request for account " & Me.object_code & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Account", Me.object_id, Me.unit_id, intRevType, Me.object_code)
DoCmd.Close acForm, "cen_heads_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "cen_heads_pa_u"
   
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub








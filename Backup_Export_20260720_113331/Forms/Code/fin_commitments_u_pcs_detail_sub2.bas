VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_commitments_u_pcs_detail_sub2"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdReverse_Click()
Dim intresponse  As Integer
Dim lngRevId As Long

intresponse = MsgBox("A Data Revision Request for Payment " & Me!trn_id & ", will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Payment", Me!trn_id, Me.Parent!pcs_intunt_id, 1)
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "fin_commitments_u_pcs_detail"
DoCmd.Close acForm, "fin_commitments_u_pcs"
End Sub

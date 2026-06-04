VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasestada_add"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub pcs_effhed_id_AfterUpdate()
EffHead__AfterUpdate
End Sub

Private Sub pcs_hed_id_AfterUpdate()
Head__AfterUpdate
End Sub

Private Sub cmdSave_Click()
Dim booHead As Boolean
Dim booSudoHead As Boolean

'Checks ---------------------
If IsNull(Me!pcs_date) Then
    MsgBox "Please enter date", vbCritical
    Exit Sub
    End If

If Nz(Me!pcs_title, "") = "" Then
    MsgBox "Please enter title", vbCritical
    Exit Sub
    End If

If IsNull(Me!pcs_effhed_id) Then
    MsgBox "Please enter head", vbCritical
    Exit Sub
    End If

'Execution --------------------
If IsNull(Me!pcs_hed_id) Then
    booHead = True
    Me.pcs_hed_id = 0
    End If

If IsNull(Me!pcs_sudohed) Then
    booSudoHead = True
    Me.pcs_sudohed = ""
    End If

Me.Dirty = False

If booHead = True Then
    CurrentDb().Execute "Update pur_purcases Set pcs_hed_id = Null Where pcs_id = " & Me!pcs_id
    End If

If booSudoHead = True Then
    CurrentDb().Execute "Update pur_purcases Set pcs_sudohed = Null Where pcs_id = " & Me!pcs_id
    End If

DoCmd.OpenForm "pur_purcasestada_detail", acNormal, , , , acHidden, Me!pcs_status & "~" & Me!pcs_id
DoCmd.Close acForm, "pur_purcasestada_add"
End Sub

Private Sub cmdCancel_Click()
Me.Undo
DoCmd.Close
End Sub



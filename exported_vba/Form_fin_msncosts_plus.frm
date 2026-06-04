VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_msncosts_plus"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim rstInstall As Recordset
Dim intEdit As Integer

intEdit = 1
Set rstInstall = Me.RecordsetClone
If getVar("varUnitId") = 800000 Then
    Select Case getVar("varMode")
        Case "editor-m", "approver-m"
            Me.cmdReverse.Visible = True
            Do While Not rstInstall.EOF
                intEdit = intEdit * IIf(rstInstall!mct_cost = 0, 1, 0)
                rstInstall.MoveNext
                Loop
            If intEdit = 1 Then
                AllowEditsAdvanced Me.Name, False, False
                Me.lblEdit.Visible = True
                End If
            End Select
        End If

If Me.Recordset.RecordCount = 0 Then
    Me.lblEdit.Visible = False
    Me.lblMsn.Visible = True
    End If
End Sub

Private Sub Form_Close()
Dim rstCost As Recordset

Me.Dirty = False

If Me.Recordset.RecordCount = 0 Then Exit Sub

If Me.AllowEdits = True And Me.txtTotal <> 0 And Me.txtTotal <> Forms!fin_sharesinstall_add!alloc Then
    Set rstCost = Me.RecordsetClone
    rstCost.MoveFirst
    Do While Not rstCost.EOF
        rstCost.Edit
        rstCost!mct_cost = 0
        rstCost.Update
        rstCost.MoveNext
        Loop
    Me.Refresh
    MsgBox "The sum of milestone costs is not equal to project allocation. Costs not updated.", vbCritical
    End If

End Sub

Private Sub mct_cost_AfterUpdate()
Me.Dirty = False
End Sub

Private Sub cmdReverse_Click()
Dim dbsGen As Database
Dim rstGen As Recordset
Dim qdfGen As QueryDef

'Set dbsGen = CurrentDb()
'dbsGen.Execute "Delete From fin_msncosts_plus_temp"
'Set qdfGen = dbsGen.QueryDefs("fin_msncosts_temp_adder")
'qdfGen.Parameters(0) = Forms!vars!Parameter1
'qdfGen.Execute
'qdfGen.Close
DoCmd.OpenForm "fin_msncosts_rev", acNormal, "", "", , acHidden

End Sub



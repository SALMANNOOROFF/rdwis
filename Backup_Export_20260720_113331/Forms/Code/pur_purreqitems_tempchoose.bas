VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreqitems_tempchoose"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub required_qty_BeforeUpdate(Cancel As Integer)

If Nz(Me!required_qty, 0) > Me!balance_qty Then
    MsgBox "Maximum quantity for this item is " & Me!balance_qty & "."
    Cancel = True
    Me.balance_qty.Undo
    End If
End Sub

Private Sub required_qty_AfterUpdate()
Dim rstRequired As Recordset
Dim intCount As Integer

If IsNull(Me!required_qty) Then Me!required_qty = 0
Me.Dirty = False
intCount = 0
Set rstRequired = Me.RecordsetClone
rstRequired.MoveFirst
Do While Not rstRequired.EOF
    If rstRequired!required_qty > 0 Then intCount = intCount + 1
    rstRequired.MoveNext
    Loop
Me.txtSelected = intCount
If intCount > 0 Then
    Me.cmdCreatePurCase.Enabled = True
    Else
    Me.cmdCreatePurCase.Enabled = False
    End If
End Sub

Private Sub cmdCreatePurCase_Click()
Dim dbsPurCase As Database
Dim rstSource As Recordset
Dim rstDestin As Recordset
Dim qdfPurCase As QueryDef
Dim lngPcsID As Long
Dim intNum As Integer

Me.Dirty = False
Set dbsPurCase = CurrentDb()
Set rstSource = dbsPurCase.OpenRecordset("Select * From pur_purreqitems_temp Where required_qty > 0 Order by pri_serial", dbOpenSnapshot)
'* Check for valid selection
If rstSource.EOF Then
    MsgBox "No item is required."
    Exit Sub
    End If

'* Add purchase case
Set rstDestin = dbsPurCase.OpenRecordset("pur_purcases", dbOpenDynaset, dbSeeChanges)    'LastModified property of recordset requires dbSeeChanges
With rstDestin
    .AddNew
    !pcs_date = DateValue(GetNow())
    !pcs_unt_id = rstSource!unt_id
    !pcs_hed_id = rstSource!hed_id
    !pcs_effhed_id = rstSource!effhed_id
    !pcs_status = "Draft"
    !pcs_purreqs = rstSource!pri_prq_id
    .Update
    .Bookmark = .LastModified
    End With
lngPcsID = rstDestin!pcs_id

'***Add items to purchase case
intNum = 1
Set rstDestin = dbsPurCase.OpenRecordset("pur_purcaseitems", dbOpenDynaset, dbAppendOnly)
Do While Not rstSource.EOF
    With rstDestin
        .AddNew
        !pci_pcs_id = lngPcsID
        !pci_pri_id = rstSource!pri_id
        !pci_serial = intNum
        !pci_desc = rstSource!pri_desc
        !pci_price = rstSource!pri_price
        !pci_qty = rstSource!required_qty
        !pci_qtyunit = rstSource!pri_qtyunit
        .Update
        End With
    intNum = intNum + 1
    rstSource.MoveNext
    Loop
DoCmd.OpenForm "pur_purcases_detail", acNormal, , , , acHidden, "Draft~" & lngPcsID
DoCmd.Close acForm, "pur_purreqitems_tempchoose"
DoCmd.Close acForm, "pur_purreqs_detail"
DoCmd.Close acForm, "pur_purreqs_u"
End Sub


Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

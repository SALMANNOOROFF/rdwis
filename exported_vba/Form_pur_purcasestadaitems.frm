VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purcasestadaitems"
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
    Case "NewRecord"
        AllowEditsAdvanced Me.Name, False, False
        Me.DataEntry = True
        Me.AllowAdditions = True
        Me.cmdDelete.Visible = True
        Me.employee_id.Visible = True
        Me.cmdAddEmpDetails.Visible = True
    Case "Draft", "Under Revision"
        Me.RecordSource = "Select * From pur_purcaseitems Where pci_id = " & arrArgs(1)
        If CheckAnyQuoteExists = False Then
            AllowEditsAdvanced Me.Name, False, False
            Me.cmdDelete.Visible = True
            Me.employee_id.Visible = True
            Me.cmdAddEmpDetails.Visible = True
            End If
    Case Else
        Me.RecordSource = "Select * From pur_purcaseitems Where pci_id = " & arrArgs(1)
    End Select
Me.Visible = True
End Sub

Private Sub cmdAddEmpDetails_Click()
Dim dbsCtr As Database
Dim rstCtr As Recordset
Dim strGrade As String
Dim strBankDetail As String
Dim lngTada As Long

If Nz(Me.employee_id, "") = "" Then
    MsgBox "Please Select an employee first", vbCritical
    Exit Sub
    End If
    
Set dbsCtr = CurrentDb()
Set rstCtr = dbsCtr.OpenRecordset("Select * From hr_contracts_last Where ctr_num = '" & Me.employee_id & "'", dbOpenSnapshot)
strGrade = rstCtr!ctr_grade
lngTada = GetTadaforSalary(rstCtr!ctr_salary)
rstCtr.Close

Set rstCtr = dbsCtr.OpenRecordset("SELECT bac_bnkname, bac_bchcode, bac_accnum FROM hr_bnkaccountsforpay " & _
                               "Where bac_emp_id = '" & Me.employee_id & "'", dbOpenSnapshot)
If rstCtr.EOF = False Then
    If rstCtr.RecordCount > 1 Then
        MsgBox "Multiple bank accounts are marked for salary of " & Me.employee_id & ". Please correct bank account data.", vbCritical
        Exit Sub
        End If
    If rstCtr!bac_bnkname = "Meezan Bank Ltd" Then
        strBankDetail = rstCtr!bac_accnum & " (" & rstCtr!bac_bchcode & ")"
        Else
        strBankDetail = "(Pay by Cheque)"
        End If
    Else
    strBankDetail = "(Pay by Cheque)"
    rstCtr.Close
    End If
rstCtr.Close

Me.pci_desc = "TA/DA for " & NameComplete(Me.employee_id.Column(1), Me.employee_id.Column(2), Me.employee_id.Column(3)) & ", " & _
              "ID: " & Me.employee_id.Column(0) & ", " & "Grade: " & strGrade & vbCrLf & _
              "Meezan Account: " & strBankDetail
Me.pci_price = lngTada
End Sub

Private Function CheckAnyQuoteExists() As Boolean
Dim dbsChkQuote As Database
Dim rstChkQuote As Recordset

Set dbsChkQuote = CurrentDb()
Set rstChkQuote = dbsChkQuote.OpenRecordset("Select qte_pcs_id From pur_quotes Where qte_pcs_id = " & Me!pci_pcs_id, dbOpenSnapshot)
If rstChkQuote.EOF = False Then CheckAnyQuoteExists = True
End Function

Private Sub cmdDelete_Click()
Dim intResponse  As Integer

intResponse = MsgBox("Are you sure you want to delete this quote?", 4, "Deletion Confirmation")
If intResponse <> 6 Then Exit Sub
CurrentDb.Execute "DELETE FROM pur_purcaseitems WHERE pci_id = " & Me!pci_id
Forms!pur_purcasestada_detail.chkDataUpdateRequired = -1
DoCmd.Close
   
End Sub

Private Sub Form_AfterUpdate()
Forms!pur_purcasestada_detail.chkDataUpdateRequired = -1
End Sub


Private Sub pci_type_AfterUpdate()
Me.pci_subtype.Requery
End Sub



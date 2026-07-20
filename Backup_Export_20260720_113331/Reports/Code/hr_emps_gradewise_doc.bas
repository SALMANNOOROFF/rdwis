VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_emps_gradewise_doc"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Report_Open(Cancel As Integer)
Dim dbsReport As Database
Dim rstReport As Recordset
Set dbsReport = CurrentDb()
Set rstReport = dbsReport.OpenRecordset("SELECT hr_contracts.ctr_num, Count(hr_emps_u_active.emp_id) AS EmpsWithoutContract " & _
                                        "FROM hr_emps_u_active LEFT JOIN hr_contracts ON hr_emps_u_active.emp_id = hr_contracts.ctr_num " & _
                                        "GROUP BY hr_contracts.ctr_num " & _
                                        "HAVING (((hr_contracts.ctr_num) Is Null));", dbOpenSnapshot)
If Not rstReport.EOF Then
    Me.lblNote.Caption = "Note: Contract data of " & rstReport!EmpsWithoutContract & " employee(s) not available."
    End If
End Sub

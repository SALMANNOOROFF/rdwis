VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_hr_emps_qualifwise_doc"
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
Set rstReport = dbsReport.OpenRecordset("SELECT hr_empsexta.empexta_emp_id, Count(hr_emps_u_active.emp_id) AS EmpsWithoutRecord " & _
                                        "FROM hr_emps_u_active LEFT JOIN hr_empsexta ON hr_emps_u_active.emp_id = hr_empsexta.empexta_emp_id " & _
                                        "GROUP BY hr_empsexta.empexta_emp_id " & _
                                        "HAVING (((hr_empsexta.empexta_emp_id) Is Null));", dbOpenSnapshot)
If Not rstReport.EOF Then
    Me.lblNote.Caption = "Note: Qualification data of " & rstReport!EmpsWithoutRecord & " employee(s) not available."
    End If
End Sub





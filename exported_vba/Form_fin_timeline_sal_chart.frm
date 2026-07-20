VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_timeline_sal_chart"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbase As Database
Dim rstSalPur As Recordset
Dim s As String

s = Forms!fin_headstatus_details.txtHrSubhead
Me.prj_alloc_sal = Forms!fin_headstatus_details.Controls("prj_alloc_" & s)
Me.prj_exp_sal = Forms!fin_headstatus_details.Controls("prj_exp_" & s)
Me.forecast_sal = GetPrjSalForecast(Forms!vars!Parameter1)
If Me.forecast_sal = 0 Then
    MsgBox "There is no salary forecast for this project.", vbCritical
    DoCmd.Close
    Exit Sub
    End If

Set dbase = CurrentDb()
Set rstSalPur = dbase.OpenRecordset("Select * From fin_stateshd_temp Where type = 'HR'")
rstSalPur.Edit
rstSalPur!forecast = Me.forecast_sal
rstSalPur![Can be spent after Forecast] = rstSalPur![Can be Spent] - Me.forecast_sal
rstSalPur.Update
End Sub

Private Sub FormHeader_Click()
MsgBox Me.prj_exp_sal
End Sub

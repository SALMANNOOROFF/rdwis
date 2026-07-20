VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_timeline_ctr_chart"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim dbsCtr As Database
Dim rstCtr As Recordset
Dim chtCtr As graph.chart
Dim dtMin As Date
Dim dtMax As Date

Set dbsCtr = CurrentDb()
Set rstCtr = dbsCtr.OpenRecordset("Select Min(start_date) As min, Max(end_date) AS max " & _
                                  "From fin_timeline_ctr1", dbOpenSnapshot)
If IsNull(rstCtr!Min) Then
    MsgBox "There is no active contract for this project.", vbCritical
    DoCmd.Close
    Exit Sub
    End If

dtMin = DateSerial(Year(rstCtr!Min), 1, 1)
dtMax = DateSerial(Year(rstCtr!Max) + 1, 1, 2)

Set chtCtr = Me.Chart1.Object
chtCtr.Axes(2, 1).MinimumScale = dtMin
chtCtr.Axes(2, 2).MinimumScale = dtMin
chtCtr.Axes(2, 1).MaximumScale = dtMax
chtCtr.Axes(2, 2).MaximumScale = dtMax

End Sub


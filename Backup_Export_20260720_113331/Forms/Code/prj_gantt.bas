VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_gantt"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
On Error GoTo Error_Handler

Dim rst As Recordset
Dim ctl As Control
Dim n As Integer
Dim curX As Integer
Dim curY As Integer

Dim margin As Integer
Dim gap As Integer
Dim descWidth As Integer
Dim graphWidth As Integer
Dim axisLabelHeight As Integer
Dim sectionHeight As Integer
Dim axisGroup As Integer    'Number of months in in a group along x axis
Dim axisDateFont As Integer

Dim chartX As Integer
Dim chartY As Integer
Dim graphX As Integer
Dim graphY As Integer
Dim sectionCount As Integer
Dim graphHeight As Integer
Dim graphScale As Single   'length of 1 day in twips
Dim titleHeight As Integer
Dim bar1Width As Integer
Dim bar2Width As Integer
Dim barHeight As Integer
Dim dmndWidth As Integer
Dim dmndHeight As Integer
Dim axisDiv As Integer
Dim axisDiv1 As Integer
Dim axisMonth1 As Date
Dim axisDate As Date
Dim axisDate1 As Date
Dim axisLabelWidth As Integer
Dim tooltipText As String

Dim PrjId As Long
Dim prjTitle As String
Dim prjStartDt As Date
Dim prjEndDt As Date
Dim itemStartDt As Date
Dim itemTargetDt As Date
Dim actComp As Integer
Dim itemActualDt As Date
Dim ItemStatus As String

'Dimensions -------------------------------------------------------------------------------

'Basic dimensions assignment
margin = 200
gap = 100
descWidth = 5000
graphWidth = 14000
axisLabelHeight = 200
sectionHeight = 600
axisGroup = 3

'Derived dimensions
chartX = margin
chartY = margin
graphX = margin + descWidth + gap
graphY = margin + axisLabelHeight
titleHeight = sectionHeight / 2
barHeight = sectionHeight / 1.5
dmndWidth = sectionHeight
dmndHeight = sectionHeight

PrjId = Screen.ActiveForm!prj_id
Set rst = CurrentDb.OpenRecordset("Select * From prj_projects Where prj_id = " & PrjId)
prjTitle = rst!prj_title
prjStartDt = rst!prj_startdt
prjEndDt = rst!prj_estenddt
graphScale = graphWidth / (prjEndDt - prjStartDt)
axisDiv = graphScale * 30.4167 * axisGroup
axisLabelWidth = axisDiv / 2
axisMonth1 = Int((DatePart("m", prjStartDt) + axisGroup) / axisGroup) * axisGroup + 1
axisMonth1 = IIf(axisMonth1 > 12, axisMonth1 - 12, axisMonth1)
axisDate1 = DateSerial(IIf(axisMonth1 <= axisGroup, DatePart("yyyy", prjStartDt) + 1, DatePart("yyyy", prjStartDt)), axisMonth1, 1)
axisDiv1 = (axisDate1 - prjStartDt) * graphScale

Set rst = CurrentDb.OpenRecordset("Select * From prj_milestones Where msn_xprj_id = " & PrjId & " Order by msn_id")
rst.MoveLast
sectionCount = rst.RecordCount
rst.MoveFirst
graphHeight = (sectionCount * sectionHeight)


'Creating Header   ---------------------------------------------------------------------------------------------------
Me.txtTitle.Width = descWidth + graphWidth + (2 * margin)
Me.txtTitle = prjTitle
Me.txtSubTitle.Width = descWidth + graphWidth + (2 * margin)
Me.txtSubTitle = "Start Date:  " & Format(prjStartDt, "dd mmm yy") & "          " & "Estimated Completion date:  " & Format(prjEndDt, "dd mmm yy")


'Creating structure  -------------------------------------------------------------------------------------------------
SetControl "gBorder", graphX, graphY, graphWidth, graphHeight
curX = graphX + axisDiv1
SetControl "gline1", curX, graphY, 0, graphHeight                                       'First graphline
SetControl "uDate1", curX, graphY - axisLabelHeight, axisLabelWidth, axisLabelHeight    'Upper label for first graphline
Me("uDate1").Caption = UCase(Format(axisDate1, "mmm yy"))
SetControl "lDate1", curX, graphY + graphHeight, axisLabelWidth, axisLabelHeight        'Lower label for first graphline
Me("lDate1").Caption = UCase(Format(axisDate1, "mmm yy"))
n = 2
axisDate = DateAdd("m", axisGroup, axisDate1)
curX = curX + axisDiv
Do While curX < graphX + graphWidth
    SetControl "gline" & n, curX, graphY, 0, graphHeight                                 'All lines in between
    SetControl "uDate" & n, curX, graphY - axisLabelHeight, axisLabelWidth, axisLabelHeight
    Me("uDate" & n).Caption = UCase(Format(axisDate, "mmm yy"))
    SetControl "lDate" & n, curX, graphY + graphHeight, axisLabelWidth, axisLabelHeight
    Me("lDate" & n).Caption = UCase(Format(axisDate, "mmm yy"))
    axisDate = DateAdd("m", axisGroup, axisDate)
    curX = curX + axisDiv
    n = n + 1
    Loop


'Populating structure   --------------------------------------------------------------------------------------------------------------------
n = 1
curY = chartY + axisLabelHeight
Do While rst.EOF = False
    curX = chartX
    'Item titles
    SetControl "iDesc" & n, curX, curY + (sectionHeight - titleHeight) / 2, descWidth, titleHeight
    Me("iDesc" & n).Caption = rst!msn_desc
    tooltipText = rst!msn_desc
    Me("iDesc" & n).ControlTipText = tooltipText
    itemTargetDt = rst!msn_targetdt
    ItemStatus = rst!msn_status
    If rst!msn_type = "Activity" Then
        'Activities
        itemStartDt = rst!msn_startdt
        actComp = rst!msn_comp
        tooltipText = tooltipText & vbNewLine & "Start date: " & Format(itemStartDt, "dd mmm yy") & vbNewLine & "End date: " & Format(itemTargetDt, "dd mmm yy") & _
                      vbNewLine & IIf(ItemStatus = "Completed", "Actual completion date: " & Format(rst!msn_achvdt, "dd mmm yy"), "")
        If actComp > 0 Then
            curX = graphX + DateDiff("d", prjStartDt, itemStartDt) * graphScale + 20
            bar1Width = DateDiff("d", itemStartDt, itemTargetDt) * graphScale * actComp / 100 - 40
            SetControl "cAct" & n, curX, curY + (sectionHeight - barHeight) / 2, bar1Width, barHeight
            Me("cAct" & n).ControlTipText = tooltipText
        End If
        If actComp < 100 Then
            curX = graphX + DateDiff("d", prjStartDt, itemStartDt) * graphScale + DateDiff("d", itemStartDt, itemTargetDt) * graphScale * actComp / 100 + 20
            bar2Width = DateDiff("d", itemStartDt, itemTargetDt) * graphScale * (100 - actComp) / 100 - 40
            SetControl "rAct" & n, curX, curY + (sectionHeight - barHeight) / 2, bar2Width, barHeight
            Me("rAct" & n).ControlTipText = tooltipText
            End If
        Else
        'Milestones
        tooltipText = tooltipText & vbNewLine & "Start date: " & Format(itemStartDt, "dd mmm yy") & vbNewLine & "End date: " & Format(itemTargetDt, "dd mmm yy") & _
                      vbNewLine & IIf(ItemStatus = "Completed", "Actual completion date: " & Format(rst!msn_achvdt, "dd mmm yy"), "")
        curX = graphX + DateDiff("d", prjStartDt, itemTargetDt) * graphScale
        If rst!msn_status = "Completed" Then
            SetControl "cMsn" & n, curX - dmndWidth / 2, curY + (sectionHeight - dmndHeight) / 2, dmndWidth, dmndHeight
            Me("cMsn" & n).ControlTipText = tooltipText
            Else
            SetControl "rMsn" & n, curX - dmndWidth / 2, curY + (sectionHeight - dmndHeight) / 2, dmndWidth, dmndHeight
            Me("rMsn" & n).ControlTipText = tooltipText
            End If
        End If
    curY = curY + sectionHeight
    n = n + 1
    rst.MoveNext
    Loop

'Today line
If Date > prjStartDt And Date < prjEndDt Then
    curY = graphY
    curX = graphX + DateDiff("d", prjStartDt, Date) * graphScale
    SetControl "dline", curX, curY, 0, graphHeight
    End If

'Chart generation date
Me.txtdate.Left = descWidth + graphWidth + (2 * margin) - Me.txtdate.Width
Me.txtdate = "Chart generated on   " & Format(Date, "dd mmm yy")

'Max are limits 19000 x 9000 twips (13.1944 x 6.25")
'1" = 1440 twips
'1 point = 20 twips

'SetControl "gline30", 200, 400, 18000, 0
'SetControl "gline31", 200, 1000, 18000, 0
'SetControl "gline32", 200, 1600, 18000, 0
'SetControl "gline33", 200, 2200, 18000, 0
'SetControl "gline34", 200, 2800, 18000, 0

The_End:
Exit Sub

Error_Handler:
MsgBox "Gantt Chart cannot be displayed due to incorrect data.", vbCritical

End Sub

Private Sub SetControl(ctlName As String, intLeft As Integer, intTop As Integer, intWidth As Integer, intHeight As Integer)
With Me(ctlName)
    .Left = intLeft
    .Top = intTop
    .Width = intWidth
    .Height = intHeight
    .Visible = True
    End With
End Sub




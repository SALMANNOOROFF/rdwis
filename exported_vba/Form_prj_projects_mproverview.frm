VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_prj_projects_mproverview"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit


Private Sub cmdDetails_Click()
On Error GoTo cmdDetails_Click_Err

DoCmd.OpenForm "prj_projects_detail_mpr", acNormal, , , , acHidden, Me.prj_status & "~" & Me.prj_id

cmdDetails_Click_Exit:
    Exit Sub

cmdDetails_Click_Err:
    MsgBox Error$
    Resume cmdDetails_Click_Exit

End Sub

Private Sub cmdPreview_Click()
On Error GoTo cmdPreview_Click_Err

DoCmd.OpenReport Me.cmbChoosePrint, acViewPreview, "", "", acNormal

cmdPreview_Click_Exit:
    Exit Sub

cmdPreview_Click_Err:
    MsgBox Error$
    Resume cmdPreview_Click_Exit

End Sub

Private Sub cmdWord_Click()
Select Case Me.cmbChoosePrint
    Case "prj_mpr-i"
    PrintMPR_I
    Case "prj_mpr-ii"
    PrintMPR_II
    End Select
End Sub

Private Sub PrintMPR_I()

On Error GoTo Error_Handler

Dim appWord As Object
Dim docMPR As Object
Dim rng As Word.Range
Dim sel As Word.Selection
Dim tbl As Word.Table
Dim countProject As Integer, countMilestones
Dim dbs As Database
Dim qdf As QueryDef
Dim rst As Recordset, rst2 As Recordset
Dim n As Integer
 
Set dbs = CurrentDb
Set qdf = dbs.QueryDefs("prj_mpr-i")
Set rst = qdf.OpenRecordset()

If rst.EOF = True Then
    MsgBox "There is no case for MPR part 1"
    Exit Sub
    End If

DoCmd.Hourglass True
countProject = 1
Set appWord = CreateObject("Word.Application")
Set docMPR = appWord.Documents.Add()    'Template:="F:\Users\AD\Documents\Custom Office Templates\Projects return.dotx"

Set sel = appWord.Selection
docMPR.PageSetup.PaperSize = wdPaperA4
docMPR.PageSetup.TopMargin = InchesToPoints(1)
docMPR.PageSetup.BottomMargin = InchesToPoints(1)
docMPR.PageSetup.RightMargin = InchesToPoints(0.75)
docMPR.PageSetup.LeftMargin = InchesToPoints(0.75)
sel.Font.Name = "Arial"

sel.ParagraphFormat.Alignment = wdAlignParagraphCenter
sel.ParagraphFormat.SpaceAfter = 16
sel.Font.Size = 14
sel.Font.Bold = True
sel.Font.Underline = wdUnderlineSingle
sel.TypeText "PART I - ONGOING PROJECTS PROGRESS" & vbCrLf
sel.ParagraphFormat.Alignment = wdAlignParagraphLeft
sel.Font.Size = 12

Do While rst.EOF = False

    sel.ParagraphFormat.SpaceAfter = 12
    sel.Font.Bold = True
    sel.Font.Underline = wdUnderlineSingle
    sel.TypeText "PROJECT " & countProject & ": " & UCaseSmart(rst!prj_title) & vbCrLf
    sel.Font.Bold = False
    sel.Font.Underline = wdUnderlineNone
    sel.ParagraphFormat.SpaceBefore = 4
    sel.ParagraphFormat.SpaceAfter = 4

    Set rng = sel.Range
    docMPR.Tables.Add rng, 2, 4
    Set tbl = docMPR.Tables(countProject * 3 - 2)
    tbl.Borders.InsideLineStyle = wdLineStyleSingle
    tbl.Borders.OutsideLineStyle = wdLineStyleSingle
    tbl.Columns(1).Width = InchesToPoints(0.94)
    tbl.Columns(2).Width = InchesToPoints(2.83)
    tbl.Columns(3).Width = InchesToPoints(1.88)
    tbl.Columns(4).Width = InchesToPoints(1.11)
    tbl.Cell(1, 1).Range.InsertAfter "PI"
    tbl.Cell(1, 2).Range.InsertAfter NameComplete(rst!unt_leadname, rst!unt_leadtitle, rst!unt_leadrank)
    tbl.Cell(1, 3).Range.InsertAfter "Start Date"
    tbl.Cell(1, 4).Range.InsertAfter Format(rst!prj_startdt, "mmm yy")
    tbl.Cell(1, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(2, 1).Range.InsertAfter "Sponsor"
    tbl.Cell(2, 2).Range.InsertAfter rst!prj_sponsor
    tbl.Cell(2, 3).Range.InsertAfter "Sch Completion Date"
    tbl.Cell(2, 4).Range.InsertAfter Format(rst!prj_estenddt, "mmm yy")
    tbl.Cell(2, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(1, 1).Range.Font.Bold = True
    tbl.Cell(1, 3).Range.Font.Bold = True
    tbl.Cell(2, 1).Range.Font.Bold = True
    tbl.Cell(2, 3).Range.Font.Bold = True
'    tbl.Cell(1, 1).Range.Underline = wdUnderlineSingle
'    tbl.Cell(1, 3).Range.Underline = wdUnderlineSingle
'    tbl.Cell(2, 1).Range.Underline = wdUnderlineSingle
'    tbl.Cell(2, 3).Range.Underline = wdUnderlineSingle
    
    sel.EndKey Unit:=wdStory  'Move to the end
    sel.ParagraphFormat.SpaceBefore = 2
    sel.ParagraphFormat.SpaceAfter = 2
    sel.TypeParagraph
    
    sel.Font.Bold = True
    sel.TypeText "Milestones:"
    sel.Font.Bold = False
    
    Set rng = sel.Range
    docMPR.Tables.Add rng, 1, 5
    Set tbl = docMPR.Tables(countProject * 3 - 1)
    tbl.Borders.InsideLineStyle = wdLineStyleSingle
    tbl.Borders.OutsideLineStyle = wdLineStyleSingle
    tbl.Columns(1).Width = InchesToPoints(0.56)
    tbl.Columns(2).Width = InchesToPoints(3)
    tbl.Columns(3).Width = InchesToPoints(1.1)
    tbl.Columns(4).Width = InchesToPoints(1.1)
    tbl.Columns(5).Width = InchesToPoints(1.02)
    tbl.Cell(1, 1).Range.InsertAfter "S No"
    tbl.Cell(1, 1).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(1, 2).Range.InsertAfter "Key Milestones"
    tbl.Cell(1, 3).Range.InsertAfter "Work Order Timeline"
    tbl.Cell(1, 3).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(1, 4).Range.InsertAfter "Achieved Timeline"
    tbl.Cell(1, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(1, 5).Range.InsertAfter "Status"
    countMilestones = 1
    Set rst2 = dbs.OpenRecordset("Select * From prj_milestones Where msn_type = 'Milestone' And msn_xprj_id = " & rst!prj_id & " Order by msn_id", dbOpenForwardOnly)
    Do While rst2.EOF = False
        sel.Tables(1).Rows.Add        'sel.Rows.Add
        tbl.Cell(countMilestones + 1, 1).Range.InsertAfter countMilestones & "."
        tbl.Cell(countMilestones + 1, 1).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
        tbl.Cell(countMilestones + 1, 2).Range.InsertAfter rst2!msn_desc
        tbl.Cell(countMilestones + 1, 3).Range.InsertAfter Format(rst2!msn_targetdt, "mmm yy")
        tbl.Cell(countMilestones + 1, 3).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
        tbl.Cell(countMilestones + 1, 4).Range.InsertAfter Format(rst2!msn_achvdt, "mmm yy")
        tbl.Cell(countMilestones + 1, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
        tbl.Cell(countMilestones + 1, 5).Range.InsertAfter rst2!msn_status
        countMilestones = countMilestones + 1
        rst2.MoveNext
        Loop
        rst2.Close
        Set rst2 = Nothing
    tbl.Rows(1).Range.Font.Bold = True
    sel.EndKey Unit:=wdStory  'Move to the end
    sel.TypeParagraph
    
    sel.Font.Bold = True
    sel.TypeText "Progress:"
    sel.Font.Bold = False
    
    Set rng = sel.Range
    docMPR.Tables.Add rng, 2, 2
    Set tbl = docMPR.Tables(countProject * 3)
    tbl.Borders.InsideLineStyle = wdLineStyleSingle
    tbl.Borders.OutsideLineStyle = wdLineStyleSingle
    tbl.Rows(1).Range.Font.Bold = True
    tbl.Columns(1).Width = InchesToPoints(3.4)
    tbl.Columns(2).Width = InchesToPoints(3.4)
    tbl.Cell(1, 1).Range.InsertAfter "Previous Progress"
    tbl.Cell(1, 2).Range.InsertAfter "Current Progress"
    tbl.Cell(2, 1).Range.InsertAfter Nz(rst!last_progress, "")
    tbl.Cell(2, 1).Range.ParagraphFormat.Alignment = wdAlignParagraphJustify
    tbl.Cell(2, 2).Range.InsertAfter rst!current_progress
    tbl.Cell(2, 2).Range.ParagraphFormat.Alignment = wdAlignParagraphJustify

    
    Set rng = tbl.Cell(2, 1).Range
    MakeReplacements rng
    Set rng = tbl.Cell(2, 2).Range
    MakeReplacements rng
    
    sel.EndKey Unit:=wdStory  'Move to the end
    sel.InsertBreak
    sel.EndKey Unit:=wdStory  'Move to the end
    
    countProject = countProject + 1
    rst.MoveNext
    
    Loop
sel.TypeBackspace
sel.TypeBackspace
sel.TypeBackspace

appWord.Visible = True
DoCmd.Hourglass False

The_End:
rst.Close
Set rst = Nothing
Set docMPR = Nothing
Set appWord = Nothing
Exit Sub

Error_Handler:
If Err.Number = 462 Then
    appWord.Visible = True
    DoCmd.Hourglass False
    MsgBox "Ms Word is not ready. Plese try again"
    Else
    'MsgBox "Error, but proceeding"
    Resume Next
    End If
GoTo The_End

End Sub

Private Sub PrintMPR_II()
On Error GoTo Error_Handler

Dim appWord As Object
Dim docMPR As Object
Dim rng As Word.Range
Dim sel As Word.Selection
Dim tbl As Word.Table
Dim countProject As Integer, countMilestones
Dim dbs As Database
Dim qdf As QueryDef
Dim rst As Recordset, rst2 As Recordset
 
Set dbs = CurrentDb
Set qdf = dbs.QueryDefs("prj_mpr-ii")
Set rst = qdf.OpenRecordset()

If rst.EOF = True Then
    MsgBox "There is no case for MPR part 2"
    Exit Sub
    End If

DoCmd.Hourglass True
countProject = 1
Set appWord = CreateObject("Word.Application")
Set docMPR = appWord.Documents.Add()    'Template:="F:\Users\AD\Documents\Custom Office Templates\Projects return.dotx"

Set sel = appWord.Selection
docMPR.PageSetup.PaperSize = wdPaperA4
docMPR.PageSetup.TopMargin = InchesToPoints(1)
docMPR.PageSetup.BottomMargin = InchesToPoints(1)
docMPR.PageSetup.RightMargin = InchesToPoints(0.75)
docMPR.PageSetup.LeftMargin = InchesToPoints(0.75)
sel.Font.Name = "Arial"


sel.ParagraphFormat.Alignment = wdAlignParagraphCenter
sel.ParagraphFormat.SpaceAfter = 16
sel.Font.Size = 14
sel.Font.Bold = True
sel.Font.Underline = wdUnderlineSingle
sel.TypeText "PART II - APPROVED PROJECTS FUNDING AWAITED" & vbCrLf
sel.ParagraphFormat.Alignment = wdAlignParagraphLeft
sel.Font.Size = 12

Do While rst.EOF = False

    sel.ParagraphFormat.SpaceAfter = 12
    sel.Font.Bold = True
    sel.Font.Underline = wdUnderlineSingle
    sel.TypeText "PROJECT " & countProject & ": " & UCaseSmart(rst!prj_title) & vbCrLf
    sel.Font.Bold = False
    sel.Font.Underline = wdUnderlineNone
    sel.ParagraphFormat.SpaceBefore = 4
    sel.ParagraphFormat.SpaceAfter = 4

    Set rng = sel.Range
    docMPR.Tables.Add rng, 3, 4
    Set tbl = docMPR.Tables(countProject * 2 - 1)
    tbl.Columns(1).Width = InchesToPoints(2.06)
    tbl.Columns(2).Width = InchesToPoints(1.4)
    tbl.Columns(3).Width = InchesToPoints(2.28)
    tbl.Columns(4).Width = InchesToPoints(1.01)
    tbl.Cell(1, 1).Range.InsertAfter "Principal Investigator:"
    tbl.Cell(1, 2).Range.InsertAfter NameComplete(rst!unt_leadname, rst!unt_leadtitle, rst!unt_leadrank)
    tbl.Cell(1, 2).Merge tbl.Cell(1, 4)
    tbl.Cell(2, 1).Range.InsertAfter "Sponsoring Directorate:"
    tbl.Cell(2, 2).Range.InsertAfter rst!prj_sponsor
    tbl.Cell(2, 2).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(2, 3).Range.InsertAfter "Total Funds Approved:"
    tbl.Cell(2, 4).Range.InsertAfter rst!prj_aprvcost / 1000000 & " M"
    tbl.Cell(2, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(3, 1).Range.InsertAfter "Project Approval Date:"
    tbl.Cell(3, 2).Range.InsertAfter Format(rst!prj_aprvdt, "mmm yy")
    tbl.Cell(3, 2).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(3, 3).Range.InsertAfter "Funds for CFY:"
    tbl.Cell(3, 4).Range.InsertAfter rst!prj_cfycost / 1000000 & " M"
    tbl.Cell(3, 4).Range.ParagraphFormat.Alignment = wdAlignParagraphCenter
    tbl.Cell(1, 1).Range.Font.Bold = True
    tbl.Cell(2, 1).Range.Font.Bold = True
    tbl.Cell(2, 3).Range.Font.Bold = True
    tbl.Cell(3, 1).Range.Font.Bold = True
    tbl.Cell(3, 3).Range.Font.Bold = True
'    tbl.Cell(1, 1).Range.Underline = wdUnderlineSingle
'    tbl.Cell(2, 1).Range.Underline = wdUnderlineSingle
'    tbl.Cell(2, 3).Range.Underline = wdUnderlineSingle
'    tbl.Cell(3, 1).Range.Underline = wdUnderlineSingle
'    tbl.Cell(3, 3).Range.Underline = wdUnderlineSingle

    sel.EndKey Unit:=wdStory  'Move to the end
    sel.ParagraphFormat.SpaceBefore = 2
    sel.ParagraphFormat.SpaceAfter = 2
    'sel.TypeParagraph
    
    sel.Font.Bold = True
    sel.TypeText "Status:"
    sel.Font.Bold = False

    
    Set rng = sel.Range
    docMPR.Tables.Add rng, 1, 1
    Set tbl = docMPR.Tables(countProject * 2)
    tbl.Borders.InsideLineStyle = wdLineStyleSingle
    tbl.Borders.OutsideLineStyle = wdLineStyleSingle
    tbl.Cell(1, 1).Range.InsertAfter rst!current_progress
    tbl.Cell(1, 1).Range.ParagraphFormat.Alignment = wdAlignParagraphJustify
    sel.EndKey Unit:=wdStory  'Move to the end
    sel.TypeParagraph
    sel.TypeParagraph
    'sel.TypeParagraph

    
    countProject = countProject + 1
    rst.MoveNext
    
    Loop
sel.TypeBackspace
sel.TypeBackspace
sel.TypeBackspace
appWord.Visible = True
DoCmd.Hourglass False

The_End:
rst.Close
Set rst = Nothing
Set docMPR = Nothing
Set appWord = Nothing
Exit Sub

Error_Handler:
If Err.Number = 462 Then
    DoCmd.Hourglass False
    MsgBox "Ms Word is not ready. Plese try again"
    docMPR.Close wdDoNotSaveChanges
    appWord.Visible = True
    Else
    'MsgBox "Error, but proceeding"
    Resume Next
    End If
GoTo The_End

End Sub

Sub MakeReplacements(inpRange As Range)
Dim Para As Paragraph
Dim count As Integer, x As Integer
Dim strText As String

ReplaceText inpRange, "^p^p", "^p", "All"
ReplaceText inpRange, String(23, " "), "^t^t^t", "All"
ReplaceText inpRange, String(14, " "), "^t^t", "All"
ReplaceText inpRange, String(7, " "), "^t", "All"
ReplaceText inpRange, String(3, " "), "^t", "All"
inpRange.Paragraphs.TabStops.Add InchesToPoints(0.3)
inpRange.Paragraphs.TabStops.Add InchesToPoints(0.6)
inpRange.Paragraphs.TabStops.Add InchesToPoints(0.9)
inpRange.Paragraphs.TabStops.Add InchesToPoints(1.2)

End Sub
 
Sub ReplaceText(rnge As Range, strFind As String, strReplace As String, strReplacements As String)
With rnge.Find
    .ClearFormatting
    .Text = strFind
    .Replacement.ClearFormatting
    .Replacement.Text = strReplace
    Select Case strReplacements
        Case "One": .Execute Replace:=wdReplaceOne, Forward:=True, Wrap:=wdFindStop
        Case "All": .Execute Replace:=wdReplaceAll, Forward:=True, Wrap:=wdFindStop
        End Select
    End With

End Sub

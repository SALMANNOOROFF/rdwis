Attribute VB_Name = "Tools"
Option Compare Database
Option Explicit

Sub UpdateStatusBarTexts()

Dim dbs As Database
Dim tbl As TableDef
Dim fld As field
Dim frm As Form
Dim ctl As Control
Dim strTableName As String
Dim strFormName As String

strTableName = InputBox("Please enter table name")
strFormName = InputBox("Please enter form name")
Set dbs = CurrentDb
Set tbl = dbs.TableDefs(strTableName)
DoCmd.OpenForm strFormName, acDesign
Set frm = Forms(strFormName)

For Each fld In tbl.Fields
    For Each ctl In frm.Controls
        If ctl.ControlType = acTextBox Or ctl.ControlType = acComboBox Then
            If ctl.ControlSource = fld.Name Then ctl.StatusBarText = fld.Properties("Description")
            End If
        Next
    Next
DoCmd.Close acForm, strFormName, acSaveYes
MsgBox "Status bar texts updated for all controls in the form " & strFormName & "."

End Sub

Sub DataCaptured()
Dim dbs As Database
Dim tbl As TableDef
Dim fld As field
On Error Resume Next
Set dbs = CurrentDb
For Each tbl In dbs.TableDefs
    'If tbl.Properties("name") Like "MSys*" Then GoTo Next_Iteration
    For Each fld In tbl.Fields
        Debug.Print fld.Name & " - " & fld.Properties("Description")
        Next
Next_Iteration:
    Next
End Sub

Sub SetProperty()
Dim d As Database
Dim p As Property
Set d = CurrentDb()
Set p = d.CreateProperty("ReadyForDeployment", dbBoolean, False)
d.Properties.Append p
End Sub

Sub ShowProperty()
Debug.Print CurrentDb().Properties("ReadyForDeployment")
End Sub

Sub DeleteProperty()
CurrentDb().Properties.Delete ("ReadyForProduction")
End Sub

Sub CloseAllForms()
Dim obj As AccessObject, dbs As Object
Set dbs = Application.CurrentProject
For Each obj In dbs.AllForms
  If obj.IsLoaded = True Then
    DoCmd.Close acForm, obj.Name, acSaveYes
  End If
Next obj
End Sub

Function SearchInQueries(SearchString As String) As String
Dim dbsSearch As Database
Dim qdfSearch As QueryDef
Dim strOutput As String

Set dbsSearch = CurrentDb()
For Each qdfSearch In dbsSearch.QueryDefs
    If qdfSearch.sql Like "*" & SearchString & "*" Then
        strOutput = strOutput & qdfSearch.Name & vbCrLf
        'strOutput = strOutput & qdfSearch.sql & vbCrLf
        End If
    Next
SearchInQueries = strOutput
End Function

Sub PopulateContractPlans()
Dim dbsCplan As Database
Dim rstCPlan As Recordset
Dim rstCtr As Recordset
Dim dtStartDate As Date
Dim dtEndDate As Date
Dim dtContractEnd As Date

Set dbsCplan = CurrentDb()
Set rstCPlan = dbsCplan.OpenRecordset("hr_contractplans")
Set rstCtr = dbsCplan.OpenRecordset("Select * From hr_contracts Order By ctr_id;", dbOpenSnapshot)
Do While Not rstCtr.EOF
    dtContractEnd = IIf(IsNull(rstCtr!ctr_termindt), rstCtr!ctr_enddt, rstCtr!ctr_termindt)
    dtStartDate = rstCtr!ctr_startdt
    Do While dtStartDate <= dtContractEnd
        dtEndDate = LastDateThisMonth(dtStartDate)
        If dtContractEnd < dtEndDate Then dtEndDate = dtContractEnd
        rstCPlan.AddNew
        rstCPlan!cpn_ctr_id = rstCtr!ctr_id
        rstCPlan!cpn_hed_id = rstCtr!ctr_hed_id
        rstCPlan!cpn_startdt = dtStartDate
        rstCPlan!cpn_enddt = dtEndDate
        rstCPlan.Update
        dtStartDate = FirstDateThisMonth(DateAdd("m", 1, dtStartDate))
        Loop
    rstCtr.MoveNext
    Loop
End Sub

Private Sub RenameFiles()

Dim objFSO As Object
Dim objFile As Object
Dim objFolder As Object
Dim firstLast As String
Dim dbsFile As Database
Dim rstFile As Recordset
Dim strFile As String

Set dbsFile = CurrentDb
Set objFSO = CreateObject("Scripting.FileSystemObject")
Set objFolder = objFSO.GetFolder("\\10.120.29.99\data to be shared\IS\Contracts")
For Each objFile In objFolder.Files
    strFile = Left(objFile.Name, Len(objFile.Name) - 4)
    Set rstFile = dbsFile.OpenRecordset("Select ctr_num, ctr_date From hr_contracts Where ctr_id = " & strFile, dbOpenSnapshot)
    If rstFile.EOF Then
        MsgBox "Contract not found!"
        Else
        objFile.Name = rstFile!ctr_num & " dated " & rstFile!ctr_date & ".pdf"
        End If
    Next
Set objFSO = Nothing
Set objFile = Nothing
Set objFolder = Nothing
End Sub

Sub ListAllControls(FormName As String)
Dim frm As Form
Dim ctl As Control

Set frm = Forms(FormName)
For Each ctl In frm
    If ctl.ControlType = acTextBox Then Debug.Print ctl.Name
    Next ctl
End Sub

Sub sdf()
'DoCmd.Echo True
'DoCmd.Hourglass False
'CloseAllForms
DBEngine(0).CommitTrans
'ShowProperty
End Sub

'CurrentDb.Version          'ACE version
'Application.Version      'Access version
'CurrentProject.FileFormat  'File version

Sub asas()
Debug.Print SearchInQueries("hr_attendance_u_oneday_temp")
End Sub

Sub ddd()
Dim dbs As Database
Dim rst As Recordset
Set dbs = CurrentDb()
Set rst = dbs.OpenRecordset("")
End Sub

Sub ggg()
Dim rst As Recordset
Set rst = CurrentDb.OpenRecordset("Select * From fin_commitments Inner Join pur_purcases On (fin_commitments.cmt_type = pur_purcases.pcs_type) And (fin_commitments.cmt_docid = pur_purcases.pcs_id) Where cmt_effhed_id = 350022", dbOpenSnapshot)
rst.MoveNext
Debug.Print rst.RecordCount
End Sub

Sub fsd()
ListAllControls ("cen_accounts_detail_add")
End Sub


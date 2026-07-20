Attribute VB_Name = "Module2"
Option Compare Database
Option Explicit

'===================================================================
' FULL ACCESS APPLICATION SOURCE EXPORTER (SINGLE MODULE VERSION)
' Exports: Forms (code+design), Reports (code+design), Standard
' Modules, Class Modules, Queries, Macros, Tables, Relationships,
' Indexes, References, Database Properties.
'
' REQUIRES:
'   - File > Options > Trust Center > Trust Center Settings >
'     Macro Settings > "Trust access to the VBA project object model" = ON
'   - VBA project must NOT be password protected
'
' RUN: Type   RunFullExport   in the Immediate Window and press Enter
'===================================================================

Public gBasePath As String
Public gStartTime As Date
Public gErrorCount As Long
Public gLogFilePath As String

'===================================================================
' MAIN ENTRY POINT
'===================================================================
Public Sub RunFullExport()

    On Error GoTo ErrHandler

    gStartTime = Now
    gErrorCount = 0

    Debug.Print String(70, "=")
    Debug.Print "ACCESS APPLICATION SOURCE EXPORT - STARTED"
    Debug.Print "Start Time: " & Format(gStartTime, "yyyy-mm-dd hh:nn:ss")
    Debug.Print String(70, "=")

    If Not CreateExportFolders() Then
        Debug.Print ">>> FATAL: Could not create export folders. Aborting."
        Exit Sub
    End If

    InitErrorLog

    RunStep "Forms (code + design)", "ExportAllForms"
    RunStep "Reports (code + design)", "ExportAllReports"
    RunStep "Standard Modules", "ExportStandardModules"
    RunStep "Class Modules", "ExportClassModules"
    RunStep "Queries", "ExportAllQueries"
    RunStep "Macros", "ExportAllMacros"
    RunStep "Tables (structure + indexes)", "ExportAllTables"
    RunStep "Relationships", "ExportAllRelationships"
    RunStep "References", "ExportAllReferences"
    RunStep "Database Properties", "ExportAllProperties"

    Debug.Print String(70, "=")
    Debug.Print "EXPORT COMPLETE"
    Debug.Print "End Time:   " & Format(Now, "yyyy-mm-dd hh:nn:ss")
    Debug.Print "Duration:   " & Format(Now - gStartTime, "hh:nn:ss")
    Debug.Print "Errors:     " & gErrorCount & " (see " & gLogFilePath & ")"
    Debug.Print "Output at:  " & gBasePath
    Debug.Print String(70, "=")

    MsgBox "Export finished." & vbCrLf & _
           "Errors: " & gErrorCount & vbCrLf & _
           "Output folder:" & vbCrLf & gBasePath, _
           IIf(gErrorCount = 0, vbInformation, vbExclamation), "Export Complete"

    Exit Sub

ErrHandler:
    Debug.Print ">>> FATAL ERROR in RunFullExport: " & Err.Number & " - " & Err.Description
    LogError "RunFullExport", "Main", Err.Number, Err.Description
    MsgBox "Export stopped due to a fatal error." & vbCrLf & Err.Description, vbCritical, "Export Failed"

End Sub

Private Sub RunStep(ByVal StepLabel As String, ByVal ProcToRun As String)

    Dim stepStart As Date
    stepStart = Now

    Debug.Print vbCrLf & "--- Exporting: " & StepLabel & " ---"

    On Error GoTo StepError
    Application.Run ProcToRun
    Debug.Print "    Done (" & Format(Now - stepStart, "hh:nn:ss") & ")"
    Exit Sub

StepError:
    Debug.Print "    >>> ERROR during '" & StepLabel & "': " & Err.Number & " - " & Err.Description
    LogError StepLabel, "Main", Err.Number, Err.Description
    Resume Next

End Sub

'===================================================================
' FOLDER SETUP / LOGGING HELPERS
'===================================================================
Private Function CreateExportFolders() As Boolean

    On Error GoTo ErrHandler

    Dim dbPath As String
    dbPath = CurrentProject.Path

    gBasePath = dbPath & "\Backup_Export_" & Format(Now, "yyyymmdd_hhnnss")

    EnsureFolder gBasePath
    EnsureFolder gBasePath & "\Forms"
    EnsureFolder gBasePath & "\Forms\Code"
    EnsureFolder gBasePath & "\Forms\Design"
    EnsureFolder gBasePath & "\Reports"
    EnsureFolder gBasePath & "\Reports\Code"
    EnsureFolder gBasePath & "\Reports\Design"
    EnsureFolder gBasePath & "\Modules"
    EnsureFolder gBasePath & "\Modules\Standard"
    EnsureFolder gBasePath & "\Modules\Class"
    EnsureFolder gBasePath & "\Queries"
    EnsureFolder gBasePath & "\Macros"
    EnsureFolder gBasePath & "\Tables"
    EnsureFolder gBasePath & "\Tables\Indexes"
    EnsureFolder gBasePath & "\Relationships"
    EnsureFolder gBasePath & "\References"
    EnsureFolder gBasePath & "\Properties"
    EnsureFolder gBasePath & "\Logs"

    Debug.Print "Export folder created: " & gBasePath
    CreateExportFolders = True
    Exit Function

ErrHandler:
    Debug.Print "ERROR creating folders: " & Err.Number & " - " & Err.Description
    CreateExportFolders = False

End Function

Public Sub EnsureFolder(ByVal FolderPath As String)
    On Error Resume Next
    If Dir(FolderPath, vbDirectory) = "" Then MkDir FolderPath
    On Error GoTo 0
End Sub

Private Sub InitErrorLog()
    Dim f As Integer
    gLogFilePath = gBasePath & "\Logs\ErrorLog.txt"
    f = FreeFile
    Open gLogFilePath For Output As #f
    Print #f, "Access Application Export - Error Log"
    Print #f, "Run started: " & Format(gStartTime, "yyyy-mm-dd hh:nn:ss")
    Print #f, String(70, "-")
    Close #f
End Sub

Public Sub LogError(ByVal SourceModule As String, ByVal ObjectOrStep As String, _
                     ByVal ErrNum As Long, ByVal ErrDesc As String)
    Dim f As Integer
    gErrorCount = gErrorCount + 1
    On Error Resume Next
    f = FreeFile
    Open gLogFilePath For Append As #f
    Print #f, Format(Now, "yyyy-mm-dd hh:nn:ss") & " | " & SourceModule & _
               " | " & ObjectOrStep & " | Err " & ErrNum & ": " & ErrDesc
    Close #f
    On Error GoTo 0
End Sub

Public Sub Progress(ByVal CurrentIndex As Long, ByVal TotalCount As Long, _
                     ByVal ObjectName As String, Optional ByVal Status As String = "OK")
    Debug.Print "  [" & CurrentIndex & "/" & TotalCount & "] " & ObjectName & " ... " & Status
End Sub

Private Function CleanFileName(ByVal sName As String) As String
    Dim badChars As Variant, i As Integer
    badChars = Array("\", "/", ":", "*", "?", Chr(34), "<", ">", "|")
    For i = 0 To UBound(badChars)
        sName = Replace(sName, badChars(i), "_")
    Next i
    CleanFileName = sName
End Function

'===================================================================
' FORMS
'===================================================================
Public Sub ExportAllForms()

    Dim doc As Object, total As Long, i As Long, safeName As String

    total = CurrentProject.AllForms.count
    Debug.Print "Total Forms: " & total
    i = 0

    For Each doc In CurrentProject.AllForms
        i = i + 1
        safeName = CleanFileName(doc.Name)

        On Error GoTo FormErr

        ' Design (full text representation, includes layout)
        Application.SaveAsText acForm, doc.Name, gBasePath & "\Forms\Design\" & safeName & ".txt"

        ' Code-behind module (skips silently inside if none exists)
        ExportVBComponentCode "Form_" & doc.Name, gBasePath & "\Forms\Code\" & safeName & ".bas"

        Progress i, total, doc.Name
        GoTo NextForm

FormErr:
        Progress i, total, doc.Name, "FAILED"
        LogError "ExportAllForms", doc.Name, Err.Number, Err.Description
        Resume NextForm

NextForm:
        On Error GoTo 0
    Next doc

End Sub

'===================================================================
' REPORTS
'===================================================================
Public Sub ExportAllReports()

    Dim doc As Object, total As Long, i As Long, safeName As String

    total = CurrentProject.AllReports.count
    Debug.Print "Total Reports: " & total
    i = 0

    For Each doc In CurrentProject.AllReports
        i = i + 1
        safeName = CleanFileName(doc.Name)

        On Error GoTo RptErr

        Application.SaveAsText acReport, doc.Name, gBasePath & "\Reports\Design\" & safeName & ".txt"

        ExportVBComponentCode "Report_" & doc.Name, gBasePath & "\Reports\Code\" & safeName & ".bas"

        Progress i, total, doc.Name
        GoTo NextRpt

RptErr:
        Progress i, total, doc.Name, "FAILED"
        LogError "ExportAllReports", doc.Name, Err.Number, Err.Description
        Resume NextRpt

NextRpt:
        On Error GoTo 0
    Next doc

End Sub

' Helper: exports the code text of a given VBComponent by name (used for form/report code-behind)
' If the component doesn't exist (no code module), this silently does nothing.
Private Sub ExportVBComponentCode(ByVal CompName As String, ByVal OutPath As String)

    Dim vbProj As Object, vbComp As Object

    On Error Resume Next
    Set vbProj = Application.VBE.ActiveVBProject
    Set vbComp = vbProj.VBComponents(CompName)
    On Error GoTo 0

    If Not vbComp Is Nothing Then
        vbComp.Export OutPath
    End If

End Sub

'===================================================================
' STANDARD & CLASS MODULES
'===================================================================
Public Sub ExportStandardModules()
    ExportModulesByType 1, gBasePath & "\Modules\Standard\"   ' vbext_ct_StdModule = 1
End Sub

Public Sub ExportClassModules()
    ExportModulesByType 2, gBasePath & "\Modules\Class\"      ' vbext_ct_ClassModule = 2
End Sub

Private Sub ExportModulesByType(ByVal CompType As Integer, ByVal OutFolder As String)

    Dim vbProj As Object, vbComp As Object
    Dim total As Long, i As Long, safeName As String, ext As String

    Set vbProj = Application.VBE.ActiveVBProject
    total = vbProj.VBComponents.count
    i = 0

    For Each vbComp In vbProj.VBComponents
        If vbComp.Type = CompType Then

            i = i + 1
            safeName = CleanFileName(vbComp.Name)
            ext = IIf(CompType = 1, ".bas", ".cls")

            On Error GoTo ModErr
            vbComp.Export OutFolder & safeName & ext
            Progress i, total, vbComp.Name
            GoTo NextMod

ModErr:
            Progress i, total, vbComp.Name, "FAILED"
            LogError "ExportModulesByType", vbComp.Name, Err.Number, Err.Description
            Resume NextMod

NextMod:
            On Error GoTo 0
        End If
    Next vbComp

End Sub

'===================================================================
' QUERIES
'===================================================================
Public Sub ExportAllQueries()

    Dim qdf As DAO.QueryDef, total As Long, i As Long, safeName As String, f As Integer

    total = CurrentDb.QueryDefs.count
    Debug.Print "Total Queries: " & total
    i = 0

    For Each qdf In CurrentDb.QueryDefs
        If Left(qdf.Name, 1) <> "~" Then   ' skip hidden/system queries
            i = i + 1
            safeName = CleanFileName(qdf.Name)

            On Error GoTo QryErr
            f = FreeFile
            Open gBasePath & "\Queries\" & safeName & ".sql" For Output As #f
            Print #f, "-- Query: " & qdf.Name
            Print #f, "-- Type: " & qdf.Type
            Print #f, ""
            Print #f, qdf.sql
            Close #f

            Progress i, total, qdf.Name
            GoTo NextQry

QryErr:
            Close #f
            Progress i, total, qdf.Name, "FAILED"
            LogError "ExportAllQueries", qdf.Name, Err.Number, Err.Description
            Resume NextQry

NextQry:
            On Error GoTo 0
        End If
    Next qdf

End Sub

'===================================================================
' MACROS
'===================================================================
Public Sub ExportAllMacros()

    Dim doc As Object, total As Long, i As Long, safeName As String

    total = CurrentProject.AllMacros.count
    Debug.Print "Total Macros: " & total
    i = 0

    For Each doc In CurrentProject.AllMacros
        i = i + 1
        safeName = CleanFileName(doc.Name)

        On Error GoTo MacErr
        Application.SaveAsText acMacro, doc.Name, gBasePath & "\Macros\" & safeName & ".txt"
        Progress i, total, doc.Name
        GoTo NextMac

MacErr:
        Progress i, total, doc.Name, "FAILED"
        LogError "ExportAllMacros", doc.Name, Err.Number, Err.Description
        Resume NextMac

NextMac:
        On Error GoTo 0
    Next doc

End Sub

'===================================================================
' TABLES (structure + indexes)
'===================================================================
Public Sub ExportAllTables()

    Dim tdf As DAO.TableDef, fld As DAO.Field, idx As DAO.Index
    Dim total As Long, i As Long, safeName As String, f As Integer

    total = 0
    For Each tdf In CurrentDb.TableDefs
        If Left(tdf.Name, 4) <> "MSys" Then total = total + 1
    Next tdf
    Debug.Print "Total Tables: " & total
    i = 0

    For Each tdf In CurrentDb.TableDefs
        If Left(tdf.Name, 4) <> "MSys" Then
            i = i + 1
            safeName = CleanFileName(tdf.Name)

            On Error GoTo TblErr

            ' --- Structure ---
            f = FreeFile
            Open gBasePath & "\Tables\" & safeName & ".txt" For Output As #f
            Print #f, "Table: " & tdf.Name
            Print #f, "RecordCount: " & tdf.RecordCount
            Print #f, String(60, "-")
            Print #f, "FieldName" & vbTab & "Type" & vbTab & "Size" & vbTab & "Required" & vbTab & "AllowZeroLength" & vbTab & "DefaultValue"
            For Each fld In tdf.Fields
                Print #f, fld.Name & vbTab & FieldTypeName(fld.Type) & vbTab & fld.Size & _
                          vbTab & fld.Required & vbTab & fld.AllowZeroLength & vbTab & fld.DefaultValue
            Next fld
            Close #f

            ' --- Indexes ---
            f = FreeFile
            Open gBasePath & "\Tables\Indexes\" & safeName & "_Indexes.txt" For Output As #f
            Print #f, "Indexes for Table: " & tdf.Name
            Print #f, String(60, "-")
            For Each idx In tdf.Indexes
                Dim idxFields As String, idxFld As DAO.Field
                idxFields = ""
                For Each idxFld In idx.Fields
                    idxFields = idxFields & idxFld.Name & "; "
                Next idxFld
                Print #f, "IndexName: " & idx.Name & vbTab & "Primary: " & idx.Primary & _
                          vbTab & "Unique: " & idx.Unique & vbTab & "Fields: " & idxFields
            Next idx
            Close #f

            Progress i, total, tdf.Name
            GoTo NextTbl

TblErr:
            Close #f
            Progress i, total, tdf.Name, "FAILED"
            LogError "ExportAllTables", tdf.Name, Err.Number, Err.Description
            Resume NextTbl

NextTbl:
            On Error GoTo 0
        End If
    Next tdf

End Sub

Private Function FieldTypeName(ByVal dt As Integer) As String
    Select Case dt
        Case dbBoolean: FieldTypeName = "Boolean"
        Case dbByte: FieldTypeName = "Byte"
        Case dbInteger: FieldTypeName = "Integer"
        Case dbLong: FieldTypeName = "Long"
        Case dbCurrency: FieldTypeName = "Currency"
        Case dbSingle: FieldTypeName = "Single"
        Case dbDouble: FieldTypeName = "Double"
        Case dbDate: FieldTypeName = "Date/Time"
        Case dbText: FieldTypeName = "Text"
        Case dbLongBinary: FieldTypeName = "OLE Object"
        Case dbMemo: FieldTypeName = "Memo"
        Case dbGUID: FieldTypeName = "GUID"
        Case dbBigInt: FieldTypeName = "BigInt"
        Case dbDecimal: FieldTypeName = "Decimal"
        Case Else: FieldTypeName = "Type_" & dt
    End Select
End Function

'===================================================================
' RELATIONSHIPS
'===================================================================
Public Sub ExportAllRelationships()

    Dim rel As DAO.Relation, fld As DAO.Field, total As Long, i As Long, f As Integer

    total = CurrentDb.Relations.count
    Debug.Print "Total Relationships: " & total

    f = FreeFile
    Open gBasePath & "\Relationships\Relationships.txt" For Output As #f
    Print #f, "Database Relationships"
    Print #f, String(70, "-")

    i = 0
    For Each rel In CurrentDb.Relations
        i = i + 1
        On Error GoTo RelErr

        Print #f, "Relationship: " & rel.Name
        Print #f, "  Table:       " & rel.Table
        Print #f, "  ForeignTable:" & rel.ForeignTable
        Print #f, "  Attributes:  " & rel.Attributes
        For Each fld In rel.Fields
            Print #f, "  Field: " & fld.Name & " -> ForeignName: " & fld.ForeignName
        Next fld
        Print #f, String(40, "-")

        Progress i, total, rel.Name
        GoTo NextRel

RelErr:
        Progress i, total, rel.Name, "FAILED"
        LogError "ExportAllRelationships", rel.Name, Err.Number, Err.Description
        Resume NextRel

NextRel:
        On Error GoTo 0
    Next rel

    Close #f

End Sub

'===================================================================
' REFERENCES
'===================================================================
Public Sub ExportAllReferences()

    Dim ref As Object, total As Long, i As Long, f As Integer

    total = Application.References.count
    Debug.Print "Total References: " & total

    f = FreeFile
    Open gBasePath & "\References\References.txt" For Output As #f
    Print #f, "VBA Project References"
    Print #f, String(70, "-")

    i = 0
    For Each ref In Application.References
        i = i + 1
        On Error GoTo RefErr

        Print #f, "Name:    " & ref.Name
        Print #f, "Path:    " & ref.FullPath
        Print #f, "GUID:    " & ref.Guid
        Print #f, "Version: " & ref.Major & "." & ref.Minor
        Print #f, "Broken:  " & ref.IsBroken
        Print #f, String(40, "-")

        Progress i, total, ref.Name
        GoTo NextRef

RefErr:
        Progress i, total, ref.Name, "FAILED (possibly broken reference)"
        LogError "ExportAllReferences", ref.Name, Err.Number, Err.Description
        Resume NextRef

NextRef:
        On Error GoTo 0
    Next ref

    Close #f

End Sub

'===================================================================
' DATABASE PROPERTIES
'===================================================================
Public Sub ExportAllProperties()

    Dim prp As DAO.Property, f As Integer

    f = FreeFile
    Open gBasePath & "\Properties\DatabaseProperties.txt" For Output As #f

    Print #f, "CurrentDb Properties"
    Print #f, String(70, "-")
    For Each prp In CurrentDb.Properties
        On Error Resume Next
        Print #f, prp.Name & " = " & prp.Value
        On Error GoTo 0
    Next prp

    Print #f, ""
    Print #f, "CurrentProject Properties"
    Print #f, String(70, "-")
    Dim cprp As Object
    For Each cprp In CurrentProject.Properties
        On Error Resume Next
        Print #f, cprp.Name & " = " & cprp.Value
        On Error GoTo 0
    Next cprp

    Close #f

    Debug.Print "Database properties exported."

End Sub


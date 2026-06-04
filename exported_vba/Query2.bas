Attribute VB_Name = "Query2"
Option Compare Database
Option Explicit

Dim varArray() As Variant, i As Long

Public Function QrySeq(ByVal fldvalue, ByVal fldName As String, ByVal QryName As String) As Long
'-------------------------------------------------------------------
'Purpose: Create Sequence Numbers in Query in a new Column
'Author : a.p.r. pillai
'Date : Dec. 2009
'All Rights Reserved by www.msaccesstips.com
'-------------------------------------------------------------------
'Parameter values
'-------------------------------------------------------------------
'1 : Column Value - must be unique Values from the Query
'2 : Column Name  - the Field Name from Unique Value Taken
'3 : Query Name   - Name of the Query this Function is Called from
'-------------------------------------------------------------------
'Limitations - Function must be called with a Unique Field Value
'            - as First Parameter
'            - Need to Save the Query after change before opening
'            - in normal View.
'-------------------------------------------------------------------
Dim k As Long
On Error GoTo QrySeq_Err

restart:
If i = 0 Or DCount("*", QryName) <> i Then
Dim j As Long, db As Database, rst As Recordset

i = DCount("*", QryName)
ReDim varArray(1 To i, 1 To 3) As Variant
Set db = CurrentDb
Set rst = db.OpenRecordset(QryName, dbOpenDynaset)
For j = 1 To i
    varArray(j, 1) = rst.Fields(fldName).Value
    varArray(j, 2) = j
    varArray(j, 3) = fldName
    rst.MoveNext
Next
rst.Close
End If

If varArray(1, 3) & varArray(1, 1) <> (fldName & DLookup(fldName, QryName)) Then
    i = 0
    GoTo restart
End If

For k = 1 To i
If varArray(k, 1) = fldvalue Then
    QrySeq = varArray(k, 2)
    Exit Function
End If
Next

QrySeq_Exit:
Exit Function

QrySeq_Err:
MsgBox Err & " : " & Err.Description, , "QrySeqQ"
Resume QrySeq_Exit

End Function



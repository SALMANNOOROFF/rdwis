Attribute VB_Name = "General"
Option Compare Database
Option Explicit


Sub PushInArray(InputArray() As Variant, Item As Variant)
Dim n As Integer
For n = LBound(InputArray) To UBound(InputArray)
    If IsEmpty(InputArray(n)) Then
        InputArray(n) = Item
        Exit Sub
        End If
    Next n
End Sub

Sub SortArray(InputArray() As Variant)
Dim Temp As Variant
Dim i As Integer
Dim j As Integer

For i = LBound(InputArray) To UBound(InputArray)
    For j = i + 1 To UBound(InputArray)
        If InputArray(i) > InputArray(j) Then
            Temp = InputArray(j)
            InputArray(j) = InputArray(i)
            InputArray(i) = Temp
            End If
        Next j
    Next i

End Sub

Sub AddRowAt(InputArray() As Variant, Position As Integer)
Dim R As Integer
Dim c As Integer
For R = UBound(InputArray, 1) - 1 To Position Step -1
    For c = LBound(InputArray, 2) To UBound(InputArray, 2)
        InputArray(R + 1, c) = InputArray(R, c)
        InputArray(R, c) = Empty
        Next c
    Next R
End Sub

Sub DeleteRow(InputArray() As Variant, Position As Integer)
Dim R As Integer
Dim c As Integer
For R = Position To UBound(InputArray, 1) - 1
    For c = LBound(InputArray, 2) To UBound(InputArray, 2)
        InputArray(R, c) = InputArray(R + 1, c)
        Next c
    Next R
End Sub

Sub Doit()
Dim arr(5)
Dim x As Variant
PushInArray arr, "abc"
PushInArray arr, "cde"
For Each x In arr
    Debug.Print x
    Next x
End Sub

Sub PrintDict(InputDict As Variant)
Dim x As Variant
For Each x In InputDict
    Debug.Print x, InputDict(x)
    Next
End Sub

Sub DictToTable(Dict As Variant, TableName As String)
Dim x As Variant
Dim sql As String

sql = "Insert into " & TableName & " Values ("
For Each x In Dict
    sql = sql & "'" & Dict(x) & "', "
    Next x
sql = Left(sql, Len(sql) - 2) & ")"
'Debug.Print sql
CurrentDb().Execute sql
End Sub

Function PrintArray(InputArray As Variant)
Dim x As Integer
Dim y As Integer
Dim strRow As String
For x = LBound(InputArray, 1) To UBound(InputArray, 1)
    strRow = ""
    For y = LBound(InputArray, 2) To UBound(InputArray, 2)
        strRow = strRow & InputArray(x, y) & ", "
        Next y
    strRow = Left(strRow, Len(strRow) - 2)
    Debug.Print strRow
    Next x
End Function

Sub ArrayToTable(Matrix() As Variant, TableName As String, Optional DeletePrevious As Boolean)
Dim R As Integer, c As Integer
Dim sql As String

If DeletePrevious = True Then CurrentDb().Execute "Delete From " & TableName
For R = LBound(Matrix, 1) To UBound(Matrix, 1)
    sql = "Insert into " & TableName & " Values ('" & R & "', "
    For c = LBound(Matrix, 2) To UBound(Matrix, 2)
          sql = sql & "'" & Matrix(R, c) & "', "
          Next c
    sql = Left(sql, Len(sql) - 2) & ")"
    'Debug.Print sql
    CurrentDb().Execute sql
    Next R
End Sub

Public Function FormatWithAmpersand(strInput)
Dim intPos As Integer
Dim strOutput As String

intPos = InStrRev(strInput, ",")
If intPos = 0 Then
    strOutput = strInput
    GoTo LastStep
    End If
strOutput = Left(strInput, intPos - 1) & Replace(strInput, ",", " & ", intPos)
strOutput = Replace(strOutput, ",", ", ")
LastStep:
FormatWithAmpersand = strOutput
End Function

Public Function getField(FieldName As String) As String
Dim frmForm As Form
Set frmForm = Screen.ActiveForm
getField = Nz(frmForm.Controls(FieldName), "")
End Function

Public Function getFieldSub(FieldName As String, SubFormName As String) As String
Dim frmForm As Form
Set frmForm = Screen.ActiveForm.Controls(SubFormName).Form
If frmForm.CurrentRecord = 0 Then
    getFieldSub = 0
    Else
    getFieldSub = Nz(frmForm.Controls(FieldName), "")
    End If
End Function

Function ExtractFirstNumber(strInput As String) As String
Dim strNum As String
Dim n As Integer
Dim booStr As Boolean
booStr = True
For n = 1 To Len(strInput)
    If IsNumeric(Mid(strInput, n, 1)) Then
        booStr = False
        strNum = strNum + Mid(strInput, n, 1)
        Else
        If booStr = False Then GoTo The_End:
        End If
    Next n

The_End:
ExtractFirstNumber = strNum
    
End Function

Function isInteger(Inpt As Variant) As Boolean
On Error GoTo Error_Code
If Int(CDec(Inpt)) = CDec(Inpt) Then
    isInteger = True
    End If
    Exit Function
Error_Code:
isInteger = False
End Function

Function isDecimal(Inpt As Variant) As Boolean
On Error GoTo Error_Code
If Int(CDec(Inpt)) <> CDec(Inpt) Then
    isDecimal = True
    End If
    Exit Function
Error_Code:
isDecimal = False
End Function

Function FormatAsPerType(Var As Variant) As Variant
Select Case True
    Case IsNull(Var): FormatAsPerType = "(Blank)"
    Case Var = 0: FormatAsPerType = 0 '0 is recognised as integer, but formatting "#,###" does not apply
    Case isInteger(Var): FormatAsPerType = Format(Var, "#,###")
    Case isDecimal(Var): FormatAsPerType = Format(Var, "#,###.00")
    Case IsDate(Var): FormatAsPerType = Format(Var, "dd mmm yy")
    Case Else: FormatAsPerType = Var
    End Select
End Function

Public Function ControlExists(objForm As Object, strControlName As String) As Boolean
    On Error Resume Next
    ControlExists = (Len(objForm.Controls(strControlName).Name) <> 0)
End Function

Public Function StringPart(InputString As String, Seperator As String, Part As Integer) As String
Dim arrInput() As String
arrInput = Split(InputString, Seperator)
StringPart = arrInput(Part - 1)
End Function

Public Function PartOfString(CompositeString As String, Part As Integer, Delimiter As String) As String
On Error Resume Next
Dim arrInput() As String
arrInput = Split(CompositeString, Delimiter)
PartOfString = arrInput(Part - 1)
End Function

Public Function LastPartOfString(CompositeString As String, Delimiter As String) As String
On Error Resume Next
Dim arrInput() As String
arrInput = Split(CompositeString, Delimiter)
LastPartOfString = arrInput(UBound(arrInput))
End Function

Public Function ParenContents(InputString As String) As String
Dim i As Integer
Dim j As Integer
On Error Resume Next
i = InStr(InputString, "(")
j = InStr(InputString, ")")
ParenContents = Mid(InputString, i + 1, j - i - 1)
End Function

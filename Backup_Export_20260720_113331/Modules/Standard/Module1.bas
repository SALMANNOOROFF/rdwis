Attribute VB_Name = "Module1"
Option Compare Database
Option Explicit

Public Sub ExportAllFormModules()

    Dim vbComp As Object
    Dim exportPath As String

    exportPath = CurrentProject.Path & "\Exported_Form_Code\"

    If Dir(exportPath, vbDirectory) = "" Then
        MkDir exportPath
    End If

    For Each vbComp In Application.VBE.ActiveVBProject.VBComponents

        If vbComp.Type = 100 Then   'Form Module

            vbComp.Export exportPath & vbComp.Name & ".bas"

        End If

    Next vbComp

    MsgBox "Done!" & vbCrLf & _
           "Location: " & exportPath

End Sub


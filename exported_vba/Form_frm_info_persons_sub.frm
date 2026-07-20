VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_info_persons_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

'Validating data in inf_value field
Private Sub inf_value_BeforeUpdate(Cancel As Integer)
Dim strValue As String

strValue = Me!inf_value
Select Case Me!inf_type
    Case "Mobile", "Landline", "Fax"
        If Not (strValue Like "+*") Then
            MsgBox "Please enter number in the format: +921234512345", vbCritical
            Cancel = True
            End If
        If Not IsNumeric((Right(strValue, Len(strValue) - 1))) Then
            MsgBox "Only numbers can be entered. Please enter 12 digit number in the format: +921234512345", vbCritical
            Cancel = True
            End If
        If strValue Like "+92*" And Len(strValue) <> 13 Then
            MsgBox "Length of number is incorrect. Please enter 12 digit number in the format: +921234512345", vbCritical
            Cancel = True
            End If
    Case "Email"
        If Not (strValue Like "*@*.??*") Then
            MsgBox "Please enter valid email like Shams@dynamic.pk", vbCritical
            Cancel = True
            End If
    Case "WebSite"
        If Not (strValue Like "???.*.??*") Then
            MsgBox "Please enter valid website address like www.yahoo.com", vbCritical
            Cancel = True
            End If
    Case Null
        MsgBox "Please enter a value in 'Type' field.", vbCritical
        Cancel = True
    End Select
    
End Sub


VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_aud_rev_expressioner"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdToString_Click()
Dim str As String
Dim n As Integer
On Error GoTo Error_Part

For n = 0 To 20
    str = str & IIf(n = 0, "", ",") & Nz(Me.Controls("txt" & n), "")
    Next
Me.txtString = str
Me.txtNum = 20
Exit Sub
Error_Part:
MsgBox Err.Description, vbCritical
End Sub

Private Sub cmdToParts_Click()
Dim arr
Dim n As Integer
On Error GoTo Error_Part

If IsNull(Me.txtString) Then Exit Sub
arr = Split(Me.txtString, ",")
For n = 0 To 20
    Me.Controls("txt" & n) = arr(n)
    Next
Me.txtNum = UBound(arr)

Exit Sub
Error_Part:
Me.txtNum = n - 1
If Err.Number = 9 Then Exit Sub
MsgBox Err.Description
End Sub
Private Sub txt0_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt1_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt2_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt3_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt4_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt5_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt6_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt7_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt8_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt9_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt10_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt11_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt12_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt13_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt14_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt15_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt16_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt17_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt18_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt19_AfterUpdate()
Me.txtString = Null
End Sub
Private Sub txt20_AfterUpdate()
Me.txtString = Null
End Sub

Private Sub txtString_AfterUpdate()
Me.txt0 = Null
Me.txt1 = Null
Me.txt2 = Null
Me.txt3 = Null
Me.txt4 = Null
Me.txt5 = Null
Me.txt6 = Null
Me.txt7 = Null
Me.txt8 = Null
Me.txt9 = Null
Me.txt10 = Null
Me.txt11 = Null
Me.txt12 = Null
Me.txt13 = Null
Me.txt14 = Null
Me.txt15 = Null
Me.txt16 = Null
Me.txt17 = Null
Me.txt18 = Null
Me.txt19 = Null
Me.txt20 = Null
ToParts
End Sub

Private Sub ToParts()
Dim arr
Dim n As Integer
On Error GoTo Error_Part

If IsNull(Me.txtString) Then Exit Sub
arr = Split(Me.txtString, ",")
For n = 0 To 20
    Me.Controls("txt" & n) = arr(n)
    Next
Me.txtNum = UBound(arr)

Exit Sub
Error_Part:
Me.txtNum = n - 1
If Err.Number = 9 Then Exit Sub
MsgBox Err.Description
End Sub

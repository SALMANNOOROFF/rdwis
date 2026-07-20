VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_attendance_rev"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim n As Integer

Me.object_id = Forms!hr_attendance_u!att_id
Me.unit_id = Forms!hr_attendance_u!att_unt_id

Me.att_emp_id = Forms!hr_attendance_u!att_emp_id
Me.att_empnamecomp = Forms!hr_attendance_u!att_empnamecomp
Me.att_unt_id = Forms!hr_attendance_u!att_unt_id
Me.att_startdt = Forms!hr_attendance_u!att_startdt

For n = 1 To 31
    Me.Controls("att_" & n) = Forms!hr_attendance_u.Controls("att_" & n)
    Me.Controls("att_" & n & "_old") = Forms!hr_attendance_u.Controls("att_" & n)
    If Me.Controls("att_" & n) = "Z" Then
        Me.Controls("att_" & n).Locked = True
        End If
    If Me.Controls("att_" & n) = "X" Then
        Me.Controls("att_" & n).Visible = False
        Me.Controls("lbl_att_" & n).Visible = False
        End If
    If GetNow() <= DateSerial(Year(Me.att_startdt), Month(Me.att_startdt), n) Then
        Me.Controls("att_" & n).Visible = False
        Me.Controls("lbl_att_" & n).Visible = False
        Else
        'If Nz(Me.Controls("att_" & n), "") = "" Then Me.Controls("att_" & n).Visible = False
        End If
    Next n
    
Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intResponse As Integer
Dim lngRevId As Long

Me.Dirty = False
If AnyChangeOnForm = False Then
    MsgBox "No change in data requested.", vbCritical
    Exit Sub
    End If
    
intResponse = MsgBox("A Data Revision Request for attendance of " & Me.att_empnamecomp & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intResponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Attendance", Me.object_id, Me.unit_id, 2)
DoCmd.Close acForm, "hr_attendance_rev"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_attendance_u"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub att_1_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_2_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_3_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_4_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_5_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_6_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_7_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_8_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_9_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_10_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_11_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_12_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_13_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_14_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_15_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_16_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_17_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_18_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_19_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_20_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_21_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_22_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_23_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_24_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_25_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_26_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_27_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_28_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_29_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_30_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub

Private Sub att_31_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub






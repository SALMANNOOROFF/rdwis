VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_attendance_rev2"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Open(Cancel As Integer)
Dim n As Integer

n = Day(Me.object_id)
Me.att.ControlSource = "att_" & n
Me.att_old.ControlSource = "att_" & n & "_old"
Me.att_detail.ControlSource = "att_" & n & "_detail"
Me.Refresh

Me.Visible = True
End Sub

Private Sub cmdGenerate_Click()
Dim intresponse As Integer
Dim lngRevId As Long

Me.Dirty = False
If AnyChangeOnForm = False Then
    MsgBox "No change in data requested.", vbCritical
    Exit Sub
    End If

intresponse = MsgBox("A Data Revision Request for attendance on " & Me.object_id & " will be generated. Do you want to continue?", vbExclamation + vbYesNo, "Confirmation")
If intresponse <> 6 Then Exit Sub

lngRevId = CreateDataRevision("Attendance", Me.object_id, Me.unit_id, 2)
DoCmd.Close acForm, "hr_attendance_rev2"
DoCmd.OpenForm "aud_revs_detail", acNormal, , , , acHidden, "Draft~" & lngRevId
DoCmd.Close acForm, "hr_attendance_u_oneday"
DoCmd.Close acForm, "hr_attendance_u"
End Sub

Private Sub cmdCancel_Click()
DoCmd.Close
End Sub

Private Sub att_KeyPress(KeyAscii As Integer)
KeyAscii = KeyPressWork(KeyAscii)
End Sub


VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_pur_purreceipts_detail_sub"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Load()
'On Error GoTo TheEnd
'Const MaxRecs As Integer = 10
'Dim NumRecs As Integer
'
''With Me.subformcontrol.Form 'code in main form
'Me.RecordsetClone.MoveLast
'NumRecs = Me.RecordsetClone.RecordCount
'If NumRecs > MaxRecs Then NumRecs = MaxRecs
'Me.InsideHeight = Me.Section(1).Height + Me.Section(2).Height _
'                  + NumRecs * Me.Section(0).Height

'Me.InsideHeight = 2300
'Debug.Print Me.InsideHeight
TheEnd:
End Sub

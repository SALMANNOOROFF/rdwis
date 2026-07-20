VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Report_prj_mpr-ii"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database

Private Sub Report_NoData(Cancel As Integer)

MsgBox "There is no case for MPR part 2"
Cancel = 1

End Sub

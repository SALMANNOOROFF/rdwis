VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_frm_firmsbrf"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database

Private Sub cmdDetails_Click()
On Error GoTo cmdDetails_Click_Err

DoCmd.OpenForm "firms", acNormal, "", "", , acHidden
Forms!Firms.RecordSource = "Select * From firms Where frm_id = " & Me!frm_id
Forms!Firms.NavigationButtons = False
Forms!Firms.lblTitle.Caption = "Selected Firm"
Forms!Firms.Visible = True
 
cmdDetails_Click_Exit:
    Exit Sub

cmdDetails_Click_Err:
    MsgBox Error$
    Resume cmdDetails_Click_Exit

End Sub


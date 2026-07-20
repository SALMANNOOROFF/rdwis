VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_fin_globalvars_sal"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub gvar_value_AfterUpdate()
If Me!gvar_type = "date" Then
    Me!gvar_value = Format(Me!gvar_value, "dd mmm yy")
    End If
End Sub

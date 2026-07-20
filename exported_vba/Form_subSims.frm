VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_subSims"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub sim_reg_AfterUpdate()
If LCase(Me!sim_reg) = "yes" Then Me!sim_reg = "Yes"
If LCase(Me!sim_reg) = "no" Then Me!sim_reg = "No"
End Sub

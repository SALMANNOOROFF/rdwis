VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_cen_unit_u"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub Form_Close()
Me.Dirty = False
Forms!vars!varUnitLeadName = Me!unt_leadname
Forms!vars!varUnitLeadTitle = Me!unt_leadtitle
Forms!vars!varUnitLeadRank = Me!unt_leadrank
Forms!vars!varUnitLeadDesig = Me!unt_leaddesig
Forms!vars!varUnitLeadDesigShort = Me!unt_leaddesigshort
End Sub

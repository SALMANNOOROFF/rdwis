Attribute VB_Name = "AccountChecks"
Option Compare Database
Option Explicit

Public Function AnyObjectionsOnExp(FromHead As Long, ForHead As Variant, sudohead As Variant, amount1 As Double, amount2 As Double) As String
'Dim dcnCheck As Scripting.Dictionary
'Dim dblExp As Double
'Dim dblInPrcSo As Double
'Dim dblInPrcPc As Double
'Dim dblOver As Double
'Dim dblLimit As Double
'Dim strMessage As String
'
'Check_For_Head:
'If Nz(SudoHead, "") <> "" Or Nz(ForHead, "") = "" Then GoTo Check_From_Head
'Set dcnCheck = GetHeadStatus(CLng(ForHead), "gjr")
'Select Case dcnCheck("TransType")
'    Case 1: dblExp = amount1
'    Case 2: dblExp = amount2
'    End Select
'dblInPrcSo = dcnCheck("PccInProcessSoFor")
'dblInPrcPc = dcnCheck("PccInProcessPcFor")
'dblLimit = dcnCheck("PrjCanBeSpent")
'dblOver = dblExp + dblInPrcSo + dblInPrcPc - dblLimit
'If dblOver > 0 Then strMessage = dblOver & " above max spent limit for the project"
'Set dcnCheck = Nothing
'
'Check_From_Head:
'Set dcnCheck = GetHeadStatus(FromHead, "gpcm")
'Select Case dcnCheck("TransType")
'    Case 1: dblExp = amount1
'    Case 2: dblExp = amount2
'    End Select
'Select Case True
'    Case Nz(SudoHead, "") = ""
'    dblInPrcSo = dcnCheck("PccInProcessSoFrom")
'    dblInPrcPc = dcnCheck("PccInProcessPcFrom")
'    dblLimit = dcnCheck("PccCanBeSpent")
'    Case Nz(SudoHead, "") <> ""
'    dblInPrcSo = dcnCheck("CfInProcessSoFrom")
'    dblInPrcPc = dcnCheck("CfInProcessPcFrom")
'    dblLimit = dcnCheck("CfCanBeSpent")
'    End Select
'dblOver = dblExp + dblInPrcSo + dblInPrcPc - dblLimit
'If dblOver > 0 Then strMessage = strMessage & vbCrLf & dblOver & " above max spent limit from the project"
'Set dcnCheck = Nothing
'
'
'AnyObjectionsOnExp = strMessage
'
End Function


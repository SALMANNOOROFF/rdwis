Attribute VB_Name = "InputBoxMasked"
Option Compare Database
Option Explicit
'----------------------------------
'API CONSTANTS FOR PRIVATE INPUTBOX
'----------------------------------

    Private Declare PtrSafe Function CallNextHookEx Lib "user32" (ByVal hHook As LongPtr, _
        ByVal ncode As Long, ByVal wParam As LongPtr, lParam As Any) As LongPtr
    Private Declare PtrSafe Function GetModuleHandle Lib "kernel32" Alias _
        "GetModuleHandleA" (ByVal lpModuleName As String) As LongPtr
    Private Declare PtrSafe Function SetWindowsHookEx Lib "user32" Alias "SetWindowsHookExA" _
        (ByVal idHook As Long, ByVal lpfn As LongPtr, ByVal hmod As LongPtr, ByVal dwThreadId As Long) As LongPtr
    Private Declare PtrSafe Function UnhookWindowsHookEx Lib "user32" (ByVal hHook As LongPtr) As Long
    Private Declare PtrSafe Function SendDlgItemMessage Lib "user32" Alias "SendDlgItemMessageA" _
        (ByVal hDlg As LongPtr, ByVal nIDDlgItem As Long, ByVal wMsg As Long, ByVal wParam As LongPtr, ByVal lParam As LongPtr) As LongPtr
    Private Declare PtrSafe Function GetClassName Lib "user32" Alias "GetClassNameA" _
        (ByVal hwnd As LongPtr, ByVal lpClassName As String, ByVal nMaxCount As Long) As Long
    Private Declare PtrSafe Function GetCurrentThreadId Lib "kernel32" () As Long

'Constants to be used in our API functions
Private Const EM_SETPASSWORDCHAR = &HCC
Private Const WH_CBT = 5
Private Const HCBT_ACTIVATE = 5
Private Const HC_ACTION = 0


Private hHook As LongPtr

'----------------------------------
'PRIVATE PASSWORDS FOR INPUTBOX
'----------------------------------

'////////////////////////////////////////////////////////////////////
'Password masked inputbox
'Allows you to hide characters entered in a VBA Inputbox.
'
'Code written by Daniel Klann
'March 2003
'64-bit modifications developed by Alexey Tseluiko
'and Ryan Wells (wellsr.com)
'February 2019
'////////////////////////////////////////////////////////////////////

Public Function NewProc(ByVal lngCode As Long, ByVal wParam As Long, ByVal lParam As Long) As LongPtr

    Dim RetVal
    Dim strClassName As String, lngBuffer As Long
    If lngCode < HC_ACTION Then
        NewProc = CallNextHookEx(hHook, lngCode, wParam, lParam)
        Exit Function
    End If

    strClassName = String$(256, " ")
    lngBuffer = 255
    If lngCode = HCBT_ACTIVATE Then 'A window has been activated
        RetVal = GetClassName(wParam, strClassName, lngBuffer)
        If Left$(strClassName, RetVal) = "#32770" Then
            'This changes the edit control so that it display the password character *.
            'You can change the Asc("*") as you please.
            SendDlgItemMessage wParam, &H1324, EM_SETPASSWORDCHAR, Asc("*"), &H0
        End If
    End If
    'This line will ensure that any other hooks that may be in place are
    'called correctly.
    CallNextHookEx hHook, lngCode, wParam, lParam
End Function

Function InputBoxDK(Prompt, title) As String

    Dim lngModHwnd As LongPtr

    Dim lngThreadID As Long
    lngThreadID = GetCurrentThreadId
    lngModHwnd = GetModuleHandle(vbNullString)
    hHook = SetWindowsHookEx(WH_CBT, AddressOf NewProc, lngModHwnd, lngThreadID)
    InputBoxDK = InputBox(Prompt, title)
    UnhookWindowsHookEx hHook
End Function

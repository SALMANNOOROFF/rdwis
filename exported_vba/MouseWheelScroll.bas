Attribute VB_Name = "MouseWheelScroll"
Option Compare Database
Option Explicit

Private Const sModName = "mod_ScrollingTextBox"

'Scrolling Constants
Public Const WM_VSCROLL = &H115
Public Const WM_HSCROLL = &H114
Public Const SB_LINEUP = 0
Public Const SB_LINEDOWN = 1
Public Const SB_PAGEUP = 2
Public Const SB_PAGEDOWN = 3

Public Declare Function apiGetFocus Lib "user32" Alias "GetFocus" _
                                     () As Long

Public Declare Function SendMessage Lib "user32" Alias "SendMessageA" _
                                    (ByVal hwnd As Long, ByVal wMsg As Long, _
                                     ByVal wParam As Integer, _
                                     ByVal lParam As Any) As Long



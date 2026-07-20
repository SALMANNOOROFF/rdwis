VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "Form_hr_applicants_stage"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Option Compare Database
Option Explicit

Private Sub cmdImport_Click()
Dim dbsImp As Database
Dim qryImp As QueryDef
Dim rstImp As Recordset
Dim strSql As String
Dim strStep As String
Dim strStatus As String
Dim intApplicId As Integer

On Error GoTo Error_Handler
Set dbsImp = CurrentDb()

'Verification of education, courses and career data. Personal data is verified by constraints present in google form.
strStep = "Verification"
If Not IsNull(Me![Qualification Level]) Then
    If IsNull([Degree / Diploma Name]) Or IsNull([Institution Name]) Or IsNull([Duration (years)]) Or IsNull([Completion Date]) Then
        MsgBox "First education record has missing data"
        Exit Sub
        End If
    End If
If Not IsNull(Me![Qualification Level1]) Then
    If IsNull([Degree / Diploma Name1]) Or IsNull([Institution Name1]) Or IsNull([Duration (years)1]) Or IsNull([Completion Date1]) Then   'Or IsNull([Grade / GPA / Division1]) Or IsNull([Majors1])
        MsgBox "Second education record has missing data"
        Exit Sub
        End If
    End If
If Not IsNull(Me![Course / Certification Name]) Then
    If IsNull([Institution]) Or IsNull([Duration]) Or IsNull([Duration unit]) Or _
            IsNull([Date of Completion]) Then
        MsgBox "First course record has missing data"
        Exit Sub
        End If
    End If
If Not IsNull(Me![Course / Certification Name1]) Then
    If IsNull([Institution1]) Or IsNull([Duration1]) Or IsNull([Duration unit1]) Or _
            IsNull([Date of Completion1]) Then
        MsgBox "Second course record has missing data"
        Exit Sub
        End If
    End If
If Not IsNull(Me![Course / Certification Name2]) Then
    If IsNull([Institution2]) Or IsNull([Duration2]) Or IsNull([Duration unit2]) Or _
            IsNull([Date of Completion2]) Then      'Or IsNull([Grade2]) Or IsNull([Specialization / Majors2])
        MsgBox "Third course record has missing data"
        Exit Sub
        End If
    End If
If Not IsNull(Me![Employer Name]) Then
    If IsNull([Employer City]) Or IsNull([Job Title]) Or IsNull([Reported to]) Or IsNull([Start Date]) Or _
            IsNull([List Down Major Responsibilities]) Or IsNull([Number of Resources Managed]) Then
        MsgBox "Career record has missing data"
        Exit Sub
        End If
    End If

'Import Personal data ---------------------------------------------------
strStep = "Personal data"
strSql = "INSERT INTO hr_applicants ( " & _
            "apl_dtg, apl_email, apl_name, apl_rank, apl_father, apl_cnic, apl_gender, apl_ntnlty, apl_qualif, apl_discip, apl_spec, apl_dob, apl_pob, apl_marital, " & _
            "apl_mobile, apl_mobile2, apl_landline, apl_taddress, apl_paddress, apl_status, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt ) " & _
         "VALUES ( " & _
            CDbl(Me!Timestamp) & ", '" & Me![Email address] & "', '" & Me![Applicant Name] & "', '" & Me!Rank & "', '" & Me![Father Name] & "', '" & Me![CNIC Number] & "', '" & Me!Gender & "', '" & _
            Me!Nationality & "', " & GetQualifId(Me!Qualification) & ", '" & Me!Discipline & "', '" & Me!Specialty & "', #" & Me![Date of Birth] & "#, '" & Me![Place of Birth] & "', '" & _
            Me![Marital Status] & "', '" & Me![Mobile Number] & "', '" & Me![Mobile Number 2] & "', '" & Me![Landline Number] & "', '" & Me![Temporary Address] & "', '" & Me![Permanent Address] & "', 'Active', '" & _
            Me![Job Title applied for] & "', " & Me![Current Salary] & ", " & Me![Expected Salary] & ", " & Me![Work experience (years)] & ", " & IIf(IsNull(Me![Expected Date of Joining]), "''", "#" & Me![Expected Date of Joining] & "#") & " );"
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = "Personal data imported - 01 record"

'Get id of newly created applicant ----------------------------------------
Set rstImp = dbsImp.OpenRecordset("Select apl_id from hr_applicants Where apl_cnic = '" & Me![CNIC Number] & "' And apl_dtg = #" & Me!Timestamp & "#;", dbOpenSnapshot)
intApplicId = rstImp!apl_id
rstImp.Close

'Import education data -----------------------------------------------------
Education_Data_0:
If IsNullish(Me![Qualification Level]) Then GoTo Courses_Data_0
strStep = "Education data 0"
strSql = "INSERT INTO hr_applicqualifs ( " & _
            "apq_apl_id, apq_type , apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_grade, apq_spec ) " & _
         "VALUES ( " & _
            intApplicId & ", 'Degree', " & GetQualifId(Me![Qualification Level]) & ", '" & Me![Degree / Diploma Name] & "', '" & Me![Institution Name] & "', " & _
            Me![Duration (years)] & ", 'Years', #" & Me![Completion Date] & "#, '" & Me![Grade / GPA / Division] & "', '" & Me!Majors & "' );"
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = strStatus & vbCrLf & "Education data imported - 01 record(s)"
'----------
Education_Data_1:
If IsNullish(Me![Qualification Level1]) Then GoTo Courses_Data_0
strStep = "Education data 1"
strSql = "INSERT INTO hr_applicqualifs ( " & _
            "apq_apl_id, apq_type , apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_grade, apq_spec ) " & _
         "VALUES ( " & _
            intApplicId & ", 'Degree', " & GetQualifId(Me![Qualification Level1]) & ", '" & Me![Degree / Diploma Name1] & "', '" & Me![Institution Name1] & "', " & _
            Me![Duration (years)1] & ", 'Years', #" & Me![Completion Date1] & "#, '" & Me![Grade / GPA / Division1] & "', '" & Me!Majors & "' );"
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = Replace(strStatus, "Education data imported - 01", "Education data imported - 02")

'Import courses / certifications data ---------------------------------------
Courses_Data_0:
If IsNullish(Me![Course / Certification Name]) Then GoTo Career_Data
strStep = "Courses data 0"
strSql = "INSERT INTO hr_applicqualifs ( " & _
            "apq_apl_id, apq_type , apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_grade, apq_spec, apq_license ) " & _
         "VALUES ( " & _
            intApplicId & ", 'Course', " & 0 & ", '" & Me![Course / Certification Name] & "', '" & Me![Institution] & "', " & _
            Me![Duration] & ", '" & [Duration unit] & "', #" & Me![Date of Completion] & "#, '" & Me![grade] & "', '" & Me![Specialization / Majors] & "', '" & _
            Me![License / Reference Number (if any)] & "' );"

Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = strStatus & vbCrLf & "Courses data imported - 01 record(s)"
'-----------
Courses_Data_1:
If IsNullish(Me![Course / Certification Name1]) Then GoTo Career_Data
strStep = "Courses data 1"
strSql = "INSERT INTO hr_applicqualifs ( " & _
            "apq_apl_id, apq_type , apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_grade, apq_spec, apq_license ) " & _
         "VALUES ( " & _
            intApplicId & ", 'Course', " & 0 & ", '" & Me![Course / Certification Name1] & "', '" & Me![Institution1] & "', " & _
            Me![Duration1] & ", '" & [Duration unit1] & "', #" & Me![Date of Completion1] & "#, '" & Me![Grade1] & "', '" & Me![Specialization / Majors1] & "', '" & _
            Me![License / Reference Number (if any)1] & "' );"
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = Replace(strStatus, "Courses data imported - 01", "Courses data imported - 02")
'-----------
Courses_Data_2:
If IsNullish(Me![Course / Certification Name2]) Then GoTo Career_Data
strStep = "Courses data 2"
strSql = "INSERT INTO hr_applicqualifs ( " & _
            "apq_apl_id, apq_type , apq_level, apq_name, apq_inst, apq_duration, apq_unit, apq_enddt, apq_grade, apq_spec, apq_license ) " & _
         "VALUES ( " & _
            intApplicId & ", 'Course', " & 0 & ", '" & Me![Course / Certification Name2] & "', '" & Me![Institution2] & "', " & _
            Me![Duration2] & ", '" & [Duration unit2] & "', #" & Me![Date of Completion2] & "#, '" & Me![Grade2] & "', '" & Me![Specialization / Majors2] & "', '" & _
            Me![License / Reference Number (if any)2] & "' );"
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = Replace(strStatus, "Courses data imported - 02", "Courses data imported - 03")

'Import job data ------------------------------------------------------------
Career_Data:
If IsNullish(Me![Employer Name]) Then GoTo End_of_Sub
strStep = "Career data"
strSql = "INSERT INTO hr_applicjobs ( " & _
            "apj_apl_id, apj_company, apj_city, apj_jobtitle, apj_repto, apj_from, apj_to, apj_resp, apj_team, apj_ach ) " & _
         "VALUES ( " & _
            intApplicId & ", '" & Me![Employer Name] & "', '" & Me![Employer City] & "', '" & Me![Job Title] & "', '" & Me![Reported to] & "', #" & _
            Me![Start Date] & "#, " & IIf(IsNull(Me![End Date]), "''", "#" & Me![End Date] & "#") & ", '" & Me![List Down Major Responsibilities] & "', " & _
            Me![Number of Resources Managed] & ", '" & Me![List Down Any Achievement / Rewards] & "' ) "
Set qryImp = dbsImp.CreateQueryDef("", strSql)
qryImp.Execute
strStatus = strStatus & vbCrLf & "Career data imported - 01 record"

End_of_Sub:
MsgBox strStatus

Exit Sub
Error_Handler:
If strStep = "Personal data" Then
    MsgBox "This applicant is already present in database with this timestamp."
    Else
    MsgBox strStep & " - Error " & Err.Number & ": " & Err.Description
    End If

Select Case strStep
    Case "Personal data"
        Exit Sub
    Case "Education data 0"
        GoTo Education_Data_1
    Case "Education data 1"
        GoTo Courses_Data_0
    Case "Courses data 0"
        GoTo Courses_Data_1
    Case "Courses data 1"
        GoTo Courses_Data_2
    Case "Courses data 2"
        GoTo Career_Data
    Case "Career data"
        GoTo End_of_Sub
    End Select
End Sub

Private Function GetQualifId(strQualification As String) As Integer
Dim rstQualif As Recordset
Set rstQualif = CurrentDb().OpenRecordset("SELECT qualification_id, qualification_name FROM qualifications WHERE qualification_name = '" & Me!Qualification & "';", dbOpenSnapshot)
GetQualifId = rstQualif!qualification_id
End Function

Private Function IsNullish(strInput As Variant) As Boolean
If IsNull(strInput) Or strInput = "" Or strInput = "Nil" Or strInput = "Nill" Or strInput = "None" Or strInput = "No" _
                                    Or strInput = "NA" Or strInput = "N.A" Or strInput = "N.A." Or strInput = "N/A" Then
    IsNullish = True
    Else
    IsNullish = False
    End If
End Function


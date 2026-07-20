-- Query: hr_applicants_import
-- Type: 64

INSERT INTO hr_applicants ( apl_dtg, apl_email, apl_name, apl_rank, apl_father, apl_cnic, apl_gender, apl_ntnlty, apl_qualif, apl_discip, apl_spec, apl_dob, apl_pob, apl_marital, apl_mobile, apl_mobile2, apl_landline, apl_taddress, apl_paddress, apl_status, apl_appliedfor, apl_currentsal, apl_expectedsal, apl_experience, apl_expjoindt )
SELECT hr_applicants_stage.Timestamp, hr_applicants_stage.[Email address], hr_applicants_stage.[Applicant Name], hr_applicants_stage.Rank, hr_applicants_stage.[Father Name], hr_applicants_stage.[CNIC Number], hr_applicants_stage.Gender, hr_applicants_stage.Nationality, 3 AS Expr1, hr_applicants_stage.Discipline, hr_applicants_stage.Specialty, hr_applicants_stage.[Date of Birth], hr_applicants_stage.[Place of Birth], hr_applicants_stage.[Marital status], hr_applicants_stage.[Mobile Number], hr_applicants_stage.[Mobile Number 2], hr_applicants_stage.[Landline Number], hr_applicants_stage.[Temporary Address], hr_applicants_stage.[Permanent Address], "Active" AS Expr2, hr_applicants_stage.[Job Title applied for], hr_applicants_stage.[Current Salary], hr_applicants_stage.[Expected Salary], hr_applicants_stage.[Work experience (years)], hr_applicants_stage.[Expected Date of Joining]
FROM hr_applicants_stage
WHERE (((hr_applicants_stage.Timestamp)=[Date and time]) AND ((hr_applicants_stage.[CNIC Number])=[CNIC]));


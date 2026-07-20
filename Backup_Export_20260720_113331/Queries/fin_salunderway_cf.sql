-- Query: fin_salunderway_cf
-- Type: 0

SELECT hr_ctrcases.ctc_id, hr_ctrcases.ctc_emp_id, hr_ctrcases.ctc_unt_id, hr_ctrcases.ctc_newsalary, hr_ctrcases.ctc_empnamecomp, hr_emps.emp_joindt, hr_ctrcaseplans.ccp_startdt AS ccp_effstartdt, hr_ctrcaseplans.ccp_enddt AS ccp_effenddt, hr_ctrcaseplans.ccp_hed_id, lastdatethismonth([ccp_enddt]) AS sal_month, hr_ctrcases.ctc_status
FROM (hr_emps INNER JOIN hr_ctrcases ON hr_emps.emp_id = hr_ctrcases.ctc_emp_id) INNER JOIN hr_ctrcaseplans ON hr_ctrcases.ctc_id = hr_ctrcaseplans.ccp_ctc_id
WHERE (((hr_ctrcases.ctc_unt_id)<200000 Or (hr_ctrcases.ctc_unt_id)>=800000) AND ((hr_ctrcases.ctc_status) Like "Under*"))
ORDER BY hr_ctrcaseplans.ccp_startdt;


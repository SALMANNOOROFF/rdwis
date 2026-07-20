-- Query: hr_ctrcaseplans_summary
-- Type: 0

SELECT hr_ctrcaseplans_summary1.hed, Min(hr_ctrcaseplans_summary1.ccp_startdt) AS start, Max(hr_ctrcaseplans_summary1.ccp_enddt) AS [end], Format(Min([ccp_startdt]),"mmm yy") & IIf([start]=FirstdateThisMonth([end]),"","  –  " & Format(Max([ccp_enddt]),"mmm yy")) AS tenure, First(hr_ctrcaseplans_summary1.ctc_newsalary) AS salary, Sum(hr_ctrcaseplans_summary1.amount) AS amount, First(hr_ctrcaseplans_summary1.ccp_ctc_id) AS ccp_ctc_id
FROM hr_ctrcaseplans_summary1
GROUP BY hr_ctrcaseplans_summary1.hed;


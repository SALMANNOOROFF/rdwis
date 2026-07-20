-- Query: hr_ctrcaseminute_status_temp_chrf_adder1
-- Type: 0

SELECT cen_heads_open.hed_id, cen_heads_open.hed_code, Format(Nz(GetA16Element("GetAccShares",([hed_id]),2),"Undefined"),"#,##0") AS share, Format(GetCfExpenditure([hed_id]),"#,##0") AS exp, Format(GetCfOutstandingCommits([hed_id]),"#,##0") AS [commit], Format(GetCfInProcess([hed_id]),"#,##0") AS inproc
FROM cen_heads_open
WHERE (((cen_heads_open.hed_type)="Project"))
ORDER BY cen_heads_open.hed_id;


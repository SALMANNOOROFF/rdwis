-- Query: hr_ctrcaseminute_status_temp_adder
-- Type: 64

INSERT INTO hr_ctrcaseminute_status_temp ( ccp_hed_id, hed_code, amount, prj_share_hr, prj_exp_hr, prj_commit_hr, forecast, underway, diff )
SELECT hr_ctrcaseminute_status_grouped.ccp_hed_id, hr_ctrcaseminute_status_grouped.head_code, hr_ctrcaseminute_status_grouped.amount, IIf(IsNull([ccp_hed_id]),Null,Nz(GetSubHeadFigure("GetPrjAllocationsShd",[ccp_hed_id],"HR"),0)) AS share, IIf(IsNull([ccp_hed_id]),Null,Nz(GetSubHeadFigure("GetPrjExpenditureShd",[ccp_hed_id],"HR"),0)) AS exp, IIf(IsNull([ccp_hed_id]),Null,Nz(GetSubHeadFigure("GetPrjOutstandingCommitsShd",[ccp_hed_id],"HR"),0)) AS [commit], IIf(IsNull([ccp_hed_id]),Null,Nz(GetPrjSalForecast([ccp_hed_id]),0)) AS forecast, IIf(IsNull([ccp_hed_id]),0,GetPrjSalUnderway([ccp_hed_id]))+IIf([ctc_status]="Draft",[amount],0) AS underway_mod, [share]-[exp]-[commit]-[forecast]-[underway_mod] AS diff
FROM hr_ctrcaseminute_status_grouped;


-- Query: pur_purcasechrf_status2
-- Type: 0

SELECT cen_heads.hed_id, cen_heads.hed_code, cen_heads.hed_transtype, GetA16Element("GetAccShares",[hed_id],2) AS cf, Nz(GetCfReceived([hed_id]),0) AS cf_rec, GetCfExpenditure([hed_id]) AS cf_exp, GetCfOutstandingCommits([hed_id]) AS cf_cmt, GetCfInProcess([hed_id]) AS cf_ipc, [cf_rec]-[cf_exp]-[cf_cmt]-[cf_ipc] AS cf_avl, pur_purcasechrf_status1.pcs_midprice, pur_purcasechrf_status1.pcs_id
FROM pur_purcasechrf_status1 INNER JOIN cen_heads ON pur_purcasechrf_status1.pcs_effhed_id = cen_heads.hed_id
ORDER BY cen_heads.hed_id;


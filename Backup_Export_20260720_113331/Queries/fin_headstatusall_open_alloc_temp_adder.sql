-- Query: fin_headstatusall_open_alloc_temp_adder
-- Type: 64

INSERT INTO fin_headstatusall_alloc_temp ( hed_id, hed_code, hed_unt_id, transtype, allocation, mtss_share, rdw_share, pcc_share, csrf_share, prj_share, prj_a, prj_b, prj_c )
SELECT cen_heads_base.hed_id, cen_heads_base.hed_code, cen_heads_base.hed_unt_id, IIf([hed_transtype]=1,"Without GST","With GST") AS transtype, GetAccAllocation([hed_id]) AS allocation, GetAccMtssShare([hed_id]) AS mtss_share, Round([allocation]-[mtss_share],1) AS rdw_share, GetA16Element("GetAccShares",[hed_id],1) AS pcc_share, GetA16Element("GetAccShares",[hed_id],2) AS cf_share, GetA16Element("GetAccShares",[hed_id],3) AS prj_share, GetSubHeadFigure("GetPrjAllocationsShd",[hed_id],"Equipment") AS equip, GetSubHeadFigure("GetPrjAllocationsShd",[hed_id],"HR") AS hr, GetSubHeadFigure("GetPrjAllocationsShd",[hed_id],"Misc") AS misc
FROM cen_heads_base
ORDER BY cen_heads_base.hed_id;


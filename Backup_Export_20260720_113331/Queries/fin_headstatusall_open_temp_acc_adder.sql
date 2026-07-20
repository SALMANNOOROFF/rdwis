-- Query: fin_headstatusall_open_temp_acc_adder
-- Type: 64

INSERT INTO fin_headstatusall_temp ( hed_id, hed_code, hed_unt_id, transtype, allocation, mtss_share, rdw_share, received, expenditure, commitments, inprocess, available, yettoberec, canbespent )
SELECT cen_heads_base.hed_id, cen_heads_base.hed_code, cen_heads_base.hed_unt_id, IIf([hed_transtype]=1,"Without GST","With GST") AS transtype, GetAccAllocation([hed_id]) AS allocation, GetAccMtssShare([hed_id]) AS mtss_share, Round([allocation]-[mtss_share],2) AS rdw_share, GetAccReceived([hed_id]) AS received, GetAccExpenditure([hed_id]) AS expenditure, GetAccOutstandingCommits([hed_id]) AS commitments, GetAccInProcess([hed_id]) AS inprocess, Round([received]-[expenditure]-[commitments]-[inprocess],2) AS available, Round([allocation]-[received],2) AS yettoberec, Round([allocation]-[mtss_share]-[expenditure]-[commitments]-[inprocess],2) AS canbespent
FROM cen_heads_base
ORDER BY cen_heads_base.hed_id;


-- Query: fin_loans1
-- Type: 0

SELECT fin_commitments.cmt_type, -1*[trn_amount2] AS amount, cen_heads_1.hed_code AS [From Project], units_1.unt_namesh AS [From Div], IIf(IsNull([cen_heads].[hed_code]),"RGA",[cen_heads].[hed_code]) AS [For Project], units.unt_namesh AS [For Div], [From Project] & "-" & Nz([For Project],"RGA") AS loan_string
FROM ((((fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) LEFT JOIN cen_heads AS cen_heads_1 ON fin_commitments.cmt_effhed_id = cen_heads_1.hed_id) LEFT JOIN units AS units_1 ON fin_commitments.cmt_effunt_id = units_1.unt_id) LEFT JOIN cen_heads ON fin_commitments.cmt_hed_id = cen_heads.hed_id) LEFT JOIN units ON fin_commitments.cmt_unt_id = units.unt_id
WHERE (((fin_commitments.cmt_type) In ("Ps","Rb","Sa","LO","TO")) AND ((fin_commitments.cmt_effhed_id)<>Nz([cmt_hed_id],160001)) AND ((fin_transactions.trn_noloan)="0"));


-- Query: fin_loans_div1
-- Type: 0

SELECT fin_commitments.cmt_type, -1*[trn_amount2] AS amount, IIf([cmt_effunt_id]<800000,[units_1].[unt_namesh],"RDW") AS [From], IIf([cmt_unt_id]<800000,[units].[unt_namesh],"RDW") AS [For], [From] & "-" & Nz([For],"RGA") AS [Loan String]
FROM ((fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) LEFT JOIN units AS units_1 ON fin_commitments.cmt_effunt_id = units_1.unt_id) LEFT JOIN units ON fin_commitments.cmt_unt_id = units.unt_id
WHERE (((fin_commitments.cmt_type) In ("Ps","Rb","Sa","LO","TO")) AND ((fin_commitments.cmt_effhed_id)<>Nz([cmt_hed_id],160001)) AND ((fin_transactions.trn_noloan)="0") AND ((fin_commitments.cmt_effunt_id)<>[cmt_unt_id]));


-- Query: aud_chk_multitrans_pcs
-- Type: 0

SELECT fin_commitments_pcs.cmt_id, First(fin_commitments_pcs.cmt_amount) AS FirstOfcmt_amount, Sum(IIf([trn_transtype]=1,Nz([trn_amount1],0),Nz([trn_amount2],0))) AS amount, First(fin_commitments_pcs.cmt_status) AS FirstOfcmt_status
FROM fin_commitments_pcs LEFT JOIN fin_transactions ON fin_commitments_pcs.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_transactions.trn_transtype)=1) AND ((fin_transactions.trn_amount1)<>[cmt_amount])) OR (((fin_transactions.trn_transtype)=2) AND ((fin_transactions.trn_amount2)<>[cmt_amount])) OR (((fin_transactions.trn_amount1) Is Null))
GROUP BY fin_commitments_pcs.cmt_id;


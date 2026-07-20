-- Query: fin_sto_acc_exp1
-- Type: 0

SELECT fin_commitments.cmt_docid, fin_commitments.cmt_type, IIf([trn_transtype]=1,[trn_amount1],[trn_amount2]) AS amount, fin_commitments.cmt_effhed_id, fin_transactions.trn_id
FROM fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_type) In ("Ps","Pt","Rb","Sa","TO")) And ((fin_commitments.cmt_effhed_id)=Forms!vars!Parameter1));


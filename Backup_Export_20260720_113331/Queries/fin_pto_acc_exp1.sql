-- Query: fin_pto_acc_exp1
-- Type: 0

SELECT fin_commitments.cmt_docid, fin_commitments.cmt_type, IIf([trn_transtype]=1,[trn_amount1],[trn_amount2]) AS amount, fin_commitments.cmt_effhed_id, fin_transactions.trn_id
FROM pur_purcases INNER JOIN (fin_commitments INNER JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) ON (pur_purcases.pcs_type = fin_commitments.cmt_type) AND (pur_purcases.pcs_id = fin_commitments.cmt_docid)
WHERE (((fin_commitments.cmt_type)="Pt") And ((fin_commitments.cmt_effhed_id)=Forms!vars!Parameter1) And ((pur_purcases.pcs_date)=Forms!vars!Parameter2));


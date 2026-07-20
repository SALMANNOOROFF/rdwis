-- Query: fin_ectimeline_pc_tempadder1
-- Type: 0

SELECT pur_purcases.pcs_date, pur_purcases.pcs_id, pur_purcases.pcs_type, pur_purcases.pcs_title, Round(IIf([pcs_status]="Partially Fulfilled",IIf([trn_transtype]=1,[trn_amount1],[trn_amount2]),IIf([pcs_transtype]=1,[pcs_midprice],[pcs_price]))*[pcd_ratio],0) AS price, pur_purcases_shd.pcd_subhead, pur_purcases_shd.pcd_ratio, pur_purcases.pcs_status
FROM ((pur_purcases INNER JOIN fin_commitments ON (pur_purcases.pcs_type = fin_commitments.cmt_type) AND (pur_purcases.pcs_id = fin_commitments.cmt_docid)) LEFT JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id) INNER JOIN pur_purcases_shd ON (pur_purcases.pcs_type = pur_purcases_shd.pcd_type) AND (pur_purcases.pcs_id = pur_purcases_shd.pcd_pcs_id)
WHERE (((pur_purcases.pcs_status)="Approved") And ((pur_purcases.pcs_hed_id)=Forms!vars!Parameter1) And ((pur_purcases.pcs_sudohed) Is Null Or (pur_purcases.pcs_sudohed)="")) Or (((pur_purcases.pcs_status)="Fulfilled") And ((pur_purcases.pcs_hed_id)=Forms!vars!Parameter1) And ((pur_purcases.pcs_sudohed) Is Null Or (pur_purcases.pcs_sudohed)="")) Or (((pur_purcases.pcs_status)="Partially Fulfilled") And ((pur_purcases.pcs_hed_id)=Forms!vars!Parameter1) And ((pur_purcases.pcs_sudohed) Is Null Or (pur_purcases.pcs_sudohed)=""));


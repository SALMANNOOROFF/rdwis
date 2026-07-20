-- Query: fin_sto_cf_commitsoutst1
-- Type: 0

SELECT First(fin_commitments.cmt_docid) AS cmt_docid, First(fin_commitments.cmt_type) AS cmt_type, fin_commitments.cmt_id, First(fin_commitments.cmt_amount) AS cmt_amount, Sum(IIf([trn_transtype]=1,Nz([trn_amount1],0),Nz([trn_amount2],0))) AS amount, First(fin_commitments.cmt_effhed_id) AS cmt_effhed_id, First(fin_commitments.cmt_hed_id) AS cmt_hed_id, First(fin_transactions.trn_id) AS trn_id
FROM fin_commitments LEFT JOIN fin_transactions ON fin_commitments.cmt_id = fin_transactions.trn_cmt_id
WHERE (((fin_commitments.cmt_effhed_id)=Forms!vars!Parameter1) And ((fin_commitments.cmt_type) In ("Ps","Pt","Rb","Sa")) And ((fin_commitments.cmt_status)="Awaited") And ((fin_commitments.cmt_sudohed) Is Not Null And (fin_commitments.cmt_sudohed)<>""))
GROUP BY fin_commitments.cmt_id;


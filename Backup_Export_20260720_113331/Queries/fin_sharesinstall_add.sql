-- Query: fin_sharesinstall_add
-- Type: 0

SELECT fin_transfers.trf_id, fin_transfers.trf_date, fin_transfers.trf_type, fin_transfers.trf_title, fin_transfers.trf_amount, fin_transfers.trf_fromhed, fin_transfers.trf_fromunt, fin_transfers.trf_tohed, fin_transfers.trf_tount, fin_transfers.trf_status, fin_commitments_trf.cmt_id, fin_commitments_trf.cmt_status, fin_commitments_trf.cmt_amount
FROM fin_transfers INNER JOIN fin_commitments_trf ON fin_transfers.trf_id = fin_commitments_trf.cmt_docid
WHERE (((fin_transfers.trf_type)="FI") And ((fin_transfers.trf_fromunt)>=getvar("varLower") And (fin_transfers.trf_fromunt)<=getvar("varUpper")) And ((fin_transfers.trf_tohed)=Forms!vars!Parameter1) And ((fin_transfers.trf_status)<>"Cancelled") And ((fin_commitments_trf.cmt_status)="Awaited")) Or (((fin_transfers.trf_type)="FI") And ((fin_transfers.trf_tohed)=Forms!vars!Parameter1) And ((fin_transfers.trf_tount)>=getvar("varLower") And (fin_transfers.trf_tount)<=getvar("varUpper")) And ((fin_transfers.trf_status)<>"Cancelled") And ((fin_commitments_trf.cmt_status)="Awaited"));


-- Query: fin_loans_detail41_plus
-- Type: 0

SELECT fin_loans_detail41.*, "Salary " & Format([sor_month],"mmm yy") & " - " & Nz([sor_empnamecomp],"unknown") AS sor_title, fin_salorders.sor_releasedtg AS sor_date, pur_purcases.pcs_title, pur_purcases.pcs_date, IIf(IsNull([sor_date]),[pcs_date],[sor_date]) AS doc_date, IIf([cmt_type]="Sa",[sor_title],[pcs_title]) AS doc_title
FROM (fin_salorders RIGHT JOIN fin_loans_detail41 ON (fin_salorders.sor_id = fin_loans_detail41.cmt_docid) AND (fin_salorders.sor_type = fin_loans_detail41.cmt_type)) LEFT JOIN pur_purcases ON (fin_loans_detail41.cmt_type = pur_purcases.pcs_type) AND (fin_loans_detail41.cmt_docid = pur_purcases.pcs_id);


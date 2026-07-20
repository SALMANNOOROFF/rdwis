-- Query: fin_docs_shd
-- Type: 128

SELECT sod_sor_id as doc_id, sod_type as doc_type, sod_subhead as subhead, sod_ratio as ratio FROM fin_salorders_shd
UNION SELECT pcd_pcs_id, pcd_type, pcd_subhead,pcd_ratio FROM pur_purcases_shd;


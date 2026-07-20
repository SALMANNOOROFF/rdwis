WITH req AS (
  SELECT DISTINCT LOWER(TRIM(i.pri_desc)) AS title,
         NULLIF(i.pri_qtyunit,'') AS qtyunit,
         r.prq_unt_id AS unt_id,
         r.prq_hed_id AS hed_id
  FROM pur.purreqitems i
  JOIN pur.purreqs r ON r.prq_id=i.pri_prq_id
),
pcs AS (
  SELECT DISTINCT LOWER(TRIM(i.pci_desc)) AS title,
         NULLIF(i.pci_qtyunit,'') AS qtyunit,
         c.pcs_unt_id AS unt_id,
         c.pcs_hed_id AS hed_id
  FROM pur.purcaseitems i
  JOIN pur.purcases c ON c.pcs_id=i.pci_pcs_id
),
all_items AS (
  SELECT title, COALESCE(qtyunit,'unit') AS qtyunit, unt_id, hed_id FROM req
  UNION
  SELECT title, COALESCE(qtyunit,'unit') AS qtyunit, unt_id, hed_id FROM pcs
)
INSERT INTO puritems.items(itm_title, itm_desc, itm_qtyunit, itm_cat_id, itm_sub_id, itm_unt_id, itm_hed_id)
SELECT a.title, a.title, a.qtyunit, c.cat_id, s.sub_id, a.unt_id, a.hed_id
FROM all_items a
CROSS JOIN LATERAL (SELECT cat_id FROM puritems.categories WHERE cat_name='Uncategorized' LIMIT 1) c
CROSS JOIN LATERAL (SELECT sub_id FROM puritems.subcategories WHERE sub_name='Uncategorized' LIMIT 1) s
ON CONFLICT ON CONSTRAINT puritems_items_uq DO NOTHING;

INSERT INTO puritems.prices(prc_itm_id, prc_base, prc_gst, prc_sst, prc_gross, prc_qty, prc_qtyunit, effective_date)
SELECT it.itm_id,
       qi.qti_price,
       0,
       0,
       qi.qti_price,
       qi.qti_qty,
       qi.qti_qtyunit,
       CURRENT_DATE
FROM pur.quoteitems qi
JOIN pur.quotes q ON q.qte_id=qi.qti_qte_id
JOIN puritems.items it
  ON LOWER(it.itm_title)=LOWER(TRIM(qi.qti_desc))
 AND it.itm_qtyunit=qi.qti_qtyunit;

INSERT INTO puritems.prices(prc_itm_id, prc_base, prc_gst, prc_sst, prc_gross, prc_qty, prc_qtyunit, effective_date)
SELECT it.itm_id,
       COALESCE(ci.pci_price,0),
       0,
       0,
       COALESCE(ci.pci_price,0),
       ci.pci_qty,
       ci.pci_qtyunit,
       CURRENT_DATE
FROM pur.purcaseitems ci
JOIN pur.purcases c ON c.pcs_id=ci.pci_pcs_id
JOIN puritems.items it
  ON LOWER(it.itm_title)=LOWER(TRIM(ci.pci_desc))
 AND it.itm_qtyunit=ci.pci_qtyunit
LEFT JOIN pur.quoteitems qi
  ON LOWER(TRIM(qi.qti_desc))=LOWER(TRIM(ci.pci_desc))
 AND qi.qti_qtyunit=ci.pci_qtyunit
WHERE qi.qti_id IS NULL;


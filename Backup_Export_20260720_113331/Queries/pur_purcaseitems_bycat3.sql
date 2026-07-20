-- Query: pur_purcaseitems_bycat3
-- Type: 0

SELECT pur_purcaseitems_bycat1.*, pur_purcaseitems_bycat2.tax_prop_sum, Round([tax_prop]/[tax_prop_sum]*[pcs_midtax],0) AS tax
FROM pur_purcaseitems_bycat1, pur_purcaseitems_bycat2;


-- Query: fin_recordfrom1
-- Type: 128

Select * From fin_recordfrom_trfx
UNION
Select * From fin_recordfrom_pcs
UNION Select * From fin_recordfrom_so;


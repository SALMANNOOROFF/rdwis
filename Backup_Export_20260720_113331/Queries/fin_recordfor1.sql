-- Query: fin_recordfor1
-- Type: 128

Select * From fin_recordfor_trfx
UNION
Select * From fin_recordfor_pcs
UNION Select * From fin_recordfor_so;


-- Query: fin_inttransfers_u
-- Type: 0

SELECT fin_transfers.*
FROM fin_transfers
WHERE (((fin_transfers.trf_type)="T"))
ORDER BY fin_transfers.trf_date DESC;


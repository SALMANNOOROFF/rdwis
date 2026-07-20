-- Query: pur_quotesmin
-- Type: 0

SELECT pur_quotes.qte_id
FROM pur_quotes INNER JOIN pur_quotesmin1 ON (pur_quotes.qte_price = pur_quotesmin1.min_price) AND (pur_quotes.qte_pcs_id = pur_quotesmin1.qte_pcs_id);


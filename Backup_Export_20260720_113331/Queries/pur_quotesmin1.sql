-- Query: pur_quotesmin1
-- Type: 0

SELECT pur_quotes.qte_pcs_id, Min(pur_quotes.qte_price) AS min_price
FROM pur_quotes
WHERE (((pur_quotes.qte_pcs_id)=[PurCaseId]) AND ((pur_quotes.qte_techaccept)="1"))
GROUP BY pur_quotes.qte_pcs_id;


-- Query: fin_sto_acc_rcvmsncompleted3
-- Type: 0

SELECT fin_sharesinstall_plus.shi_hed_id, Sum([shi_pcc]+[shi_cf]) AS rec_acc
FROM fin_sharesinstall_plus
GROUP BY fin_sharesinstall_plus.shi_hed_id;


-- Query: fin_sto_acc_rcvmsncompleted2
-- Type: 0

SELECT fin_sto_acc_rcvmsncompleted1.mct_hed_id, fin_sto_acc_rcvmsncompleted1.costcmp, Round([costcmp]*([alloc]-[mtss_share])/[alloc],0) AS costcmp_acc
FROM fin_sto_acc_rcvmsncompleted1 INNER JOIN fin_sharesalloc_plus ON fin_sto_acc_rcvmsncompleted1.mct_hed_id = fin_sharesalloc_plus.sha_hed_id;


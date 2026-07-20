-- Query: fin_sto_acc_rcvmsncompleted
-- Type: 0

SELECT fin_sto_acc_rcvmsncompleted2.mct_hed_id, fin_sto_acc_rcvmsncompleted2.costcmp, fin_sto_acc_rcvmsncompleted2.costcmp_acc, Nz([rec_acc],0) AS received, IIf([costcmp]=0,0,[costcmp_acc]-[received]) AS amount
FROM fin_sto_acc_rcvmsncompleted2 LEFT JOIN fin_sto_acc_rcvmsncompleted3 ON fin_sto_acc_rcvmsncompleted2.mct_hed_id = fin_sto_acc_rcvmsncompleted3.shi_hed_id;


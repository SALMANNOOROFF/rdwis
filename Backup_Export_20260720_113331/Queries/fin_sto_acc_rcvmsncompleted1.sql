-- Query: fin_sto_acc_rcvmsncompleted1
-- Type: 0

SELECT fin_msncosts.mct_hed_id, Sum(fin_msncosts.mct_cost) AS costcmp
FROM prj_milestones INNER JOIN fin_msncosts ON prj_milestones.msn_idd = fin_msncosts.mct_msn_idd
WHERE (((fin_msncosts.mct_hed_id)=Forms!vars!Parameter1) And ((prj_milestones.msn_status)="Completed"))
GROUP BY fin_msncosts.mct_hed_id;


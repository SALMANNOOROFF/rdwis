-- Query: fin_sto_acc_rcvmsncurrent
-- Type: 0

SELECT fin_msncosts.mct_hed_id, fin_msncosts.mct_cost, Round([mct_cost]*(([sha_cf]+[sha_pcc])/[cmt_amount]),0) AS amount
FROM prj_milestones INNER JOIN ((fin_msncosts INNER JOIN fin_sharesalloc ON fin_msncosts.mct_hed_id = fin_sharesalloc.sha_hed_id) INNER JOIN fin_commitments ON fin_sharesalloc.sha_ficmt_id = fin_commitments.cmt_id) ON prj_milestones.msn_idd = fin_msncosts.mct_msn_idd
WHERE (((fin_msncosts.mct_hed_id)=Forms!vars!Parameter1) And ((prj_milestones.msn_status)="In progress"))
ORDER BY fin_msncosts.mct_msn_id;


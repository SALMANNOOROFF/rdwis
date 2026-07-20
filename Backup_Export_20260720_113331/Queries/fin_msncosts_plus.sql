-- Query: fin_msncosts_plus
-- Type: 0

SELECT fin_msncosts.mct_msn_idd, fin_msncosts.mct_msn_id, fin_msncosts.mct_prj_id, fin_msncosts.mct_hed_id, fin_msncosts.mct_cost, prj_milestones.msn_desc, prj_milestones.msn_status, prj_milestones.msn_id
FROM fin_msncosts RIGHT JOIN prj_milestones ON fin_msncosts.mct_msn_idd = prj_milestones.msn_idd
WHERE (((fin_msncosts.mct_hed_id)=[Forms]![vars]![Parameter1]))
ORDER BY prj_milestones.msn_id;


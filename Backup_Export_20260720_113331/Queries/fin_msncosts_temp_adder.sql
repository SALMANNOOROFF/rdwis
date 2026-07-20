-- Query: fin_msncosts_temp_adder
-- Type: 64

INSERT INTO fin_msncosts_plus_temp ( mct_msn_idd, mct_msn_id, mct_prj_id, mct_hed_id, mct_cost, mct_cost_old, mct_cost_detail, msn_desc, msn_status )
SELECT fin_msncosts_plus.mct_msn_idd, fin_msncosts_plus.mct_msn_id, fin_msncosts_plus.mct_prj_id, fin_msncosts_plus.mct_hed_id, fin_msncosts_plus.mct_cost, fin_msncosts_plus.mct_cost, "c,mct_cost,Milestone Cost,(mct_msn_id),1,numeric,,,,,Select * From fin_msncosts Where mct_msn_idd = P1,(mct_msn_idd),,,,fin_msncosts,mct_msn_idd,(mct_msn_idd),,," AS detail, fin_msncosts_plus.msn_desc, fin_msncosts_plus.msn_status
FROM fin_msncosts_plus;


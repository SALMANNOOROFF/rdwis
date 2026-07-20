-- Query: pur_purcasechrf_status
-- Type: 0

SELECT pur_purcasechrf_status2.hed_id, pur_purcasechrf_status2.hed_code, pur_purcasechrf_status2.hed_transtype, IIf(IsNull([cf]),"Undefined",Format(CLng([cf]),"#,##0")) AS cf_mod, IIf(IsNull([cf_rec]),"Undefined",Format(CLng([cf_avl]),"#,##0")) AS cf_rec_mod, IIf(IsNull([cf_exp]),"Undefined",Format(CLng([cf_exp]),"#,##0")) AS cf_exp_mod, IIf(IsNull([cf_cmt]),"Undefined",Format(CLng([cf_cmt]),"#,##0")) AS cf_cmt_mod, IIf(IsNull([cf_ipc]),"Undefined",Format(CLng([cf_ipc]),"#,##0")) AS cf_ipc_mod, IIf(IsNull([cf_avl]),"Undefined",Format(CLng([cf_avl]),"#,##0")) AS cf_avl_mod, pur_purcasechrf_status2.pcs_midprice, pur_purcasechrf_status2.pcs_id
FROM pur_purcasechrf_status2;


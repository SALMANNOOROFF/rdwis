-- Query: fin_subheads_sorted
-- Type: 0

SELECT fin_subheads.sbh_hed_id, fin_subheads.sbh_name, fin_subheads.sbh_alloc, IIf([sbh_name]="Misc","zzz",[sbh_name]) AS sorter
FROM fin_subheads
WHERE (((fin_subheads.sbh_hed_id)=[HeadID]))
ORDER BY IIf([sbh_name]="Misc","zzz",[sbh_name]);


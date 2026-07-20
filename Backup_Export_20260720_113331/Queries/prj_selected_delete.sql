-- Query: prj_selected_delete
-- Type: 32

DELETE selected.sel_mpr AS Expr1, [selected].[sel_mpr] AS Expr2
FROM selected
WHERE ((([selected].[sel_mpr])=[ProjectId]));


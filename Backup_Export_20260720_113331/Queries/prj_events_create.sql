-- Query: prj_events_create
-- Type: 64

INSERT INTO prj_events ( evt_name, evt_doer, evt_dtg, evt_xprj_id, evt_xpgh_id, evt_xcmt_id, evt_effectee )
SELECT [EventName] AS Expr1, getVar("varRoleDesigShort") AS Expr2, [EventDtg] AS Expr3, [ProjectId] AS Expr4, [ProgressId] AS Expr5, [CommentId] AS Expr6, [Effectee] AS Expr7;


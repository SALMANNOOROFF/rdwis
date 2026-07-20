-- Query: prj_events_delete
-- Type: 32

DELETE events.evt_id AS Expr1, [events].[evt_id] AS Expr2
FROM events
WHERE ((([events].[evt_id])=[event_id]));


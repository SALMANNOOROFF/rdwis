-- Query: prj_event_last1
-- Type: 0

SELECT events.evt_xpgh_id AS Expr1, Max(events.evt_dtg) AS dtg_last
FROM events
GROUP BY events.evt_xpgh_id;


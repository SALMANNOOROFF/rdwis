-- Query: fin_warnunassignedsalheads
-- Type: 0

SELECT fin_empeffheads.eeh_emphed_id, fin_empeffheads.eeh_status
FROM fin_empeffheads
WHERE (((fin_empeffheads.eeh_emphed_id) Is Null) AND ((fin_empeffheads.eeh_status)<>"Closed"));


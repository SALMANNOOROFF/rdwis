-- Query: hr_reset_salaryhead
-- Type: 48

UPDATE fin_empeffheads SET fin_empeffheads.eeh_emphed_id = Null, fin_empeffheads.eeh_sudohed = "", fin_empeffheads.eeh_dtg = [EntryDtg]
WHERE (((fin_empeffheads.eeh_emp_id)=[Emp]));


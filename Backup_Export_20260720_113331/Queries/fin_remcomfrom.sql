-- Query: fin_remcomfrom
-- Type: 0

SELECT fin_remcomfrom1.docid, fin_remcomfrom1.doctype, DateValue([rdate]) AS rsdate, fin_remcomfrom1.title, fin_remcomfrom1.effhed_id, fin_remcomfrom1.effunt_id, fin_remcomfrom1.hed_id, fin_remcomfrom1.unt_id, fin_remcomfrom1.commit, fin_remcomfrom1.paid, fin_remcomfrom1.rem, fin_remcomfrom1.sudohead, IIf([doctype]="Sa","sal","pur") AS doctype_mod, fin_remcomfrom1.cmt_id
FROM fin_remcomfrom1
ORDER BY DateValue([rdate]);


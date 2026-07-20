-- Query: hr_qualifs_max1
-- Type: 0

SELECT hr_qualifs.qlf_emp_id, Max(hr_qualifs.qlf_level) AS MaxOfqlf_level
FROM hr_qualifs
GROUP BY hr_qualifs.qlf_emp_id
HAVING (((Max(hr_qualifs.qlf_level))>0));


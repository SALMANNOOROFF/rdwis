-- Query: hr_attachsummary
-- Type: 0

SELECT hr_attachsummary5.Currency, hr_attachsummary5.eat_type, hr_attachsummary5.Status, Count(hr_attachsummary5.Status) AS CountOfStatus
FROM hr_attachsummary5
GROUP BY hr_attachsummary5.Currency, hr_attachsummary5.eat_type, hr_attachsummary5.Status;


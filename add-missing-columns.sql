
-- Add missing columns to hr.ctrcases
ALTER TABLE hr.ctrcases
ADD COLUMN ctc_divisionid integer,
ADD COLUMN ctc_createdby integer,
ADD COLUMN ctc_createdat timestamp without time zone,
ADD COLUMN ctc_releasedby integer;

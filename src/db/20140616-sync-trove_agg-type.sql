-- Use the same field type as group's, from which the value is copied.
-- Avoid errors like:
-- SQL> ERROR:  value too long for type character varying(255)

ALTER TABLE trove_agg ALTER COLUMN short_description TYPE text;

--
--	Convert downloads numbers to new format for RTS's patch
--
BEGIN;
UPDATE frs_dlstats_file SET MONTH=('2002'::text || month::text)::int;
COMMIT;


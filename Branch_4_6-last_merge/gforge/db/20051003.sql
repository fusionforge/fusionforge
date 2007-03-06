--ALTER TABLE plugin_cvstracker_data_master DROP COLUMN cvs_date;
--ALTER TABLE plugin_cvstracker_data_master ADD COLUMN cvs_date int;
--UPDATE plugin_cvstracker_data_master SET cvs_date=extract(epoch from now());
--ALTER TABLE plugin_cvstracker_data_master ALTER COLUMN cvs_date SET NOT NULL;


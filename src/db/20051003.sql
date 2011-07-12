ALTER TABLE plugin_cvstracker_data_master ADD COLUMN cvs_date_int int;
UPDATE plugin_cvstracker_data_master SET cvs_date_int=extract(epoch from cvs_date);
ALTER TABLE plugin_cvstracker_data_master DROP COLUMN cvs_date;
ALTER TABLE plugin_cvstracker_data_master RENAME COLUMN cvs_date_int TO cvs_date;
ALTER TABLE plugin_cvstracker_data_master ALTER COLUMN cvs_date SET NOT NULL;

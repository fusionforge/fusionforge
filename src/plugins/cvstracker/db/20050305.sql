-- TRUE? SELECT data_type = 'date' FROM information_schema.columns WHERE table_name = 'plugin_cvstracker_data_master' AND column_name='cvs_date';

DROP INDEX plugin_cvstracker_group_artifact_id;

ALTER TABLE plugin_cvstracker_data_master ADD COLUMN cvs_date2 int4;
ALTER TABLE plugin_cvstracker_data_master RENAME COLUMN cvs_date to dead1;
ALTER TABLE plugin_cvstracker_data_master ALTER COLUMN dead1 DROP NOT NULL;
ALTER TABLE plugin_cvstracker_data_master RENAME COLUMN cvs_date2 to cvs_date;                                   
UPDATE plugin_cvstracker_data_master SET cvs_date=date_part('epoch', dead1);
ALTER TABLE plugin_cvstracker_data_master ALTER COLUMN cvs_date SET NOT NULL;

CREATE INDEX plugincvstrackerdataartifact_groupartifactid ON plugin_cvstracker_data_artifact(group_artifact_id);
CREATE INDEX plugincvstrackerdataartifact_projecttaskid ON plugin_cvstracker_data_artifact(project_task_id);

CREATE INDEX plugincvstrackerdatamaster_holderid ON plugin_cvstracker_data_master(holder_id);
CREATE INDEX plugincvstrackerdatamaster_cvsdate ON plugin_cvstracker_data_master(cvs_date);

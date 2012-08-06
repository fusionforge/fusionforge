DROP INDEX plugin_svntracker_group_artifact_id;

ALTER TABLE plugin_svntracker_data_master ADD COLUMN svn_date2 int4;
ALTER TABLE plugin_svntracker_data_master RENAME COLUMN svn_date to dead1;
ALTER TABLE plugin_svntracker_data_master ALTER COLUMN dead1 DROP NOT NULL;
ALTER TABLE plugin_svntracker_data_master RENAME COLUMN svn_date2 to svn_date;
UPDATE plugin_svntracker_data_master SET svn_date=date_part('epoch', dead1);
ALTER TABLE plugin_svntracker_data_master ALTER COLUMN svn_date SET NOT NULL;

CREATE INDEX pluginsvntrackerdataartifact_groupartifactid ON plugin_svntracker_data_artifact(group_artifact_id);
CREATE INDEX pluginsvntrackerdataartifact_projecttaskid ON plugin_svntracker_data_artifact(project_task_id);

CREATE INDEX pluginsvntrackerdatamaster_holderid ON plugin_svntracker_data_master(holder_id);
CREATE INDEX pluginsvntrackerdatamaster_svndate ON plugin_svntracker_data_master(svn_date);

ALTER TABLE plugin_svntracker_data_master DROP COLUMN svn_date;
ALTER TABLE plugin_svntracker_data_master ADD COLUMN svn_date int;
UPDATE plugin_svntracker_data_master SET svn_date=extract(epoch from now());
ALTER TABLE plugin_svntracker_data_master ALTER COLUMN svn_date SET NOT NULL;

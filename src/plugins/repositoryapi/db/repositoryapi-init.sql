CREATE TABLE scm_activities (
  group_id INTEGER NOT NULL REFERENCES GROUPS ON DELETE CASCADE ON UPDATE CASCADE,
  plugin_id INTEGER NOT NULL REFERENCES plugins ON DELETE CASCADE ON UPDATE CASCADE,
  repository_id TEXT NOT NULL,
  tstamp INTEGER NOT NULL
);
CREATE INDEX scm_activities_rid_idx ON scm_activities USING btree(repository_id);

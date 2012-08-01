CREATE INDEX layouts_contents_owner_idx ON layouts_contents (owner_type,owner_id);

ALTER TABLE users_idx ADD PRIMARY KEY (user_id);
ALTER TABLE groups_idx ADD PRIMARY KEY (group_id);

DROP INDEX artmonitor_useridartid;
ALTER TABLE artifact_monitor DROP COLUMN id;
ALTER TABLE artifact_monitor ADD FOREIGN KEY (user_id) REFERENCES users(user_id);
DROP SEQUENCE artifact_monitor_id_seq;

ALTER TABLE docdata_monitored_docman DROP COLUMN monitor_id;
ALTER TABLE docdata_monitored_docman ADD PRIMARY KEY (doc_id, user_id);
ALTER TABLE docdata_monitored_docman ADD FOREIGN KEY (user_id) REFERENCES users(user_id);
ALTER TABLE docdata_monitored_docman ADD FOREIGN KEY (doc_id) REFERENCES doc_data(docid);
DROP SEQUENCE docdata_monitored_docman_pk_seq;

ALTER TABLE docgroup_monitored_docman DROP COLUMN monitor_id;
ALTER TABLE docgroup_monitored_docman ADD PRIMARY KEY (docgroup_id, user_id);
ALTER TABLE docgroup_monitored_docman ADD FOREIGN KEY (user_id) REFERENCES users(user_id);
ALTER TABLE docgroup_monitored_docman ADD FOREIGN KEY (docgroup_id) REFERENCES doc_groups(doc_group);
DROP SEQUENCE docgroup_monitored_docman_pk_seq;

DROP INDEX forummonitoredforums_useridforumid;
ALTER TABLE forum_monitored_forums DROP COLUMN monitor_id;
ALTER TABLE forum_monitored_forums ADD FOREIGN KEY (user_id) REFERENCES users(user_id);
ALTER TABLE forum_monitored_forums ADD FOREIGN KEY (forum_id) REFERENCES forum_group_list(group_forum_id);
DROP SEQUENCE forum_monitored_forums_pk_seq;

DROP INDEX userdiarymon_useridmonitoredid;
ALTER TABLE user_diary_monitor ADD FOREIGN KEY (user_id) REFERENCES users(user_id);
ALTER TABLE user_diary_monitor ADD FOREIGN KEY (monitored_user) REFERENCES users(user_id);
ALTER TABLE user_diary_monitor DROP COLUMN monitor_id;
DROP SEQUENCE user_diary_monitor_pk_seq;

## mysqldiff 0.25
## 
## run on Mon Dec 11 10:26:14 2000
##

ALTER TABLE activity_log DROP INDEX idx_activity_log_group; # was INDEX (group_id)
ALTER TABLE activity_log DROP INDEX type_idx; # was INDEX (type)
ALTER TABLE activity_log DROP INDEX idx_activity_log_day; # was INDEX (day)

ALTER TABLE activity_log_old DROP INDEX idx_activity_log_group; # was INDEX (group_id)
ALTER TABLE activity_log_old DROP INDEX type_idx; # was INDEX (type)
ALTER TABLE activity_log_old DROP INDEX idx_activity_log_day; # was INDEX (day)

ALTER TABLE activity_log_old_old DROP INDEX idx_activity_log_group; # was INDEX (group_id)
ALTER TABLE activity_log_old_old DROP INDEX type_idx; # was INDEX (type)
ALTER TABLE activity_log_old_old DROP INDEX idx_activity_log_day; # was INDEX (day)

ALTER TABLE doc_data ADD COLUMN language_id int(11) DEFAULT '1' NOT NULL;

DROP TABLE filedownload_log;

DROP TABLE filemodule;

ALTER TABLE filemodule_monitor ADD COLUMN id int(11) NOT NULL auto_increment;
ALTER TABLE filemodule_monitor ADD PRIMARY KEY (id);

DROP TABLE filerelease;

ALTER TABLE forum ADD COLUMN most_recent_date int(11) DEFAULT '0' NOT NULL;
ALTER TABLE forum DROP INDEX idx_forum_thread_date_followup; # was INDEX (thread_id,date,is_followup_to)
ALTER TABLE forum DROP INDEX idx_forum_is_followup_to; # was INDEX (is_followup_to)
ALTER TABLE forum DROP INDEX idx_forum_id_date_followup; # was INDEX (group_forum_id,date,is_followup_to)
ALTER TABLE forum DROP INDEX idx_forum_id_date; # was INDEX (group_forum_id,date)
ALTER TABLE forum ADD INDEX forum_forumid_msgid (group_forum_id,msg_id);
ALTER TABLE forum ADD INDEX forum_forumid_isfollto_mostrecentdate (group_forum_id,is_followup_to,most_recent_date);
ALTER TABLE forum ADD INDEX forum_threadid_isfollowupto (thread_id,is_followup_to);
ALTER TABLE forum ADD INDEX forum_forumid_isfollowupto (group_forum_id,is_followup_to);
ALTER TABLE forum ADD INDEX forum_forumid_threadid_mostrecent (group_forum_id,thread_id,most_recent_date);

ALTER TABLE forum_group_list ADD COLUMN allow_anonymous int(11) DEFAULT '0' NOT NULL;
ALTER TABLE forum_group_list ADD COLUMN send_all_posts_to text;

ALTER TABLE foundry_data CHANGE COLUMN foundry_id foundry_id int(11) DEFAULT '0' NOT NULL; # was int(11) NOT NULL auto_increment

ALTER TABLE frs_dlstats_filetotal_agg DROP PRIMARY KEY; # was ()
ALTER TABLE frs_dlstats_filetotal_agg ADD PRIMARY KEY ((file_id));

ALTER TABLE group_cvs_history ADD COLUMN id int(11) NOT NULL auto_increment;
ALTER TABLE group_cvs_history DROP PRIMARY KEY; # was ()
ALTER TABLE group_cvs_history ADD PRIMARY KEY ((id));

ALTER TABLE groups ADD COLUMN use_bug_depend_box int(11) DEFAULT '1' NOT NULL;
ALTER TABLE groups ADD COLUMN use_pm_depend_box int(11) DEFAULT '1' NOT NULL;
ALTER TABLE groups ADD COLUMN new_task_address text DEFAULT '' NOT NULL;
ALTER TABLE groups ADD COLUMN send_all_tasks int(11) DEFAULT '0' NOT NULL;

DROP TABLE image;

DROP TABLE mailaliases;

ALTER TABLE people_job_category ADD COLUMN private_flag int(11) DEFAULT '0' NOT NULL;

--database portability change
ALTER TABLE user RENAME AS users;
ALTER TABLE users ADD COLUMN language int(11) DEFAULT '1' NOT NULL;

ALTER TABLE user_group ADD COLUMN member_role int(11) DEFAULT '100' NOT NULL;
ALTER TABLE user_group ADD COLUMN release_flags int(11) DEFAULT '0' NOT NULL;
ALTER TABLE user_group ADD COLUMN cvs_flags int(11) DEFAULT '1' NOT NULL;

ALTER TABLE user_preferences ADD COLUMN set_date int(11) DEFAULT '0' NOT NULL;

CREATE TABLE canned_responses (
  response_id int(11) NOT NULL auto_increment,
  response_title varchar(25),
  response_text text,
  PRIMARY KEY (response_id)
);

CREATE TABLE supported_languages (
  language_id int(11) NOT NULL auto_increment,
  name text,
  filename text,
  classname text,
  language_code char(2),
  PRIMARY KEY (language_id),
  KEY idx_supported_languages_code (language_code)
);

CREATE TABLE unix_uids (
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY (id)
);

CREATE TABLE user_diary (
  id int(11) NOT NULL auto_increment,
  user_id int(11) DEFAULT '0' NOT NULL,
  date_posted int(11) DEFAULT '0' NOT NULL,
  summary text,
  details text,
  is_public int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY idx_user_diary_user_date (user_id,date_posted),
  KEY idx_user_diary_date (date_posted),
  KEY idx_user_diary_user (user_id)
);

CREATE TABLE user_diary_monitor (
  monitor_id int(11) NOT NULL auto_increment,
  monitored_user int(11) DEFAULT '0' NOT NULL,
  user_id int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (monitor_id),
  KEY idx_user_diary_monitor_user (user_id),
  KEY idx_user_diary_monitor_monitored_user (monitored_user)
);

CREATE TABLE user_metric (
  ranking int(11) NOT NULL auto_increment,
  user_id int(11) DEFAULT '0' NOT NULL,
  times_ranked int(11) DEFAULT '0' NOT NULL,
  avg_raters_importance float(10,8) DEFAULT '0.00000000' NOT NULL,
  avg_rating float(10,8) DEFAULT '0.00000000' NOT NULL,
  metric float(10,8) DEFAULT '0.00000000' NOT NULL,
  percentile float(10,8) DEFAULT '0.00000000' NOT NULL,
  importance_factor float(10,8) DEFAULT '0.00000000' NOT NULL,
  PRIMARY KEY (ranking)
);

CREATE TABLE user_metric0 (
  ranking int(11) NOT NULL auto_increment,
  user_id int(11) DEFAULT '0' NOT NULL,
  times_ranked int(11) DEFAULT '0' NOT NULL,
  avg_raters_importance float(10,8) DEFAULT '0.00000000' NOT NULL,
  avg_rating float(10,8) DEFAULT '0.00000000' NOT NULL,
  metric float(10,8) DEFAULT '0.00000000' NOT NULL,
  percentile float(10,8) DEFAULT '0.00000000' NOT NULL,
  importance_factor float(10,8) DEFAULT '0.00000000' NOT NULL,
  PRIMARY KEY (ranking),
  KEY idx_user_metric0_user_id (user_id)
);

CREATE TABLE user_ratings (
  rated_by int(11) DEFAULT '0' NOT NULL,
  user_id int(11) DEFAULT '0' NOT NULL,
  rate_field int(11) DEFAULT '0' NOT NULL,
  rating int(11) DEFAULT '0' NOT NULL,
  KEY idx_user_ratings_rated_by (rated_by),
  KEY idx_user_ratings_user_id (user_id)
);


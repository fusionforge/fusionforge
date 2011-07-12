CREATE INDEX artifactextrafldlmts_extrafieldid ON
artifact_extra_field_elements(extra_field_id);

CREATE INDEX artifactextrafielddata_artifactid ON
artifact_extra_field_data(artifact_id);

CREATE INDEX artifactextrafieldlist_groupartid ON
artifact_extra_field_list(group_artifact_id);

CREATE INDEX docdata_groupid ON doc_data (group_id,doc_group);

CREATE SEQUENCE artifact_extra_field_elements_element_id_seq;
ALTER TABLE artifact_extra_field_elements ALTER COLUMN
	element_id SET DEFAULT nextval('artifact_extra_field_elements_element_id_seq');
DROP SEQUENCE artifact_group_selection_box_options_id_seq;
SELECT setval('artifact_extra_field_elements_element_id_seq',(SELECT
max(element_id) FROM artifact_extra_field_elements));

CREATE SEQUENCE artifact_extra_field_data_data_id_seq;
ALTER TABLE artifact_extra_field_data ALTER COLUMN
	data_id SET DEFAULT nextval('artifact_extra_field_data_data_id_seq');
SELECT setval('artifact_extra_field_data_data_id_seq',(SELECT
max(data_id) FROM artifact_extra_field_data));
DROP SEQUENCE artifact_extra_field_data_id_seq;

CREATE SEQUENCE artifact_extra_field_list_extra_field_id_seq;
ALTER TABLE artifact_extra_field_list ALTER COLUMN
	extra_field_id SET DEFAULT nextval('artifact_extra_field_list_extra_field_id_seq');
SELECT setval('artifact_extra_field_list_extra_field_id_seq',(SELECT
max(extra_field_id) FROM artifact_extra_field_list));
DROP SEQUENCE artifact_group_selection_box_list_id_seq;


ALTER TABLE artifact_counts_agg ADD CONSTRAINT
	artifact_counts_agg_pkey primary key (group_artifact_id);
DROP INDEX artifactcountsagg_groupartid;


ALTER TABLE artifact_extra_field_elements DROP CONSTRAINT
	artifact_group_selection_box_options_pkey;
ALTER TABLE artifact_extra_field_elements ADD CONSTRAINT
	artifact_extra_field_elements_pkey primary key (element_id);


ALTER TABLE artifact_extra_field_list DROP CONSTRAINT
	artifact_group_selection_box_list_pkey;
ALTER TABLE artifact_extra_field_list ADD CONSTRAINT
	artifact_extra_field_list_pkey primary key (extra_field_id);

DROP INDEX artfile_artid;

DROP INDEX artgrouplist_groupid;

DROP INDEX arthistory_artid;

DROP INDEX artmessage_artid;

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE artifact_monitor DROP CONSTRAINT artifact_monitor_pkey;
DROP INDEX artmonitor_artifactid;
ALTER TABLE artifact_monitor ADD CONSTRAINT artifact_monitor_pkey PRIMARY KEY (artifact_id,user_id);
CREATE INDEX artmonitor_useridartid ON artifact_monitor(user_id,artifact_id);

DROP INDEX artperm_groupartifactid;

CREATE INDEX cronhist_jobrundate ON cron_history(job,rundate);

DROP INDEX doc_group_doc_group;

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE filemodule_monitor DROP CONSTRAINT filemodule_monitor_pkey;
DROP INDEX filemodule_monitor_id;
DROP INDEX filemodulemonitor_userid;
ALTER TABLE filemodule_monitor ADD CONSTRAINT filemodule_monitor_pkey PRIMARY KEY (filemodule_id,user_id);
CREATE INDEX filemodulemonitor_useridfilemoduleid ON filemodule_monitor (user_id,filemodule_id);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE forum_monitored_forums DROP CONSTRAINT forum_monitored_forums_pkey;
DROP INDEX forum_monitor_combo_id;
DROP INDEX forum_monitor_thread_id;
DROP INDEX forummonitoredforums_user;
ALTER TABLE forum_monitored_forums ADD CONSTRAINT forum_monitored_forums_pkey PRIMARY KEY (forum_id,user_id);
CREATE INDEX forummonitoredforums_useridforumid ON forum_monitored_forums(user_id,forum_id);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE forum_perm DROP CONSTRAINT forum_perm_id_key;
CREATE INDEX forumperm_useridgroupforumid ON forum_perm(user_id,group_forum_id);
ALTER TABLE forum_perm ADD CONSTRAINT forum_perm_pkey PRIMARY KEY (group_forum_id, user_id);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE forum_saved_place DROP CONSTRAINT forum_saved_place_pkey;
ALTER TABLE forum_saved_place ADD CONSTRAINT
	forum_saved_place_pkey PRIMARY KEY (user_id,forum_id);

DROP INDEX frsdlfiletotal_fileid;
ALTER TABLE frs_dlstats_filetotal_agg DROP CONSTRAINT frs_dlstats_filetotal_agg_pkey;
ALTER TABLE frs_dlstats_filetotal_agg ADD CONSTRAINT
	frs_dlstats_filetotal_agg_pkey PRIMARY KEY (file_id);

--
-- TODO investigate if group_plugin_id is needed at all
--
CREATE INDEX groupplugin_groupid ON group_plugin(group_id);

ALTER TABLE licenses DROP CONSTRAINT licenses_license_id_key CASCADE;
ALTER TABLE licenses ADD CONSTRAINT licenses_pkey PRIMARY KEY (license_id);
--groups fkey is dropped BY CASCADE
--"groups_license" FOREIGN KEY (license) REFERENCES licenses(license_id) MATCH FULL
ALTER TABLE groups ADD CONSTRAINT groups_license
        FOREIGN KEY (license) REFERENCES licenses(license_id) MATCH FULL;

CREATE INDEX prdbdbs_groupid ON prdb_dbs(group_id);
CREATE INDEX prdbstates_stateid ON prdb_states(stateid);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE project_assigned_to DROP CONSTRAINT project_assigned_to_pkey;
DROP INDEX project_assigned_to_assigned_to;
DROP INDEX project_assigned_to_task_id;
--mop up duplicate ids
DELETE FROM project_assigned_to WHERE EXISTS (
SELECT * FROM (SELECT project_task_id,assigned_to_id,count(*) AS count FROM project_assigned_to
	GROUP BY project_task_id,assigned_to_id ORDER BY count) ta WHERE ta.count > 1
	AND ta.project_task_id=project_assigned_to.project_task_id);
ALTER TABLE project_assigned_to ADD CONSTRAINT
	project_assigned_to_pkey PRIMARY KEY (project_task_id,assigned_to_id);
CREATE INDEX projectassigned_assignedtotaskid ON
	project_assigned_to(assigned_to_id,project_task_id);

ALTER TABLE project_counts_agg ADD CONSTRAINT
	project_counts_agg_pkey PRIMARY KEY (group_project_id);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE project_dependencies DROP CONSTRAINT project_dependencies_pkey;
DROP INDEX project_dependencies_task_id;
DROP INDEX project_is_dependent_on_task_id;
ALTER TABLE project_dependencies ALTER COLUMN link_type SET DEFAULT 'FS';
--mop up duplicate ids
DELETE FROM project_dependencies WHERE EXISTS (
SELECT * FROM (SELECT project_task_id,is_dependent_on_task_id,count(*) AS count
	FROM project_dependencies
	GROUP BY project_task_id,is_dependent_on_task_id ORDER BY count) ta WHERE ta.count > 1
	AND ta.project_task_id=project_dependencies.project_task_id
	AND ta.is_dependent_on_task_id=project_dependencies.is_dependent_on_task_id);
ALTER TABLE project_dependencies ADD CONSTRAINT project_dependencies_pkey
	PRIMARY KEY(project_task_id,is_dependent_on_task_id);
CREATE INDEX projectdep_isdepon_projtaskid ON
	project_dependencies(is_dependent_on_task_id,project_task_id);

DROP TABLE project_group_doccat;
DROP TABLE project_group_forum;

CREATE INDEX projectmsgs_projtaskidpostdate ON project_messages(project_task_id,postdate);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE project_perm DROP CONSTRAINT project_perm_id_key;
DROP INDEX projectperm_groupprojiduserid;
ALTER TABLE project_perm ADD CONSTRAINT project_perm_id_key PRIMARY KEY(group_project_id,user_id);
CREATE INDEX projectperm_useridgroupprojid ON project_perm(user_id,group_project_id);


DROP INDEX projectsumsagg_groupid;
--MAY HAVE TO RUN db_project_sums.php cronjob first
ALTER TABLE project_sums_agg ALTER type SET NOT NULL;
DELETE FROM project_sums_agg;
ALTER TABLE project_sums_agg ADD CONSTRAINT project_sums_agg_pkey PRIMARY KEY (group_id,type);

DROP INDEX project_task_group_project_id;

DROP INDEX projecttaskartifact_artifactid;
ALTER TABLE project_task_artifact ALTER project_task_id SET NOT NULL;
ALTER TABLE project_task_artifact ALTER artifact_id SET NOT NULL;
ALTER TABLE project_task_artifact ADD CONSTRAINT
	project_task_artifact_pkey PRIMARY KEY (project_task_id,artifact_id);
CREATE INDEX projecttaskartifact_artidprojtaskid ON
	project_task_artifact(artifact_id,project_task_id);

DROP INDEX projecttaskexternal_projtaskid;
ALTER TABLE project_task_external_order ADD CONSTRAINT
	roject_task_external_order_pkey PRIMARY KEY (project_task_id);

--UNKNOWN IF CORRECT: project_weekly_metric
--UNKNOWN IF CORRECT: prweb_vhost

ALTER TABLE role DROP CONSTRAINT role_role_id_key CASCADE;
--NOTICE:  drop cascades to constraint usergroup_roleid on table user_group
--NOTICE:  drop cascades to constraint $1 on table role_setting
ALTER TABLE role ADD CONSTRAINT role_role_id_pkey PRIMARY KEY (role_id);
ALTER TABLE user_group ADD CONSTRAINT usergroup_roleid
        FOREIGN KEY (role_id) REFERENCES role(role_id) MATCH FULL;
ALTER TABLE role_setting ADD CONSTRAINT rolesetting_roleroleid
	FOREIGN KEY (role_id) REFERENCES role(role_id) ON DELETE CASCADE;

DROP INDEX rolesetting_roleidsectionid;
ALTER TABLE role_setting ADD CONSTRAINT role_setting_pkey
	PRIMARY KEY (role_id,section_name,ref_id);

--skills_data ignored - to be dropped
--stats tables ignored - to be dropped

CREATE UNIQUE INDEX supportedlanguage_code ON supported_languages(language_code);
--TODO DROP supported_languages.filename

--NEED TO BE INVESTIGATED MORE THOROUGHLY
-- public | survey_questions              | table | tperdue
-- public | survey_rating_aggregate       | table | tperdue
-- public | survey_rating_response        | table | tperdue
-- public | survey_responses              | table | tperdue
DROP INDEX survey_responses_user_survey;

DROP INDEX troveagg_trovecatid;
--trove_agg - what is this?
DROP INDEX parent_idx;
CREATE INDEX trovecat_parentid ON trove_cat(parent);
DROP INDEX version_idx;
DROP INDEX root_parent_idx;

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE trove_group_link DROP CONSTRAINT trove_group_link_pkey;
DROP INDEX trove_group_link_cat_id;
DROP INDEX trove_group_link_group_id;
CREATE INDEX trovegrouplink_groupidcatid ON trove_group_link(group_id,trove_cat_id);
ALTER TABLE trove_group_link ADD CONSTRAINT
	trove_group_link_pkey PRIMARY KEY(trove_cat_id,group_id,trove_cat_version);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE trove_treesums DROP CONSTRAINT trove_treesums_pkey;
ALTER TABLE trove_treesums ADD CONSTRAINT trove_treesums_pkey PRIMARY KEY (trove_cat_id);

DROP INDEX user_diary_user;
DROP INDEX user_diary_date;

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE user_diary_monitor DROP CONSTRAINT user_diary_monitor_pkey;
DROP INDEX user_diary_monitor_monitored_us;
DROP INDEX user_diary_monitor_user;
ALTER TABLE user_diary_monitor ADD CONSTRAINT
	user_diary_monitor_pkey PRIMARY KEY (monitored_user,user_id);
CREATE INDEX userdiarymon_useridmonitoredid ON
	user_diary_monitor(user_id,monitored_user);

--
-- TODO DROP unnecessary sequence/id
--
DROP INDEX admin_flags_idx;
DROP INDEX forum_flags_idx;
DROP INDEX project_flags_idx;
DROP INDEX user_group_group_id;
DROP INDEX user_group_user_id;
ALTER TABLE user_group DROP CONSTRAINT user_group_pkey;
ALTER TABLE user_group ADD CONSTRAINT user_group_pkey PRIMARY KEY (group_id,user_id);
CREATE INDEX usergroup_useridgroupid ON user_group(user_id,group_id);
DROP INDEX usergroup_uniq_groupid_userid;

CREATE UNIQUE INDEX usermetric_userid ON user_metric(user_id);

CREATE INDEX usermetrichistory_useridmonthday ON user_metric_history(user_id,month,day);
DROP INDEX user_metric_history_date_userid;
ALTER TABLE user_metric_history ADD CONSTRAINT
	user_metric_history_pkey PRIMARY KEY (month,day,user_id);

--
-- TODO DROP unnecessary sequence/id
--
ALTER TABLE user_plugin DROP CONSTRAINT user_plugin_pkey;
ALTER TABLE user_plugin ALTER user_id SET NOT NULL;
ALTER TABLE user_plugin ALTER plugin_id SET NOT NULL;
ALTER TABLE user_plugin ADD CONSTRAINT user_plugin_pkey PRIMARY KEY (user_id,plugin_id);


DROP INDEX user_pref_user_id;
ALTER TABLE user_preferences ALTER user_id SET NOT NULL;
ALTER TABLE user_preferences ALTER preference_name SET NOT NULL;
ALTER TABLE user_preferences ADD CONSTRAINT
	user_preferences_pkey PRIMARY KEY (user_id,preference_name);

DROP INDEX user_ratings_rated_by;
ALTER TABLE user_ratings ADD CONSTRAINT user_ratings_pkey PRIMARY KEY (rated_by,user_id,rate_field);

DROP INDEX users_user_pw;


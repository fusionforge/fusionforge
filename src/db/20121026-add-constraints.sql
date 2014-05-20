ALTER TABLE users_idx ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_session ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_ratings ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_ratings ADD FOREIGN KEY (rated_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_preferences ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_metric_history ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_diary ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE user_bookmarks ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE survey_responses ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE survey_rating_response ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_cvs_user WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE stats_cvs_user ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE snippet_version ADD FOREIGN KEY (submitted_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE snippet_package_version ADD FOREIGN KEY (submitted_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE snippet_package ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE snippet ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE rep_user_act_weekly ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE rep_user_act_monthly ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE rep_user_act_daily ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE rep_time_tracking ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_assigned_to ADD FOREIGN KEY (assigned_to_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_skill_inventory ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE news_bytes ADD FOREIGN KEY (submitted_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE forum_saved_place ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM filemodule_monitor WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE filemodule_monitor ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE doc_data ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE doc_data ADD FOREIGN KEY (reserved_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE doc_data ADD FOREIGN KEY (locked_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE forum_pending_messages ADD FOREIGN KEY (posted_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM frs_dlstats_file WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE frs_dlstats_file ADD FOREIGN KEY (user_id) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE skills_data_idx ADD FOREIGN KEY (skills_data_id) REFERENCES skills_data ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_task_idx ADD FOREIGN KEY (project_task_id) REFERENCES project_task ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE news_bytes_idx ADD FOREIGN KEY (id) REFERENCES news_bytes ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE groups_idx ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE frs_release_idx ADD FOREIGN KEY (release_id) REFERENCES frs_release ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE frs_file_idx ADD FOREIGN KEY (file_id) REFERENCES frs_file ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE forum_idx ADD FOREIGN KEY (msg_id) REFERENCES forum ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE doc_data_idx ADD FOREIGN KEY (docid) REFERENCES doc_data ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_idx ADD FOREIGN KEY (artifact_id) REFERENCES artifact ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE surveys ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE survey_questions ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_subd_pages WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE stats_subd_pages ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_project_months WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE stats_project_months ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_project_metric WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE stats_project_metric ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_project WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE stats_project ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE stats_cvs_group ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE roadmap ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM rep_group_act_weekly WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE rep_group_act_weekly ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM rep_group_act_monthly WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE rep_group_act_monthly ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE rep_group_act_daily ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM prweb_vhost WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE prweb_vhost ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_weekly_metric ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_sums_agg ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_metric ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM group_history WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE group_history ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_cvs_history ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE db_images ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE doc_data_idx ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE news_bytes ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM stats_project_developers WHERE group_id NOT IN (SELECT group_id FROM groups);
ALTER TABLE stats_project_developers ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE prdb_dbs ADD FOREIGN KEY (group_id) REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE survey_questions ADD FOREIGN KEY (question_type) REFERENCES survey_question_types ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_history ADD FOREIGN KEY (project_task_id) REFERENCES project_task ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM project_dependencies WHERE is_dependent_on_task_id NOT IN (SELECT project_task_id FROM project_task);
ALTER TABLE project_dependencies ADD FOREIGN KEY (project_task_id) REFERENCES project_task ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_dependencies ADD FOREIGN KEY (is_dependent_on_task_id) REFERENCES project_task ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE project_counts_agg ADD FOREIGN KEY (group_project_id) REFERENCES project_group_list ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE prdb_states ADD PRIMARY KEY (stateid);
ALTER TABLE prdb_dbs ADD FOREIGN KEY (dbtype) REFERENCES prdb_types ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE prdb_dbs ADD FOREIGN KEY (state) REFERENCES prdb_states ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE people_job_inventory ADD FOREIGN KEY (job_id) REFERENCES people_job ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job_inventory ADD FOREIGN KEY (skill_id) REFERENCES people_skill ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job_inventory ADD FOREIGN KEY (skill_level_id) REFERENCES people_skill_level ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job_inventory ADD FOREIGN KEY (skill_year_id) REFERENCES people_skill_year ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job ADD FOREIGN KEY (status_id) REFERENCES people_job_status ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE people_job ADD FOREIGN KEY (category_id) REFERENCES people_job_category ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE frs_dlstats_filetotal_agg ADD FOREIGN KEY (file_id) REFERENCES frs_file ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE forum_agg_msg_count ADD FOREIGN KEY (group_forum_id) REFERENCES forum_group_list ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE artifact_canned_responses ADD FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_counts_agg ADD FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_extra_field_elements ADD FOREIGN KEY (extra_field_id) REFERENCES artifact_extra_field_list ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_extra_field_data ADD FOREIGN KEY (extra_field_id) REFERENCES artifact_extra_field_list ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_extra_field_data ADD FOREIGN KEY (artifact_id) REFERENCES artifact ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE artifact_extra_field_list ADD FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list ON DELETE CASCADE ON UPDATE CASCADE;


CREATE FUNCTION "plpgsql_call_handler" () RETURNS opaque AS '$libdir/plpgsql', 'plpgsql_call_handler' LANGUAGE 'C';


CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER "plpgsql_call_handler" LANCOMPILER '';


CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "canned_responses" (
	"response_id" integer DEFAULT nextval('canned_responses_pk_seq'::text) NOT NULL,
	"response_title" character varying(25),
	"response_text" text,
	Constraint "canned_responses_pkey" Primary Key ("response_id")
);


CREATE SEQUENCE "db_images_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "db_images" (
	"id" integer DEFAULT nextval('db_images_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"description" text DEFAULT '' NOT NULL,
	"bin_data" text DEFAULT '' NOT NULL,
	"filename" text DEFAULT '' NOT NULL,
	"filesize" integer DEFAULT '0' NOT NULL,
	"filetype" text DEFAULT '' NOT NULL,
	"width" integer DEFAULT '0' NOT NULL,
	"height" integer DEFAULT '0' NOT NULL,
	"upload_date" integer,
	"version" integer,
	Constraint "db_images_pkey" Primary Key ("id")
);


CREATE SEQUENCE "doc_data_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "doc_data" (
	"docid" integer DEFAULT nextval('doc_data_pk_seq'::text) NOT NULL,
	"stateid" integer DEFAULT '0' NOT NULL,
	"title" character varying(255) DEFAULT '' NOT NULL,
	"data" text DEFAULT '' NOT NULL,
	"updatedate" integer DEFAULT '0' NOT NULL,
	"createdate" integer DEFAULT '0' NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"doc_group" integer DEFAULT '0' NOT NULL,
	"description" text,
	"language_id" integer DEFAULT '1' NOT NULL,
	"filename" text,
	"filetype" text,
	"group_id" integer,
	Constraint "doc_data_pkey" Primary Key ("docid")
);


CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "doc_groups" (
	"doc_group" integer DEFAULT nextval('doc_groups_pk_seq'::text) NOT NULL,
	"groupname" character varying(255) DEFAULT '' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "doc_groups_pkey" Primary Key ("doc_group")
);


CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "doc_states" (
	"stateid" integer DEFAULT nextval('doc_states_pk_seq'::text) NOT NULL,
	"name" character varying(255) DEFAULT '' NOT NULL,
	Constraint "doc_states_pkey" Primary Key ("stateid")
);


CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "filemodule_monitor" (
	"id" integer DEFAULT nextval('filemodule_monitor_pk_seq'::text) NOT NULL,
	"filemodule_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "filemodule_monitor_pkey" Primary Key ("id")
);


CREATE SEQUENCE "forum_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "forum" (
	"msg_id" integer DEFAULT nextval('forum_pk_seq'::text) NOT NULL,
	"group_forum_id" integer DEFAULT '0' NOT NULL,
	"posted_by" integer DEFAULT '0' NOT NULL,
	"subject" text DEFAULT '' NOT NULL,
	"body" text DEFAULT '' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	"is_followup_to" integer DEFAULT '0' NOT NULL,
	"thread_id" integer DEFAULT '0' NOT NULL,
	"has_followups" integer DEFAULT '0',
	"most_recent_date" integer DEFAULT '0' NOT NULL,
	Constraint "forum_pkey" Primary Key ("msg_id")
);


CREATE TABLE "forum_agg_msg_count" (
	"group_forum_id" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL,
	Constraint "forum_agg_msg_count_pkey" Primary Key ("group_forum_id")
);


CREATE SEQUENCE "forum_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "forum_group_list" (
	"group_forum_id" integer DEFAULT nextval('forum_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"forum_name" text DEFAULT '' NOT NULL,
	"is_public" integer DEFAULT '0' NOT NULL,
	"description" text,
	"allow_anonymous" integer DEFAULT '0' NOT NULL,
	"send_all_posts_to" text,
	Constraint "forum_group_list_pkey" Primary Key ("group_forum_id")
);


CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "forum_monitored_forums" (
	"monitor_id" integer DEFAULT nextval('forum_monitored_forums_pk_seq'::text) NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "forum_monitored_forums_pkey" Primary Key ("monitor_id")
);


CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "forum_saved_place" (
	"saved_place_id" integer DEFAULT nextval('forum_saved_place_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"save_date" integer DEFAULT '0' NOT NULL,
	Constraint "forum_saved_place_pkey" Primary Key ("saved_place_id")
);


CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE SEQUENCE "frs_file_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_file" (
	"file_id" integer DEFAULT nextval('frs_file_pk_seq'::text) NOT NULL,
	"filename" text,
	"release_id" integer DEFAULT '0' NOT NULL,
	"type_id" integer DEFAULT '0' NOT NULL,
	"processor_id" integer DEFAULT '0' NOT NULL,
	"release_time" integer DEFAULT '0' NOT NULL,
	"file_size" integer DEFAULT '0' NOT NULL,
	"post_date" integer DEFAULT '0' NOT NULL,
	Constraint "frs_file_pkey" Primary Key ("file_id")
);


CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_filetype" (
	"type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_filetype_pkey" Primary Key ("type_id")
);


CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_package" (
	"package_id" integer DEFAULT nextval('frs_package_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"name" text,
	"status_id" integer DEFAULT '0' NOT NULL,
	Constraint "frs_package_pkey" Primary Key ("package_id")
);


CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_processor" (
	"processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_processor_pkey" Primary Key ("processor_id")
);


CREATE SEQUENCE "frs_release_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_release" (
	"release_id" integer DEFAULT nextval('frs_release_pk_seq'::text) NOT NULL,
	"package_id" integer DEFAULT '0' NOT NULL,
	"name" text,
	"notes" text,
	"changes" text,
	"status_id" integer DEFAULT '0' NOT NULL,
	"preformatted" integer DEFAULT '0' NOT NULL,
	"release_date" integer DEFAULT '0' NOT NULL,
	"released_by" integer DEFAULT '0' NOT NULL,
	Constraint "frs_release_pkey" Primary Key ("release_id")
);


CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "frs_status" (
	"status_id" integer DEFAULT nextval('frs_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "group_history" (
	"group_history_id" integer DEFAULT nextval('group_history_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	Constraint "group_history_pkey" Primary Key ("group_history_id")
);


CREATE SEQUENCE "groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "groups" (
	"group_id" integer DEFAULT nextval('groups_pk_seq'::text) NOT NULL,
	"group_name" character varying(40),
	"homepage" character varying(128),
	"is_public" integer DEFAULT '0' NOT NULL,
	"status" character(1) DEFAULT 'A' NOT NULL,
	"unix_group_name" character varying(30) DEFAULT '' NOT NULL,
	"unix_box" character varying(20) DEFAULT 'shell1' NOT NULL,
	"http_domain" character varying(80),
	"short_description" character varying(255),
	"cvs_box" character varying(20) DEFAULT 'cvs1' NOT NULL,
	"license" character varying(16),
	"register_purpose" text,
	"license_other" text,
	"register_time" integer DEFAULT '0' NOT NULL,
	"dead1" integer DEFAULT '1' NOT NULL,
	"rand_hash" text,
	"use_mail" integer DEFAULT '1' NOT NULL,
	"use_survey" integer DEFAULT '1' NOT NULL,
	"dead2" integer DEFAULT '1' NOT NULL,
	"use_forum" integer DEFAULT '1' NOT NULL,
	"use_pm" integer DEFAULT '1' NOT NULL,
	"use_cvs" integer DEFAULT '1' NOT NULL,
	"use_news" integer DEFAULT '1' NOT NULL,
	"dead3" integer DEFAULT '1' NOT NULL,
	"dead4" text DEFAULT '' NOT NULL,
	"dead5" text DEFAULT '' NOT NULL,
	"dead6" text DEFAULT '' NOT NULL,
	"type" integer DEFAULT '1' NOT NULL,
	"use_docman" integer DEFAULT '1' NOT NULL,
	"dead7" integer DEFAULT '0' NOT NULL,
	"dead8" integer DEFAULT '0' NOT NULL,
	"dead9" integer DEFAULT '0' NOT NULL,
	"new_doc_address" text DEFAULT '' NOT NULL,
	"send_all_docs" integer DEFAULT '0' NOT NULL,
	"dead10" integer DEFAULT '1' NOT NULL,
	"use_pm_depend_box" integer DEFAULT '1' NOT NULL,
	"dead11" integer,
	"dead12" integer,
	"dead13" integer,
	"use_ftp" integer DEFAULT 1,
	"use_tracker" integer DEFAULT 1,
	"use_frs" integer DEFAULT 1,
	"use_stats" integer DEFAULT 1,
	"enable_pserver" integer DEFAULT 1,
	"enable_anoncvs" integer DEFAULT 1,
	Constraint "groups_pkey" Primary Key ("group_id")
);


CREATE SEQUENCE "mail_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "mail_group_list" (
	"group_list_id" integer DEFAULT nextval('mail_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"list_name" text,
	"is_public" integer DEFAULT '0' NOT NULL,
	"password" character varying(16),
	"list_admin" integer DEFAULT '0' NOT NULL,
	"status" integer DEFAULT '0' NOT NULL,
	"description" text,
	Constraint "mail_group_list_pkey" Primary Key ("group_list_id")
);


CREATE SEQUENCE "news_bytes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "news_bytes" (
	"id" integer DEFAULT nextval('news_bytes_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"is_approved" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	Constraint "news_bytes_pkey" Primary Key ("id")
);


CREATE SEQUENCE "people_job_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_job" (
	"job_id" integer DEFAULT nextval('people_job_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"title" text,
	"description" text,
	"date" integer DEFAULT '0' NOT NULL,
	"status_id" integer DEFAULT '0' NOT NULL,
	"category_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_pkey" Primary Key ("job_id")
);


CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_job_category" (
	"category_id" integer DEFAULT nextval('people_job_category_pk_seq'::text) NOT NULL,
	"name" text,
	"private_flag" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_category_pkey" Primary Key ("category_id")
);


CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_job_inventory" (
	"job_inventory_id" integer DEFAULT nextval('people_job_inventory_pk_seq'::text) NOT NULL,
	"job_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_inventory_pkey" Primary Key ("job_inventory_id")
);


CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_job_status" (
	"status_id" integer DEFAULT nextval('people_job_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_job_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_skill" (
	"skill_id" integer DEFAULT nextval('people_skill_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_pkey" Primary Key ("skill_id")
);


CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_skill_inventory" (
	"skill_inventory_id" integer DEFAULT nextval('people_skill_inventory_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_skill_inventory_pkey" Primary Key ("skill_inventory_id")
);


CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_skill_level" (
	"skill_level_id" integer DEFAULT nextval('people_skill_level_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_level_pkey" Primary Key ("skill_level_id")
);


CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "people_skill_year" (
	"skill_year_id" integer DEFAULT nextval('people_skill_year_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_year_pkey" Primary Key ("skill_year_id")
);


CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_assigned_to" (
	"project_assigned_id" integer DEFAULT nextval('project_assigned_to_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"assigned_to_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_assigned_to_pkey" Primary Key ("project_assigned_id")
);


CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_dependencies" (
	"project_depend_id" integer DEFAULT nextval('project_dependencies_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_task_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_dependencies_pkey" Primary Key ("project_depend_id")
);


CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_group_list" (
	"group_project_id" integer DEFAULT nextval('project_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"project_name" text DEFAULT '' NOT NULL,
	"is_public" integer DEFAULT '0' NOT NULL,
	"description" text,
	"send_all_posts_to" text,
	Constraint "project_group_list_pkey" Primary Key ("group_project_id")
);


CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_history" (
	"project_history_id" integer DEFAULT nextval('project_history_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"mod_date" integer DEFAULT '0' NOT NULL,
	Constraint "project_history_pkey" Primary Key ("project_history_id")
);


CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_metric" (
	"ranking" integer DEFAULT nextval('project_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_metric_pkey" Primary Key ("ranking")
);


CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_metric_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	Constraint "project_metric_tmp1_pkey" Primary Key ("ranking")
);


CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_status" (
	"status_id" integer DEFAULT nextval('project_status_pk_seq'::text) NOT NULL,
	"status_name" text DEFAULT '' NOT NULL,
	Constraint "project_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "project_task_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_task" (
	"project_task_id" integer DEFAULT nextval('project_task_pk_seq'::text) NOT NULL,
	"group_project_id" integer DEFAULT '0' NOT NULL,
	"summary" text DEFAULT '' NOT NULL,
	"details" text DEFAULT '' NOT NULL,
	"percent_complete" integer DEFAULT '0' NOT NULL,
	"priority" integer DEFAULT '0' NOT NULL,
	"hours" double precision DEFAULT '0.00' NOT NULL,
	"start_date" integer DEFAULT '0' NOT NULL,
	"end_date" integer DEFAULT '0' NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"status_id" integer DEFAULT '0' NOT NULL,
	"category_id" integer,
	Constraint "project_task_pkey" Primary Key ("project_task_id")
);


CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_weekly_metric" (
	"ranking" integer DEFAULT nextval('project_weekly_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL
);


CREATE TABLE "session" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"session_hash" character(32) DEFAULT '' NOT NULL,
	"ip_addr" character(15) DEFAULT '' NOT NULL,
	"time" integer DEFAULT '0' NOT NULL,
	Constraint "session_pkey" Primary Key ("session_hash")
);


CREATE SEQUENCE "snippet_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "snippet" (
	"snippet_id" integer DEFAULT nextval('snippet_pk_seq'::text) NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"name" text,
	"description" text,
	"type" integer DEFAULT '0' NOT NULL,
	"language" integer DEFAULT '0' NOT NULL,
	"license" text DEFAULT '' NOT NULL,
	"category" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_pkey" Primary Key ("snippet_id")
);


CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "snippet_package" (
	"snippet_package_id" integer DEFAULT nextval('snippet_package_pk_seq'::text) NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"name" text,
	"description" text,
	"category" integer DEFAULT '0' NOT NULL,
	"language" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_pkey" Primary Key ("snippet_package_id")
);


CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "snippet_package_item" (
	"snippet_package_item_id" integer DEFAULT nextval('snippet_package_item_pk_seq'::text) NOT NULL,
	"snippet_package_version_id" integer DEFAULT '0' NOT NULL,
	"snippet_version_id" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_item_pkey" Primary Key ("snippet_package_item_id")
);


CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "snippet_package_version" (
	"snippet_package_version_id" integer DEFAULT nextval('snippet_package_version_pk_seq'::text) NOT NULL,
	"snippet_package_id" integer DEFAULT '0' NOT NULL,
	"changes" text,
	"version" text,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_version_pkey" Primary Key ("snippet_package_version_id")
);


CREATE SEQUENCE "snippet_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "snippet_version" (
	"snippet_version_id" integer DEFAULT nextval('snippet_version_pk_seq'::text) NOT NULL,
	"snippet_id" integer DEFAULT '0' NOT NULL,
	"changes" text,
	"version" text,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	"code" text,
	Constraint "snippet_version_pkey" Primary Key ("snippet_version_id")
);


CREATE TABLE "stats_agg_logo_by_day" (
	"day" integer,
	"count" integer
);


CREATE TABLE "stats_agg_pages_by_day" (
	"day" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);


CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "survey_question_types" (
	"id" integer DEFAULT nextval('survey_question_types_pk_seq'::text) NOT NULL,
	"type" text DEFAULT '' NOT NULL,
	Constraint "survey_question_types_pkey" Primary Key ("id")
);


CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "survey_questions" (
	"question_id" integer DEFAULT nextval('survey_questions_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"question" text DEFAULT '' NOT NULL,
	"question_type" integer DEFAULT '0' NOT NULL,
	Constraint "survey_questions_pkey" Primary Key ("question_id")
);


CREATE TABLE "survey_rating_aggregate" (
	"type" integer DEFAULT '0' NOT NULL,
	"id" integer DEFAULT '0' NOT NULL,
	"response" double precision DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);


CREATE TABLE "survey_rating_response" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"type" integer DEFAULT '0' NOT NULL,
	"id" integer DEFAULT '0' NOT NULL,
	"response" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL
);


CREATE TABLE "survey_responses" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"survey_id" integer DEFAULT '0' NOT NULL,
	"question_id" integer DEFAULT '0' NOT NULL,
	"response" text DEFAULT '' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL
);


CREATE SEQUENCE "surveys_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "surveys" (
	"survey_id" integer DEFAULT nextval('surveys_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"survey_title" text DEFAULT '' NOT NULL,
	"survey_questions" text DEFAULT '' NOT NULL,
	"is_active" integer DEFAULT '1' NOT NULL,
	Constraint "surveys_pkey" Primary Key ("survey_id")
);


CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE SEQUENCE "trove_cat_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "trove_cat" (
	"trove_cat_id" integer DEFAULT nextval('trove_cat_pk_seq'::text) NOT NULL,
	"version" integer DEFAULT '0' NOT NULL,
	"parent" integer DEFAULT '0' NOT NULL,
	"root_parent" integer DEFAULT '0' NOT NULL,
	"shortname" character varying(80),
	"fullname" character varying(80),
	"description" character varying(255),
	"count_subcat" integer DEFAULT '0' NOT NULL,
	"count_subproj" integer DEFAULT '0' NOT NULL,
	"fullpath" text DEFAULT '' NOT NULL,
	"fullpath_ids" text,
	Constraint "trove_cat_pkey" Primary Key ("trove_cat_id")
);


CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "trove_group_link" (
	"trove_group_id" integer DEFAULT nextval('trove_group_link_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_version" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_root" integer DEFAULT '0' NOT NULL,
	Constraint "trove_group_link_pkey" Primary Key ("trove_group_id")
);


CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_bookmarks" (
	"bookmark_id" integer DEFAULT nextval('user_bookmarks_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"bookmark_url" text,
	"bookmark_title" text,
	Constraint "user_bookmarks_pkey" Primary Key ("bookmark_id")
);


CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_diary" (
	"id" integer DEFAULT nextval('user_diary_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"date_posted" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	"is_public" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_pkey" Primary Key ("id")
);


CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_diary_monitor" (
	"monitor_id" integer DEFAULT nextval('user_diary_monitor_pk_seq'::text) NOT NULL,
	"monitored_user" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_monitor_pkey" Primary Key ("monitor_id")
);


CREATE SEQUENCE "user_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_group" (
	"user_group_id" integer DEFAULT nextval('user_group_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"admin_flags" character(16) DEFAULT '' NOT NULL,
	"dead1" integer DEFAULT '0' NOT NULL,
	"forum_flags" integer DEFAULT '0' NOT NULL,
	"project_flags" integer DEFAULT '2' NOT NULL,
	"dead2" integer DEFAULT '1' NOT NULL,
	"dead3" integer DEFAULT '1' NOT NULL,
	"doc_flags" integer DEFAULT '0' NOT NULL,
	"cvs_flags" integer DEFAULT '1' NOT NULL,
	"member_role" integer DEFAULT '100' NOT NULL,
	"release_flags" integer DEFAULT '0' NOT NULL,
	"artifact_flags" integer,
	Constraint "user_group_pkey" Primary Key ("user_group_id")
);


CREATE SEQUENCE "user_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_metric" (
	"ranking" integer DEFAULT nextval('user_metric_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"times_ranked" integer DEFAULT '0' NOT NULL,
	"avg_raters_importance" double precision DEFAULT '0.00000000' NOT NULL,
	"avg_rating" double precision DEFAULT '0.00000000' NOT NULL,
	"metric" double precision DEFAULT '0.00000000' NOT NULL,
	"percentile" double precision DEFAULT '0.00000000' NOT NULL,
	"importance_factor" double precision DEFAULT '0.00000000' NOT NULL,
	Constraint "user_metric_pkey" Primary Key ("ranking")
);


CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "user_metric0" (
	"ranking" integer DEFAULT nextval('user_metric0_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"times_ranked" integer DEFAULT '0' NOT NULL,
	"avg_raters_importance" double precision DEFAULT '0.00000000' NOT NULL,
	"avg_rating" double precision DEFAULT '0.00000000' NOT NULL,
	"metric" double precision DEFAULT '0.00000000' NOT NULL,
	"percentile" double precision DEFAULT '0.00000000' NOT NULL,
	"importance_factor" double precision DEFAULT '0.00000000' NOT NULL,
	Constraint "user_metric0_pkey" Primary Key ("ranking")
);


CREATE TABLE "user_preferences" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"preference_name" character varying(20),
	"dead1" character varying(20),
	"set_date" integer DEFAULT '0' NOT NULL,
	"preference_value" text
);


CREATE TABLE "user_ratings" (
	"rated_by" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"rate_field" integer DEFAULT '0' NOT NULL,
	"rating" integer DEFAULT '0' NOT NULL
);


CREATE SEQUENCE "users_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "users" (
	"user_id" integer DEFAULT nextval('users_pk_seq'::text) NOT NULL,
	"user_name" text DEFAULT '' NOT NULL,
	"email" text DEFAULT '' NOT NULL,
	"user_pw" character varying(32) DEFAULT '' NOT NULL,
	"realname" character varying(32) DEFAULT '' NOT NULL,
	"status" character(1) DEFAULT 'A' NOT NULL,
	"shell" character varying(20) DEFAULT '/bin/bash' NOT NULL,
	"unix_pw" character varying(40) DEFAULT '' NOT NULL,
	"unix_status" character(1) DEFAULT 'N' NOT NULL,
	"unix_uid" integer DEFAULT '0' NOT NULL,
	"unix_box" character varying(10) DEFAULT 'shell1' NOT NULL,
	"add_date" integer DEFAULT '0' NOT NULL,
	"confirm_hash" character varying(32),
	"mail_siteupdates" integer DEFAULT '0' NOT NULL,
	"mail_va" integer DEFAULT '0' NOT NULL,
	"authorized_keys" text,
	"email_new" text,
	"people_view_skills" integer DEFAULT '0' NOT NULL,
	"people_resume" text DEFAULT '' NOT NULL,
	"timezone" character varying(64) DEFAULT 'GMT',
	"language" integer DEFAULT '1' NOT NULL,
	"block_ratings" integer DEFAULT 0,
	"jabber_address" text,
	"jabber_only" integer,
	Constraint "users_pkey" Primary Key ("user_id")
);


CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "project_sums_agg" (
	"group_id" integer DEFAULT 0 NOT NULL,
	"type" character(4),
	"count" integer DEFAULT 0 NOT NULL
);


CREATE SEQUENCE "project_metric_wee_ranking1_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "prdb_dbs" (
	"dbid" integer DEFAULT nextval('"prdb_dbs_dbid_seq"'::text) NOT NULL,
	"group_id" integer NOT NULL,
	"dbname" text NOT NULL,
	"dbusername" text NOT NULL,
	"dbuserpass" text NOT NULL,
	"requestdate" integer NOT NULL,
	"dbtype" integer NOT NULL,
	"created_by" integer NOT NULL,
	"state" integer NOT NULL,
	Constraint "prdb_dbs_pkey" Primary Key ("dbid")
);


CREATE TABLE "prdb_states" (
	"stateid" integer NOT NULL,
	"statename" text
);


CREATE TABLE "prdb_types" (
	"dbtypeid" integer NOT NULL,
	"dbservername" text NOT NULL,
	"dbsoftware" text NOT NULL,
	Constraint "prdb_types_pkey" Primary Key ("dbtypeid")
);


CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "prweb_vhost" (
	"vhostid" integer DEFAULT nextval('"prweb_vhost_vhostid_seq"'::text) NOT NULL,
	"vhost_name" text,
	"docdir" text,
	"cgidir" text,
	"group_id" integer NOT NULL,
	Constraint "prweb_vhost_pkey" Primary Key ("vhostid")
);


CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_group_list" (
	"group_artifact_id" integer DEFAULT nextval('"artifact_grou_group_artifac_seq"'::text) NOT NULL,
	"group_id" integer NOT NULL,
	"name" text,
	"description" text,
	"is_public" integer DEFAULT 0 NOT NULL,
	"allow_anon" integer DEFAULT 0 NOT NULL,
	"email_all_updates" integer DEFAULT 0 NOT NULL,
	"email_address" text NOT NULL,
	"due_period" integer DEFAULT 2592000 NOT NULL,
	"use_resolution" integer DEFAULT 0 NOT NULL,
	"submit_instructions" text,
	"browse_instructions" text,
	"datatype" integer DEFAULT 0 NOT NULL,
	"status_timeout" integer,
	Constraint "artifact_group_list_pkey" Primary Key ("group_artifact_id")
);


CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_resolution" (
	"id" integer DEFAULT nextval('"artifact_resolution_id_seq"'::text) NOT NULL,
	"resolution_name" text,
	Constraint "artifact_resolution_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_perm" (
	"id" integer DEFAULT nextval('"artifact_perm_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"perm_level" integer DEFAULT 0 NOT NULL,
	Constraint "artifact_perm_pkey" Primary Key ("id")
);


CREATE VIEW "artifactperm_user_vw" as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id);


CREATE VIEW "artifactperm_artgrouplist_vw" as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id);


CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_category" (
	"id" integer DEFAULT nextval('"artifact_category_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"category_name" text NOT NULL,
	"auto_assign_to" integer DEFAULT 100 NOT NULL,
	Constraint "artifact_category_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_group" (
	"id" integer DEFAULT nextval('"artifact_group_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"group_name" text NOT NULL,
	Constraint "artifact_group_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_status" (
	"id" integer DEFAULT nextval('"artifact_status_id_seq"'::text) NOT NULL,
	"status_name" text NOT NULL,
	Constraint "artifact_status_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact" (
	"artifact_id" integer DEFAULT nextval('"artifact_artifact_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"status_id" integer DEFAULT '1' NOT NULL,
	"category_id" integer DEFAULT '100' NOT NULL,
	"artifact_group_id" integer DEFAULT '0' NOT NULL,
	"resolution_id" integer DEFAULT '100' NOT NULL,
	"priority" integer DEFAULT '5' NOT NULL,
	"submitted_by" integer DEFAULT '100' NOT NULL,
	"assigned_to" integer DEFAULT '100' NOT NULL,
	"open_date" integer DEFAULT '0' NOT NULL,
	"close_date" integer DEFAULT '0' NOT NULL,
	"summary" text NOT NULL,
	"details" text NOT NULL,
	Constraint "artifact_pkey" Primary Key ("artifact_id")
);


CREATE VIEW "artifact_vw" as SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.category_id, artifact.artifact_group_id, artifact.resolution_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact_category.category_name, artifact_group.group_name, artifact_resolution.resolution_name FROM users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution WHERE ((((((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id)) AND (artifact.category_id = artifact_category.id)) AND (artifact.artifact_group_id = artifact_group.id)) AND (artifact.resolution_id = artifact_resolution.id));


CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_history" (
	"id" integer DEFAULT nextval('"artifact_history_id_seq"'::text) NOT NULL,
	"artifact_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"entrydate" integer DEFAULT '0' NOT NULL,
	Constraint "artifact_history_pkey" Primary Key ("id")
);


CREATE VIEW "artifact_history_user_vw" as SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name FROM artifact_history ah, users WHERE (ah.mod_by = users.user_id);


CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_file" (
	"id" integer DEFAULT nextval('"artifact_file_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"description" text NOT NULL,
	"bin_data" text NOT NULL,
	"filename" text NOT NULL,
	"filesize" integer NOT NULL,
	"filetype" text NOT NULL,
	"adddate" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer NOT NULL,
	Constraint "artifact_file_pkey" Primary Key ("id")
);


CREATE VIEW "artifact_file_user_vw" as SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname FROM artifact_file af, users WHERE (af.submitted_by = users.user_id);


CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_message" (
	"id" integer DEFAULT nextval('"artifact_message_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"submitted_by" integer NOT NULL,
	"from_email" text NOT NULL,
	"adddate" integer DEFAULT '0' NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_message_pkey" Primary Key ("id")
);


CREATE VIEW "artifact_message_user_vw" as SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname FROM artifact_message am, users WHERE (am.submitted_by = users.user_id);


CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_monitor" (
	"id" integer DEFAULT nextval('"artifact_monitor_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"email" text,
	Constraint "artifact_monitor_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "artifact_canned_responses" (
	"id" integer DEFAULT nextval('"artifact_canned_response_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"title" text NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_canned_responses_pkey" Primary Key ("id")
);


CREATE TABLE "artifact_counts_agg" (
	"group_artifact_id" integer NOT NULL,
	"count" integer NOT NULL,
	"open_count" integer
);


CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);


CREATE FUNCTION "forumgrouplist_insert_agg" () RETURNS opaque AS '
BEGIN
        INSERT INTO forum_agg_msg_count (group_forum_id,count) 
                VALUES (NEW.group_forum_id,0);
        RETURN NEW;
END;    
' LANGUAGE 'plpgsql';


CREATE FUNCTION "artifactgrouplist_insert_agg" () RETURNS opaque AS '
BEGIN
    INSERT INTO artifact_counts_agg (group_artifact_id,count,open_count) 
        VALUES (NEW.group_artifact_id,0,0);
        RETURN NEW;
END;    
' LANGUAGE 'plpgsql';


CREATE FUNCTION "artifactgroup_update_agg" () RETURNS opaque AS '
BEGIN
	--
	-- see if they are moving to a new artifacttype
	-- if so, its a more complex operation
	--
	IF NEW.group_artifact_id <> OLD.group_artifact_id THEN
		--
		-- transferred artifacts always have a status of 1
		-- so we will increment the new artifacttypes sums
		--
		IF OLD.status_id=3 THEN
			-- No need to decrement counters on old tracker
		ELSE 
			IF OLD.status_id=2 THEN
				UPDATE artifact_counts_agg SET count=count-1 
					WHERE group_artifact_id=OLD.group_artifact_id;
			ELSE 
				IF OLD.status_id=1 THEN
					UPDATE artifact_counts_agg SET count=count-1,open_count=open_count-1 
						WHERE group_artifact_id=OLD.group_artifact_id;
				END IF;
			END IF;
		END IF;

		IF NEW.status_id=3 THEN
			--DO NOTHING
		ELSE
			IF NEW.status_id=2 THEN
					UPDATE artifact_counts_agg SET count=count+1 
						WHERE group_artifact_id=NEW.group_artifact_id;
			ELSE
				IF NEW.status_id=1 THEN
					UPDATE artifact_counts_agg SET count=count+1, open_count=open_count+1 
						WHERE group_artifact_id=NEW.group_artifact_id;
				END IF;
			END IF;
		END IF;
	ELSE
		--
		-- just need to evaluate the status flag and 
		-- increment/decrement the counter as necessary
		--
		IF NEW.status_id <> OLD.status_id THEN
			IF NEW.status_id = 1 THEN
				IF OLD.status_id=2 THEN
					UPDATE artifact_counts_agg SET open_count=open_count+1 
						WHERE group_artifact_id=NEW.group_artifact_id;
				ELSE 
					IF OLD.status_id=3 THEN
						UPDATE artifact_counts_agg SET open_count=open_count+1, count=count+1 
							WHERE group_artifact_id=NEW.group_artifact_id;
					END IF;
				END IF;
			ELSE
				IF NEW.status_id = 2 THEN
					IF OLD.status_id=1 THEN
						UPDATE artifact_counts_agg SET open_count=open_count-1 
							WHERE group_artifact_id=NEW.group_artifact_id;
					ELSE
						IF OLD.status_id=3 THEN
							UPDATE artifact_counts_agg SET count=count+1 
								WHERE group_artifact_id=NEW.group_artifact_id;
						END IF;
					END IF;
				ELSE 
					IF NEW.status_id = 3 THEN
						IF OLD.status_id=2 THEN
							UPDATE artifact_counts_agg SET count=count-1 
								WHERE group_artifact_id=NEW.group_artifact_id;
						ELSE
							IF OLD.status_id=1 THEN
								UPDATE artifact_counts_agg SET open_count=open_count-1,count=count-1 
									WHERE group_artifact_id=NEW.group_artifact_id;
							END IF;
						END IF;
					END IF;
				END IF;
			END IF;
		END IF;	
	END IF;
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "massmail_queue" (
	"id" integer DEFAULT nextval('"massmail_queue_id_seq"'::text) NOT NULL,
	"type" character varying(8) NOT NULL,
	"subject" text NOT NULL,
	"message" text NOT NULL,
	"queued_date" integer NOT NULL,
	"last_userid" integer DEFAULT 0 NOT NULL,
	"failed_date" integer DEFAULT 0 NOT NULL,
	"finished_date" integer DEFAULT 0 NOT NULL,
	Constraint "massmail_queue_pkey" Primary Key ("id")
);


CREATE TABLE "stats_agg_site_by_group" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"count" integer
);


CREATE TABLE "stats_project_metric" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"ranking" integer DEFAULT 0 NOT NULL,
	"percentile" double precision DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL
);


CREATE TABLE "stats_agg_logo_by_group" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"count" integer
);


CREATE TABLE "stats_subd_pages" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"pages" integer DEFAULT 0 NOT NULL
);


CREATE TABLE "stats_cvs_user" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"user_id" integer DEFAULT 0 NOT NULL,
	"checkouts" integer DEFAULT 0 NOT NULL,
	"commits" integer DEFAULT 0 NOT NULL,
	"adds" integer DEFAULT 0 NOT NULL
);


CREATE TABLE "stats_cvs_group" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"checkouts" integer DEFAULT 0 NOT NULL,
	"commits" integer DEFAULT 0 NOT NULL,
	"adds" integer DEFAULT 0 NOT NULL
);


CREATE TABLE "stats_project_developers" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"developers" integer DEFAULT 0 NOT NULL
);


CREATE TABLE "stats_project" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"file_releases" integer DEFAULT 0,
	"msg_posted" integer DEFAULT 0,
	"msg_uniq_auth" integer DEFAULT 0,
	"bugs_opened" integer DEFAULT 0,
	"bugs_closed" integer DEFAULT 0,
	"support_opened" integer DEFAULT 0,
	"support_closed" integer DEFAULT 0,
	"patches_opened" integer DEFAULT 0,
	"patches_closed" integer DEFAULT 0,
	"artifacts_opened" integer DEFAULT 0,
	"artifacts_closed" integer DEFAULT 0,
	"tasks_opened" integer DEFAULT 0,
	"tasks_closed" integer DEFAULT 0,
	"help_requests" integer DEFAULT 0
);


CREATE TABLE "stats_site" (
	"month" integer,
	"day" integer,
	"uniq_users" integer,
	"sessions" integer,
	"total_users" integer,
	"new_users" integer,
	"new_projects" integer
);


CREATE TABLE "activity_log" (
	"day" integer DEFAULT '0' NOT NULL,
	"hour" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"browser" character varying(8) DEFAULT 'OTHER' NOT NULL,
	"ver" double precision DEFAULT '0.00' NOT NULL,
	"platform" character varying(8) DEFAULT 'OTHER' NOT NULL,
	"time" integer DEFAULT '0' NOT NULL,
	"page" text,
	"type" integer DEFAULT '0' NOT NULL
);


CREATE TABLE "user_metric_history" (
	"month" integer NOT NULL,
	"day" integer NOT NULL,
	"user_id" integer NOT NULL,
	"ranking" integer NOT NULL,
	"metric" double precision NOT NULL
);


CREATE TABLE "frs_dlstats_filetotal_agg" (
	"file_id" integer,
	"downloads" integer
);


CREATE TABLE "stats_project_months" (
	"month" integer,
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
	"file_releases" integer,
	"msg_posted" integer,
	"msg_uniq_auth" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);


CREATE TABLE "stats_site_pages_by_month" (
	"month" integer,
	"site_page_views" integer
);


CREATE TABLE "stats_site_months" (
	"month" integer,
	"site_page_views" integer,
	"downloads" integer,
	"subdomain_views" integer,
	"msg_posted" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);


CREATE TABLE "trove_agg" (
	"trove_cat_id" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30),
	"status" character(1),
	"register_time" integer,
	"short_description" character varying(255),
	"percentile" double precision,
	"ranking" integer
);


CREATE SEQUENCE "trove_treesum_trove_treesum_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;


CREATE TABLE "trove_treesums" (
	"trove_treesums_id" integer DEFAULT nextval('"trove_treesum_trove_treesum_seq"'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"limit_1" integer DEFAULT '0' NOT NULL,
	"subprojects" integer DEFAULT '0' NOT NULL,
	Constraint "trove_treesums_pkey" Primary Key ("trove_treesums_id")
);


CREATE TABLE "frs_dlstats_file" (
	"ip_address" text,
	"file_id" integer,
	"month" integer,
	"day" integer
);


CREATE SEQUENCE "group_cvs_history_id_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "group_cvs_history" (
	"id" integer DEFAULT nextval('"group_cvs_history_id_seq"'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"user_name" character varying(80) DEFAULT '' NOT NULL,
	"cvs_commits" integer DEFAULT '0' NOT NULL,
	"cvs_commits_wk" integer DEFAULT '0' NOT NULL,
	"cvs_adds" integer DEFAULT '0' NOT NULL,
	"cvs_adds_wk" integer DEFAULT '0' NOT NULL
);


CREATE SEQUENCE "themes_theme_id_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "themes" (
	"theme_id" integer DEFAULT nextval('"themes_theme_id_seq"'::text) NOT NULL,
	"dirname" character varying(80),
	"fullname" character varying(80),
	"enabled" boolean DEFAULT 't'::bool
);


CREATE TABLE "theme_prefs" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"user_theme" integer DEFAULT '0' NOT NULL,
	"body_font" character(80) DEFAULT '',
	"body_size" character(5) DEFAULT '',
	"titlebar_font" character(80) DEFAULT '',
	"titlebar_size" character(5) DEFAULT '',
	"color_titlebar_back" character(7) DEFAULT '',
	"color_ltback1" character(7) DEFAULT '',
	Constraint "theme_prefs_pkey" Primary Key ("user_id")
);


CREATE SEQUENCE "supported_langu_language_id_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "supported_languages" (
	"language_id" integer DEFAULT nextval('"supported_langu_language_id_seq"'::text) NOT NULL,
	"name" text,
	"filename" text,
	"classname" text,
	"language_code" character(5),
	Constraint "supported_languages_pkey" Primary Key ("language_id")
);


CREATE VIEW "forum_user_vw" as SELECT forum.msg_id, forum.group_forum_id, forum.posted_by, forum.subject, forum.body, forum.date, forum.is_followup_to, forum.thread_id, forum.has_followups, forum.most_recent_date, users.user_name, users.realname FROM forum, users WHERE (forum.posted_by = users.user_id);


CREATE VIEW "forum_group_list_vw" as SELECT forum_group_list.group_forum_id, forum_group_list.group_id, forum_group_list.forum_name, forum_group_list.is_public, forum_group_list.description, forum_group_list.allow_anonymous, forum_group_list.send_all_posts_to, forum_agg_msg_count.count AS total, (SELECT max(forum.date) AS recent FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS recent, (SELECT count(*) AS count FROM (SELECT forum.thread_id FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id) GROUP BY forum.thread_id) tmp) AS threads FROM (forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id));


CREATE SEQUENCE "skills_data_pk_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE SEQUENCE "skills_data_types_pk_seq" start 0 increment 1 maxvalue 9223372036854775807 minvalue 0 cache 1;


CREATE TABLE "skills_data_types" (
	"type_id" integer DEFAULT nextval('skills_data_types_pk_seq'::text) NOT NULL,
	"type_name" character varying(25) DEFAULT '' NOT NULL,
	Constraint "skills_data_types_pkey" Primary Key ("type_id")
);


CREATE TABLE "skills_data" (
	"skills_data_id" integer DEFAULT nextval('skills_data_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"type" integer DEFAULT '0' NOT NULL,
	"title" character varying(100) DEFAULT '' NOT NULL,
	"start" integer DEFAULT '0' NOT NULL,
	"finish" integer DEFAULT '0' NOT NULL,
	"keywords" character varying(255) DEFAULT '' NOT NULL,
	Constraint "skills_data_pkey" Primary Key ("skills_data_id")
);


CREATE VIEW "frs_file_vw" as SELECT frs_file.file_id, frs_file.filename, frs_file.release_id, frs_file.type_id, frs_file.processor_id, frs_file.release_time, frs_file.file_size, frs_file.post_date, frs_filetype.name AS filetype, frs_processor.name AS processor, frs_dlstats_filetotal_agg.downloads FROM frs_filetype, frs_processor, (frs_file LEFT JOIN frs_dlstats_filetotal_agg ON ((frs_dlstats_filetotal_agg.file_id = frs_file.file_id))) WHERE ((frs_filetype.type_id = frs_file.type_id) AND (frs_processor.processor_id = frs_file.processor_id));


CREATE SEQUENCE "project_categor_category_id_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "project_category" (
	"category_id" integer DEFAULT nextval('"project_categor_category_id_seq"'::text) NOT NULL,
	"group_project_id" integer,
	"category_name" text
);


CREATE TABLE "project_task_artifact" (
	"project_task_id" integer,
	"artifact_id" integer
);


CREATE TABLE "project_group_forum" (
	"group_project_id" integer,
	"group_forum_id" integer
);


CREATE TABLE "project_group_doccat" (
	"group_project_id" integer,
	"doc_group_id" integer
);


CREATE VIEW "project_depend_vw" as SELECT pt.project_task_id, pd.is_dependent_on_task_id, pt.end_date, pt.start_date FROM (project_task pt NATURAL JOIN project_dependencies pd);


CREATE VIEW "project_dependon_vw" as SELECT pd.project_task_id, pd.is_dependent_on_task_id, pt.end_date, pt.start_date FROM (project_task pt FULL JOIN project_dependencies pd ON ((pd.is_dependent_on_task_id = pt.project_task_id)));


CREATE VIEW "project_history_user_vw" as SELECT users.realname, users.email, users.user_name, project_history.project_history_id, project_history.project_task_id, project_history.field_name, project_history.old_value, project_history.mod_by, project_history.mod_date FROM users, project_history WHERE (project_history.mod_by = users.user_id);


CREATE SEQUENCE "project_messa_project_messa_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "project_messages" (
	"project_message_id" integer DEFAULT nextval('"project_messa_project_messa_seq"'::text) NOT NULL,
	"project_task_id" integer NOT NULL,
	"body" text,
	"posted_by" integer NOT NULL,
	"postdate" integer NOT NULL
);


CREATE VIEW "project_message_user_vw" as SELECT users.realname, users.email, users.user_name, project_messages.project_message_id, project_messages.project_task_id, project_messages.body, project_messages.posted_by, project_messages.postdate FROM users, project_messages WHERE (project_messages.posted_by = users.user_id);


CREATE FUNCTION "projtask_update_depend" () RETURNS opaque AS '
DECLARE
    dependent RECORD;
    dependon RECORD;
    delta   INTEGER;
BEGIN
    --
    --  See if tasks that are dependent on us are OK
    --  See if the end date has changed
    --
    IF NEW.end_date > OLD.end_date THEN
        --
        --  If the end date pushed back, push back dependent tasks
        --
        FOR dependent IN SELECT * FROM project_depend_vw WHERE is_dependent_on_task_id=NEW.project_task_id LOOP
            --
            --  Some dependent tasks may not start immediately
            --
            IF dependent.start_date > OLD.end_date THEN
                IF dependent.start_date < NEW.end_date THEN
                    delta := NEW.end_date-dependent.start_date;
                    UPDATE project_task
                        SET start_date=start_date+delta,
                        end_date=end_date+delta
                        WHERE project_task_id=dependent.project_task_id;
                END IF;
            ELSE
                IF dependent.start_date = OLD.end_date THEN
                    delta := NEW.end_date-OLD.end_date;
                    UPDATE project_task
                        SET start_date=start_date+delta,
                        end_date=end_date+delta
                        WHERE project_task_id=dependent.project_task_id;
                END IF;
            END IF;
        END LOOP;
    ELSIF NEW.end_date < OLD.end_date THEN
            --
            --  If the end date moved up, move up dependent tasks
            --
            FOR dependent IN SELECT * FROM project_depend_vw WHERE is_dependent_on_task_id=NEW.project_task_id LOOP
                IF dependent.start_date = OLD.end_date THEN
                    --
                    --  dependent task was constrained by us - bring it forward
                    --
                    delta := OLD.end_date-NEW.end_date;
                    UPDATE project_task
                        SET start_date=start_date-delta,
                        end_date=end_date-delta
                        WHERE project_task_id=dependent.project_task_id;
                END IF;
            END LOOP;
    END IF;
    RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE FUNCTION "projtask_insert_depend" () RETURNS opaque AS '
DECLARE
        dependon RECORD;
        delta INTEGER;
BEGIN
        --
        --  ENFORCE START/END DATE logic
        --
        IF NEW.start_date > NEW.end_date THEN
                RAISE EXCEPTION ''START DATE CANNOT BE AFTER END DATE'';
        END IF;
        --
        --        First make sure we start on or after end_date of tasks
        --        that we depend on
        --
        FOR dependon IN SELECT * FROM project_dependon_vw
                                WHERE project_task_id=NEW.project_task_id LOOP
                --
                --        See if the task we are dependon on
                --        ends after we are supposed to start
                --
                IF dependon.end_date > NEW.start_date THEN
                        delta := dependon.end_date-NEW.start_date;
                        RAISE NOTICE ''Bumping Back: % Delta: % '',NEW.project_task_id,delta;
                        NEW.start_date := NEW.start_date+delta;
                        NEW.end_date := NEW.end_date+delta;
                END IF;

        END LOOP;
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE VIEW "docdata_vw" as SELECT users.user_name, users.realname, users.email, d.group_id, d.docid, d.stateid, d.title, d.updatedate, d.createdate, d.created_by, d.doc_group, d.description, d.language_id, d.filename, d.filetype, doc_states.name AS state_name, doc_groups.groupname AS group_name, sl.name AS language_name FROM ((((doc_data d NATURAL JOIN doc_states) NATURAL JOIN doc_groups) JOIN supported_languages sl ON ((sl.language_id = d.language_id))) JOIN users ON ((users.user_id = d.created_by)));


CREATE VIEW "artifact_group_list_vw" as SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description, agl.is_public, agl.allow_anon, agl.email_all_updates, agl.email_address, agl.due_period, agl.use_resolution, agl.submit_instructions, agl.browse_instructions, agl.datatype, agl.status_timeout, aca.count, aca.open_count FROM (artifact_group_list agl LEFT JOIN artifact_counts_agg aca USING (group_artifact_id));


CREATE VIEW "project_task_vw" as SELECT project_task.project_task_id, project_task.group_project_id, project_task.summary, project_task.details, project_task.percent_complete, project_task.priority, project_task.hours, project_task.start_date, project_task.end_date, project_task.created_by, project_task.status_id, project_task.category_id, project_category.category_name, project_status.status_name, users.user_name, users.realname FROM (((project_task FULL JOIN project_category ON ((project_category.category_id = project_task.category_id))) FULL JOIN users ON ((users.user_id = project_task.created_by))) NATURAL JOIN project_status);


CREATE FUNCTION "frs_dlstats_filetotal_insert_ag" () RETURNS opaque AS '
BEGIN
	INSERT INTO frs_dlstats_filetotal_agg (file_id, downloads) VALUES (NEW.file_id, 0);
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE VIEW "frs_dlstats_file_agg_vw" as SELECT frs_dlstats_file."month", frs_dlstats_file."day", frs_dlstats_file.file_id, count(*) AS downloads FROM frs_dlstats_file GROUP BY frs_dlstats_file."month", frs_dlstats_file."day", frs_dlstats_file.file_id;


CREATE VIEW "frs_dlstats_grouptotal_vw" as SELECT frs_package.group_id, sum(frs_dlstats_filetotal_agg.downloads) AS downloads FROM frs_package, frs_release, frs_file, frs_dlstats_filetotal_agg WHERE (((frs_package.package_id = frs_release.package_id) AND (frs_release.release_id = frs_file.release_id)) AND (frs_file.file_id = frs_dlstats_filetotal_agg.file_id)) GROUP BY frs_package.group_id;


CREATE VIEW "frs_dlstats_group_vw" as SELECT frs_package.group_id, fdfa."month", fdfa."day", sum(fdfa.downloads) AS downloads FROM frs_package, frs_release, frs_file, frs_dlstats_file_agg_vw fdfa WHERE (((frs_package.package_id = frs_release.package_id) AND (frs_release.release_id = frs_file.release_id)) AND (frs_file.file_id = fdfa.file_id)) GROUP BY frs_package.group_id, fdfa."month", fdfa."day";


CREATE VIEW "stats_project_vw" as SELECT spd.group_id, spd."month", spd."day", spd.developers, spm.ranking AS group_ranking, spm.percentile AS group_metric, salbg.count AS logo_showings, fdga.downloads, sasbg.count AS site_views, ssp.pages AS subdomain_views, (CASE WHEN (sasbg.count IS NOT NULL) THEN sasbg.count WHEN (0 IS NOT NULL) THEN 0 ELSE NULL::int4 END + CASE WHEN (ssp.pages IS NOT NULL) THEN ssp.pages WHEN (0 IS NOT NULL) THEN 0 ELSE NULL::int4 END) AS page_views, sp.file_releases, sp.msg_posted, sp.msg_uniq_auth, sp.bugs_opened, sp.bugs_closed, sp.support_opened, sp.support_closed, sp.patches_opened, sp.patches_closed, sp.artifacts_opened, sp.artifacts_closed, sp.tasks_opened, sp.tasks_closed, sp.help_requests, scg.checkouts AS cvs_checkouts, scg.commits AS cvs_commits, scg.adds AS cvs_adds FROM (((((((stats_project_developers spd LEFT JOIN stats_project sp USING ("month", "day", group_id)) LEFT JOIN stats_project_metric spm USING ("month", "day", group_id)) LEFT JOIN stats_cvs_group scg USING ("month", "day", group_id)) LEFT JOIN stats_agg_site_by_group sasbg USING ("month", "day", group_id)) LEFT JOIN stats_agg_logo_by_group salbg USING ("month", "day", group_id)) LEFT JOIN stats_subd_pages ssp USING ("month", "day", group_id)) LEFT JOIN frs_dlstats_group_vw fdga USING ("month", "day", group_id));


CREATE VIEW "stats_project_all_vw" as SELECT stats_project_months.group_id, int4(avg(stats_project_months.developers)) AS developers, int4(avg(stats_project_months.group_ranking)) AS group_ranking, avg(stats_project_months.group_metric) AS group_metric, sum(stats_project_months.logo_showings) AS logo_showings, sum(stats_project_months.downloads) AS downloads, sum(stats_project_months.site_views) AS site_views, sum(stats_project_months.subdomain_views) AS subdomain_views, sum(stats_project_months.page_views) AS page_views, sum(stats_project_months.file_releases) AS file_releases, sum(stats_project_months.msg_posted) AS msg_posted, int4(avg(stats_project_months.msg_uniq_auth)) AS msg_uniq_auth, sum(stats_project_months.bugs_opened) AS bugs_opened, sum(stats_project_months.bugs_closed) AS bugs_closed, sum(stats_project_months.support_opened) AS support_opened, sum(stats_project_months.support_closed) AS support_closed, sum(stats_project_months.patches_opened) AS patches_opened, sum(stats_project_months.patches_closed) AS patches_closed, sum(stats_project_months.artifacts_opened) AS artifacts_opened, sum(stats_project_months.artifacts_closed) AS artifacts_closed, sum(stats_project_months.tasks_opened) AS tasks_opened, sum(stats_project_months.tasks_closed) AS tasks_closed, sum(stats_project_months.help_requests) AS help_requests, sum(stats_project_months.cvs_checkouts) AS cvs_checkouts, sum(stats_project_months.cvs_commits) AS cvs_commits, sum(stats_project_months.cvs_adds) AS cvs_adds FROM stats_project_months GROUP BY stats_project_months.group_id;


CREATE VIEW "stats_site_vw" as SELECT p."month", p."day", sspbd.site_page_views, sum(p.downloads) AS downloads, sum(p.subdomain_views) AS subdomain_views, sum(p.msg_posted) AS msg_posted, sum(p.bugs_opened) AS bugs_opened, sum(p.bugs_closed) AS bugs_closed, sum(p.support_opened) AS support_opened, sum(p.support_closed) AS support_closed, sum(p.patches_opened) AS patches_opened, sum(p.patches_closed) AS patches_closed, sum(p.artifacts_opened) AS artifacts_opened, sum(p.artifacts_closed) AS artifacts_closed, sum(p.tasks_opened) AS tasks_opened, sum(p.tasks_closed) AS tasks_closed, sum(p.help_requests) AS help_requests, sum(p.cvs_checkouts) AS cvs_checkouts, sum(p.cvs_commits) AS cvs_commits, sum(p.cvs_adds) AS cvs_adds FROM stats_project_vw p, stats_site_pages_by_day sspbd WHERE ((p."month" = sspbd."month") AND (p."day" = sspbd."day")) GROUP BY p."month", p."day", sspbd.site_page_views;


CREATE VIEW "stats_site_all_vw" as SELECT sum(stats_site_months.site_page_views) AS site_page_views, sum(stats_site_months.downloads) AS downloads, sum(stats_site_months.subdomain_views) AS subdomain_views, sum(stats_site_months.msg_posted) AS msg_posted, sum(stats_site_months.bugs_opened) AS bugs_opened, sum(stats_site_months.bugs_closed) AS bugs_closed, sum(stats_site_months.support_opened) AS support_opened, sum(stats_site_months.support_closed) AS support_closed, sum(stats_site_months.patches_opened) AS patches_opened, sum(stats_site_months.patches_closed) AS patches_closed, sum(stats_site_months.artifacts_opened) AS artifacts_opened, sum(stats_site_months.artifacts_closed) AS artifacts_closed, sum(stats_site_months.tasks_opened) AS tasks_opened, sum(stats_site_months.tasks_closed) AS tasks_closed, sum(stats_site_months.help_requests) AS help_requests, sum(stats_site_months.cvs_checkouts) AS cvs_checkouts, sum(stats_site_months.cvs_commits) AS cvs_commits, sum(stats_site_months.cvs_adds) AS cvs_adds FROM stats_site_months;


CREATE SEQUENCE "plugins_pk_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "plugins" (
	"plugin_id" integer DEFAULT nextval('plugins_pk_seq'::text) NOT NULL,
	"plugin_name" character varying(32) NOT NULL,
	"plugin_desc" text,
	Constraint "plugins_pkey" Primary Key ("plugin_id")
);


CREATE SEQUENCE "group_plugin_pk_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "group_plugin" (
	"group_plugin_id" integer DEFAULT nextval('group_plugin_pk_seq'::text) NOT NULL,
	"group_id" integer,
	"plugin_id" integer,
	Constraint "group_plugin_pkey" Primary Key ("group_plugin_id")
);


CREATE SEQUENCE "user_plugin_pk_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1;


CREATE TABLE "user_plugin" (
	"user_plugin_id" integer DEFAULT nextval('user_plugin_pk_seq'::text) NOT NULL,
	"user_id" integer,
	"plugin_id" integer,
	Constraint "user_plugin_pkey" Primary Key ("user_plugin_id")
);


CREATE FUNCTION "project_sums" () RETURNS opaque AS '
	DECLARE
		num integer;
		curr_group integer;
		found integer;
	BEGIN
		---
		--- Get number of things this group has now
		---
		IF TG_ARGV[0]=''surv'' THEN
			IF TG_OP=''DELETE'' THEN
				SELECT INTO num count(*) FROM surveys WHERE OLD.group_id=group_id AND is_active=1;
				curr_group := OLD.group_id;
			ELSE
				SELECT INTO num count(*) FROM surveys WHERE NEW.group_id=group_id AND is_active=1;
				curr_group := NEW.group_id;
			END IF;
		END IF;
		IF TG_ARGV[0]=''mail'' THEN
			IF TG_OP=''DELETE'' THEN
				SELECT INTO num count(*) FROM mail_group_list WHERE OLD.group_id=group_id AND is_public=1;
				curr_group := OLD.group_id;
			ELSE
				SELECT INTO num count(*) FROM mail_group_list WHERE NEW.group_id=group_id AND is_public=1;
				curr_group := NEW.group_id;
			END IF;
		END IF;
		IF TG_ARGV[0]=''fmsg'' THEN
			IF TG_OP=''DELETE'' THEN
				SELECT INTO curr_group group_id FROM forum_group_list WHERE OLD.group_forum_id=group_forum_id;
				SELECT INTO num count(*) FROM forum, forum_group_list WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 AND forum_group_list.group_id=curr_group;
			ELSE
				SELECT INTO curr_group group_id FROM forum_group_list WHERE NEW.group_forum_id=group_forum_id;
				SELECT INTO num count(*) FROM forum, forum_group_list WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 AND forum_group_list.group_id=curr_group;
			END IF;
		END IF;
		IF TG_ARGV[0]=''fora'' THEN
			IF TG_OP=''DELETE'' THEN
				SELECT INTO num count(*) FROM forum_group_list WHERE OLD.group_id=group_id AND is_public=1;
				curr_group = OLD.group_id;
				--- also need to update message count
				DELETE FROM project_sums_agg WHERE group_id=OLD.group_id AND type=''fmsg'';
				INSERT INTO project_sums_agg
					SELECT forum_group_list.group_id,''fmsg''::text AS type, count(forum.msg_id) AS count
					FROM forum, forum_group_list
					WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 GROUP BY group_id,type;
			ELSE
				SELECT INTO num count(*) FROM forum_group_list WHERE NEW.group_id=group_id AND is_public=1;
				curr_group = NEW.group_id;
				--- fora do not get deleted... they get their status set to 9
				IF NEW.is_public=9 THEN
					--- also need to update message count
					DELETE FROM project_sums_agg WHERE group_id=NEW.group_id AND type=''fmsg'';
					INSERT INTO project_sums_agg
						SELECT forum_group_list.group_id,''fmsg''::text AS type, count(forum.msg_id) AS count
						FROM forum, forum_group_list
						WHERE forum.group_forum_id=forum_group_list.group_forum_id AND forum_group_list.is_public=1 GROUP BY group_id,type;
				END IF;
			END IF;
		END IF;
		---
		--- See if this group already has a row in project_sums_agg for these things
		---
		SELECT INTO found count(group_id) FROM project_sums_agg WHERE curr_group=group_id AND type=TG_ARGV[0];

		IF found=0 THEN
			---
			--- Create row for this group
			---
			INSERT INTO project_sums_agg
				VALUES (curr_group, TG_ARGV[0], num);
		ELSE
			---
			--- Update count
			---
			UPDATE project_sums_agg SET count=num
			WHERE curr_group=group_id AND type=TG_ARGV[0];
		END IF;

		IF TG_OP=''DELETE'' THEN
			RETURN OLD;
		ELSE
			RETURN NEW;
		END IF;
	END;
' LANGUAGE 'plpgsql';



COPY "canned_responses" FROM stdin;
\.


COPY "db_images" FROM stdin;
\.


COPY "doc_data" FROM stdin;
\.


COPY "doc_groups" FROM stdin;
\.


COPY "doc_states" FROM stdin;
1	active
2	deleted
3	pending
4	hidden
5	private
\.


COPY "filemodule_monitor" FROM stdin;
\.


COPY "forum" FROM stdin;
\.


COPY "forum_agg_msg_count" FROM stdin;
\.


COPY "forum_group_list" FROM stdin;
\.


COPY "forum_monitored_forums" FROM stdin;
\.


COPY "forum_saved_place" FROM stdin;
\.


COPY "frs_file" FROM stdin;
\.


COPY "frs_filetype" FROM stdin;
1000	.deb
2000	.rpm
3000	.zip
3100	.bz2
3110	.gz
5000	Source .zip
5010	Source .bz2
5020	Source .gz
5100	Source .rpm
5900	Other Source File
8000	.jpg
8100	text
8200	html
8300	pdf
9999	Other
\.


COPY "frs_package" FROM stdin;
\.


COPY "frs_processor" FROM stdin;
1000	i386
6000	IA64
7000	Alpha
8000	Any
2000	PPC
3000	MIPS
4000	Sparc
5000	UltraSparc
9999	Other
\.


COPY "frs_release" FROM stdin;
\.


COPY "frs_status" FROM stdin;
1	Active
3	Hidden
\.


COPY "group_history" FROM stdin;
\.


COPY "groups" FROM stdin;
2	Stats Group	\N	0	A	stats	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N	1	1	1	1	1	1
3	News Group	\N	0	A	news	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N	1	1	1	1	1	1
4	Peer Ratings Group	\N	0	A	peerrating	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N	1	1	1	1	1	1
1	Master Group	\N	0	A	gforge	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N	1	1	1	1	1	1
\.


COPY "mail_group_list" FROM stdin;
\.


COPY "news_bytes" FROM stdin;
\.


COPY "people_job" FROM stdin;
\.


COPY "people_job_category" FROM stdin;
1	Developer	0
2	Project Manager	0
3	Unix Admin	0
4	Doc Writer	0
5	Tester	0
6	Support Manager	0
7	Graphic/Other Designer	0
\.


COPY "people_job_inventory" FROM stdin;
\.


COPY "people_job_status" FROM stdin;
1	Open
2	Filled
3	Deleted
\.


COPY "people_skill" FROM stdin;
\.


COPY "people_skill_inventory" FROM stdin;
\.


COPY "people_skill_level" FROM stdin;
1	Want to Learn
2	Competent
3	Wizard
4	Wrote The Book
5	Wrote It
\.


COPY "people_skill_year" FROM stdin;
1	< 6 Months
2	6 Mo - 2 yr
3	2 yr - 5 yr
4	5 yr - 10 yr
5	> 10 years
\.


COPY "project_assigned_to" FROM stdin;
\.


COPY "project_dependencies" FROM stdin;
\.


COPY "project_group_list" FROM stdin;
1	1	Default	0	Default Project - Don't Change	\N
\.


COPY "project_history" FROM stdin;
\.


COPY "project_metric" FROM stdin;
\.


COPY "project_metric_tmp1" FROM stdin;
\.


COPY "project_status" FROM stdin;
1	Open
2	Closed
100	None
3	Deleted
\.


COPY "project_task" FROM stdin;
1	1			0	0	0	0	0	100	100	100
\.


COPY "project_weekly_metric" FROM stdin;
\.


COPY "session" FROM stdin;
\.


COPY "snippet" FROM stdin;
\.


COPY "snippet_package" FROM stdin;
\.


COPY "snippet_package_item" FROM stdin;
\.


COPY "snippet_package_version" FROM stdin;
\.


COPY "snippet_version" FROM stdin;
\.


COPY "stats_agg_logo_by_day" FROM stdin;
\.


COPY "stats_agg_pages_by_day" FROM stdin;
\.


COPY "survey_question_types" FROM stdin;
1	Radio Buttons 1-5
2	Text Area
3	Radio Buttons Yes/No
4	Comment Only
5	Text Field
100	None
\.


COPY "survey_questions" FROM stdin;
\.


COPY "survey_rating_aggregate" FROM stdin;
\.


COPY "survey_rating_response" FROM stdin;
\.


COPY "survey_responses" FROM stdin;
\.


COPY "surveys" FROM stdin;
\.


COPY "trove_cat" FROM stdin;
1	2000031601	0	0	audience	Intended Audience	The main class of people likely to be interested in this resource.	0	0	Intended Audience	1
2	2000032401	1	1	endusers	End Users/Desktop	Programs and resources for software end users. Software for the desktop.	0	0	Intended Audience :: End Users/Desktop	1 :: 2
3	2000041101	1	1	developers	Developers	Programs and resources for software developers, to include libraries.	0	0	Intended Audience :: Developers	1 :: 3
4	2000031601	1	1	sysadmins	System Administrators	Programs and resources for people who administer computers and networks.	0	0	Intended Audience :: System Administrators	1 :: 4
5	2000040701	1	1	other	Other Audience	Programs and resources for an unlisted audience.	0	0	Intended Audience :: Other Audience	1 :: 5
6	2000031601	0	0	developmentstatus	Development Status	An indication of the development status of the software or resource.	0	0	Development Status	6
7	2000040701	6	6	planning	1 - Planning	This resource is in the planning stages only. There is no code.	0	0	Development Status :: 1 - Planning	6 :: 7
8	2000040701	6	6	prealpha	2 - Pre-Alpha	There is code for this project, but it is not usable except for further development.	0	0	Development Status :: 2 - Pre-Alpha	6 :: 8
9	2000041101	6	6	alpha	3 - Alpha	Resource is in early development, and probably incomplete and/or extremely buggy.	0	0	Development Status :: 3 - Alpha	6 :: 9
10	2000040701	6	6	beta	4 - Beta	Resource is in late phases of development. Deliverables are essentially complete, but may still have significant bugs.	0	0	Development Status :: 4 - Beta	6 :: 10
11	2000040701	6	6	production	5 - Production/Stable	Deliverables are complete and usable by the intended audience.	0	0	Development Status :: 5 - Production/Stable	6 :: 11
12	2000040701	6	6	mature	6 - Mature	This resource has an extensive history of successful use and has probably undergone several stable revisions.	0	0	Development Status :: 6 - Mature	6 :: 12
13	2000031601	0	0	license	License	License terms under which the resource is distributed.	0	0	License	13
197	2000032001	13	13	publicdomain	Public Domain	Public Domain. No author-retained rights.	0	0	License :: Public Domain	13 :: 197
196	2000040701	13	13	other	Other/Proprietary License	Non OSI-Approved/Proprietary license.	0	0	License :: Other/Proprietary License	13 :: 196
14	2000032401	13	13	osi	OSI Approved	Licenses that have been approved by OSI as approved	0	0	License :: OSI Approved	13 :: 14
303	2001041701	14	13	nethack	Nethack General Public License	Nethack General Public License	0	0	License :: OSI Approved :: Nethack General Public License	13 :: 14 :: 303
141	2000032001	136	18	clustering	Clustering/Distributed Networks	Tools for automatically distributing computation across a network.	0	0	Topic :: System :: Clustering/Distributed Networks	18 :: 136 :: 141
139	2000032001	136	18	boot	Boot	Programs for bootstrapping your OS.	0	0	Topic :: System :: Boot	18 :: 136 :: 139
140	2000032001	139	18	init	Init	Init-time programs to start system services after boot.	0	0	Topic :: System :: Boot :: Init	18 :: 136 :: 139 :: 140
138	2000032001	136	18	benchmark	Benchmark	Programs for benchmarking system performance.	0	0	Topic :: System :: Benchmark	18 :: 136 :: 138
74	2000042701	136	18	emulators	Emulators	Emulations of foreign operating systme and machines.	0	0	Topic :: System :: Emulators	18 :: 136 :: 74
19	2000032001	136	18	archiving	Archiving	Tools for maintaining and searching software or document archives.	0	0	Topic :: System :: Archiving	18 :: 136 :: 19
137	2000032001	19	18	backup	Backup	Programs to manage and sequence system backup.	0	0	Topic :: System :: Archiving :: Backup	18 :: 136 :: 19 :: 137
42	2000031601	19	18	compression	Compression	Tools and libraries for data compression.	0	0	Topic :: System :: Archiving :: Compression	18 :: 136 :: 19 :: 42
41	2000031601	19	18	packaging	Packaging	Tools for packing and unpacking multi-file formats. Includes data-only formats and software package systems.	0	0	Topic :: System :: Archiving :: Packaging	18 :: 136 :: 19 :: 41
132	2000032001	18	18	religion	Religion	Programs relating to religion and sacred texts.	0	0	Topic :: Religion	18 :: 132
129	2000031701	18	18	office	Office/Business	Software for assisting and organizing work at your desk.	0	0	Topic :: Office/Business	18 :: 129
131	2000032001	129	18	suites	Office Suites	Integrated office suites (word processing, presentation, spreadsheet, database, etc).	0	0	Topic :: Office/Business :: Office Suites	18 :: 129 :: 131
130	2000031701	129	18	scheduling	Scheduling	Projects for scheduling time, to include project management.	0	0	Topic :: Office/Business :: Scheduling	18 :: 129 :: 130
75	2000031701	129	18	financial	Financial	Programs related to finance.	0	0	Topic :: Office/Business :: Financial	18 :: 129 :: 75
79	2000031601	75	18	pointofsale	Point-Of-Sale	Point-Of-Sale applications.	0	0	Topic :: Office/Business :: Financial :: Point-Of-Sale	18 :: 129 :: 75 :: 79
78	2000031601	75	18	spreadsheet	Spreadsheet	Spreadsheet applications.	0	0	Topic :: Office/Business :: Financial :: Spreadsheet	18 :: 129 :: 75 :: 78
77	2000031601	75	18	investment	Investment	Programs for assisting in financial investment.	0	0	Topic :: Office/Business :: Financial :: Investment	18 :: 129 :: 75 :: 77
76	2000031601	75	18	accounting	Accounting	Checkbook balancers and accounting programs.	0	0	Topic :: Office/Business :: Financial :: Accounting	18 :: 129 :: 75 :: 76
234	2000040701	18	18	other	Other/Nonlisted Topic	Topic does not fit into any listed category.	0	0	Topic :: Other/Nonlisted Topic	18 :: 234
156	2000032001	18	18	terminals	Terminals	Terminal emulators, terminal programs, and terminal session utilities.	0	0	Topic :: Terminals	18 :: 156
159	2000032001	156	18	telnet	Telnet	Support for telnet; terminal sessions across Internet links.	0	0	Topic :: Terminals :: Telnet	18 :: 156 :: 159
158	2000032001	156	18	virtual	Terminal Emulators/X Terminals	Programs to handle multiple terminal sessions. Includes terminal emulations for X and other window systems.	0	0	Topic :: Terminals :: Terminal Emulators/X Terminals	18 :: 156 :: 158
157	2000032001	156	18	serial	Serial	Dialup, terminal emulation, and file transfer over serial lines.	0	0	Topic :: Terminals :: Serial	18 :: 156 :: 157
115	2000031701	113	18	capture	Capture/Recording	Sound capture and recording.	0	0	Topic :: Multimedia :: Sound/Audio :: Capture/Recording	18 :: 99 :: 113 :: 115
114	2000031701	113	18	analysis	Analysis	Sound analysis tools, to include frequency analysis.	0	0	Topic :: Multimedia :: Sound/Audio :: Analysis	18 :: 99 :: 113 :: 114
100	2000031601	99	18	graphics	Graphics	Tools and resources for computer graphics.	0	0	Topic :: Multimedia :: Graphics	18 :: 99 :: 100
112	2000031701	100	18	viewers	Viewers	Programs that can display various graphics formats.	0	0	Topic :: Multimedia :: Graphics :: Viewers	18 :: 99 :: 100 :: 112
111	2000031701	100	18	presentation	Presentation	Tools for generating presentation graphics and slides.	0	0	Topic :: Multimedia :: Graphics :: Presentation	18 :: 99 :: 100 :: 111
110	2000031701	100	18	3drendering	3D Rendering	Programs which render 3D models.	0	0	Topic :: Multimedia :: Graphics :: 3D Rendering	18 :: 99 :: 100 :: 110
109	2000031701	100	18	3dmodeling	3D Modeling	Programs for working with 3D Models.	0	0	Topic :: Multimedia :: Graphics :: 3D Modeling	18 :: 99 :: 100 :: 109
106	2000031701	100	18	editors	Editors	Drawing, painting, and structured editing programs.	0	0	Topic :: Multimedia :: Graphics :: Editors	18 :: 99 :: 100 :: 106
108	2000031701	106	18	raster	Raster-Based	Raster/Bitmap based drawing programs.	0	0	Topic :: Multimedia :: Graphics :: Editors :: Raster-Based	18 :: 99 :: 100 :: 106 :: 108
107	2000031701	106	18	vector	Vector-Based	Vector-Based drawing programs.	0	0	Topic :: Multimedia :: Graphics :: Editors :: Vector-Based	18 :: 99 :: 100 :: 106 :: 107
105	2000031701	100	18	conversion	Graphics Conversion	Programs which convert between graphics formats.	0	0	Topic :: Multimedia :: Graphics :: Graphics Conversion	18 :: 99 :: 100 :: 105
101	2000031601	100	18	capture	Capture	Support for scanners, cameras, and screen capture.	0	0	Topic :: Multimedia :: Graphics :: Capture	18 :: 99 :: 100 :: 101
104	2000031601	101	18	screencapture	Screen Capture	Screen capture tools and processors.	0	0	Topic :: Multimedia :: Graphics :: Capture :: Screen Capture	18 :: 99 :: 100 :: 101 :: 104
103	2000031601	101	18	cameras	Digital Camera	Digital Camera	0	0	Topic :: Multimedia :: Graphics :: Capture :: Digital Camera	18 :: 99 :: 100 :: 101 :: 103
102	2000031601	101	18	scanners	Scanners	Support for graphic scanners.	0	0	Topic :: Multimedia :: Graphics :: Capture :: Scanners	18 :: 99 :: 100 :: 101 :: 102
154	2000032001	18	18	printing	Printing	Tools, daemons, and utilities for printer control.	0	0	Topic :: Printing	18 :: 154
136	2000032001	18	18	system	System	Operating system core and administration utilities.	0	0	Topic :: System	18 :: 136
294	2001032001	136	18	shells	System Shells	System Shells	0	0	Topic :: System :: System Shells	18 :: 136 :: 294
257	2000071101	136	18	softwaredist	Software Distribution	Systems software for distributing other software.	0	0	Topic :: System :: Software Distribution	18 :: 136 :: 257
253	2000071101	136	18	sysadministration	Systems Administration	Systems Administration Software (e.g. configuration apps.)	0	0	Topic :: System :: Systems Administration	18 :: 136 :: 253
289	2001032001	253	18	authentication	Authentication/Directory	Authentication and directory services	0	0	Topic :: System :: Systems Administration :: Authentication/Directory	18 :: 136 :: 253 :: 289
291	2001032001	289	18	ldap	LDAP	Leightweight directory access protocol	0	0	Topic :: System :: Systems Administration :: Authentication/Directory :: LDAP	18 :: 136 :: 253 :: 289 :: 291
290	2001032001	289	18	nis	NIS	NIS services	0	0	Topic :: System :: Systems Administration :: Authentication/Directory :: NIS	18 :: 136 :: 253 :: 289 :: 290
153	2000032001	136	18	power	Power (UPS)	Code for communication with uninterruptible power supplies.	0	0	Topic :: System :: Power (UPS)	18 :: 136 :: 153
150	2000032001	136	18	networking	Networking	Network configuration and administration.	0	0	Topic :: System :: Networking	18 :: 136 :: 150
152	2000032001	150	18	monitoring	Monitoring	System monitoring, traffic analysis, and sniffers.	0	0	Topic :: System :: Networking :: Monitoring	18 :: 136 :: 150 :: 152
155	2000032001	152	18	watchdog	Hardware Watchdog	Software to monitor and perform actions or shutdown on hardware trouble detection.	0	0	Topic :: System :: Networking :: Monitoring :: Hardware Watchdog	18 :: 136 :: 150 :: 152 :: 155
151	2000032001	150	18	firewalls	Firewalls	Firewalls and filtering systems.	0	0	Topic :: System :: Networking :: Firewalls	18 :: 136 :: 150 :: 151
148	2000032001	136	18	logging	Logging	Utilities for clearing, rotating, and digesting system logs.	0	0	Topic :: System :: Logging	18 :: 136 :: 148
147	2000032001	136	18	setup	Installation/Setup	Tools for installation and setup of the operating system and other programs.	0	0	Topic :: System :: Installation/Setup	18 :: 136 :: 147
146	2000032001	136	18	hardware	Hardware	Tools for direct, non-kernel control and configuration of hardware.	0	0	Topic :: System :: Hardware	18 :: 136 :: 146
292	2001032001	146	18	drivers	Hardware Drivers	Hardware Drivers	0	0	Topic :: System :: Hardware :: Hardware Drivers	18 :: 136 :: 146 :: 292
144	2000032001	136	18	kernels	Operating System Kernels	OS Kernels, patches, modules, and tools.	0	0	Topic :: System :: Operating System Kernels	18 :: 136 :: 144
239	2000041301	144	18	gnuhurd	GNU Hurd	Kernel code and modules for GNU Hurd.	0	0	Topic :: System :: Operating System Kernels :: GNU Hurd	18 :: 136 :: 144 :: 239
145	2000032001	144	18	bsd	BSD	Code relating to any of the BSD kernels.	0	0	Topic :: System :: Operating System Kernels :: BSD	18 :: 136 :: 144 :: 145
143	2000032001	144	18	linux	Linux	The Linux kernel, patches, and modules.	0	0	Topic :: System :: Operating System Kernels :: Linux	18 :: 136 :: 144 :: 143
142	2000032001	136	18	filesystems	Filesystems	Support for creating, editing, reading, and writing file systems.	0	0	Topic :: System :: Filesystems	18 :: 136 :: 142
287	2001032001	80	18	boardgames	Board Games	Board Games	0	0	Topic :: Games/Entertainment :: Board Games	18 :: 80 :: 287
268	2000082101	80	18	Puzzles	Puzzle Games	Puzzle Games	0	0	Topic :: Games/Entertainment :: Puzzle Games	18 :: 80 :: 268
86	2000031601	80	18	mud	Multi-User Dungeons (MUD)	Massively-multiplayer text based games.	0	0	Topic :: Games/Entertainment :: Multi-User Dungeons (MUD)	18 :: 80 :: 86
85	2000031601	80	18	simulation	Simulation	Simulation games	0	0	Topic :: Games/Entertainment :: Simulation	18 :: 80 :: 85
84	2000031601	80	18	rpg	Role-Playing	Role-Playing games	0	0	Topic :: Games/Entertainment :: Role-Playing	18 :: 80 :: 84
83	2000032401	80	18	turnbasedstrategy	Turn Based Strategy	Turn Based Strategy	0	0	Topic :: Games/Entertainment :: Turn Based Strategy	18 :: 80 :: 83
82	2000031601	80	18	firstpersonshooters	First Person Shooters	First Person Shooters.	0	0	Topic :: Games/Entertainment :: First Person Shooters	18 :: 80 :: 82
81	2000031601	80	18	realtimestrategy	Real Time Strategy	Real Time strategy games	0	0	Topic :: Games/Entertainment :: Real Time Strategy	18 :: 80 :: 81
288	2001032001	80	18	sidescrolling	Side-Scrolling/Arcade Games	Arcade-style side-scrolling games	0	0	Topic :: Games/Entertainment :: Side-Scrolling/Arcade Games	18 :: 80 :: 288
71	2000031601	18	18	education	Education	Programs and tools for educating yourself or others.	0	0	Topic :: Education	18 :: 71
73	2000031601	71	18	testing	Testing	Tools for testing someone's knowledge on a subject.	0	0	Topic :: Education :: Testing	18 :: 71 :: 73
72	2000031601	71	18	cai	Computer Aided Instruction (CAI)	Programs for authoring or using Computer Aided Instrution courses.	0	0	Topic :: Education :: Computer Aided Instruction (CAI)	18 :: 71 :: 72
66	2000031601	18	18	database	Database	Front ends, engines, and tools for database work.	0	0	Topic :: Database	18 :: 66
68	2000031601	66	18	frontends	Front-Ends	Clients and front-ends for generating queries to database engines.	0	0	Topic :: Database :: Front-Ends	18 :: 66 :: 68
67	2000031601	66	18	engines	Database Engines/Servers	Programs that manage data and provide control via some query language.	0	0	Topic :: Database :: Database Engines/Servers	18 :: 66 :: 67
63	2000032001	18	18	editors	Text Editors	Programs for editing code and documents.	0	0	Topic :: Text Editors	18 :: 63
285	2001032001	63	18	textprocessing	Text Processing	Programs or libraries that are designed to batch process text documents	0	0	Topic :: Text Editors :: Text Processing	18 :: 63 :: 285
70	2000031601	63	18	wordprocessors	Word Processors	WYSIWYG word processors.	0	0	Topic :: Text Editors :: Word Processors	18 :: 63 :: 70
69	2000031601	63	18	documentation	Documentation	Tools for the creation and use of documentation.	0	0	Topic :: Text Editors :: Documentation	18 :: 63 :: 69
65	2000031601	63	18	ide	Integrated Development Environments (IDE)	Complete editing environments for code, including cababilities such as compilation and code building assistance.	0	0	Topic :: Text Editors :: Integrated Development Environments (IDE)	18 :: 63 :: 65
64	2000031601	63	18	emacs	Emacs	GNU Emacs and its imitators and tools.	0	0	Topic :: Text Editors :: Emacs	18 :: 63 :: 64
125	2000031701	99	18	video	Video	Video capture, editing, and playback.	0	0	Topic :: Multimedia :: Video	18 :: 99 :: 125
126	2000031701	125	18	capture	Capture	Video capture tools.	0	0	Topic :: Multimedia :: Video :: Capture	18 :: 99 :: 125 :: 126
256	2000071101	125	18	nonlineareditor	Non-Linear Editor	Video Non-Linear Editors	0	0	Topic :: Multimedia :: Video :: Non-Linear Editor	18 :: 99 :: 125 :: 256
128	2000031701	125	18	display	Display	Programs which display various video formats.	0	0	Topic :: Multimedia :: Video :: Display	18 :: 99 :: 125 :: 128
127	2000031701	125	18	conversion	Conversion	Programs which convert between video formats.	0	0	Topic :: Multimedia :: Video :: Conversion	18 :: 99 :: 125 :: 127
113	2000031701	99	18	sound	Sound/Audio	Tools for generating, editing, analyzing, and playing sound.	0	0	Topic :: Multimedia :: Sound/Audio	18 :: 99 :: 113
249	2000042801	113	18	synthesis	Sound Synthesis	Software for creation and synthesis of sound.	0	0	Topic :: Multimedia :: Sound/Audio :: Sound Synthesis	18 :: 99 :: 113 :: 249
248	2000042801	113	18	midi	MIDI	Software related to MIDI synthesis and playback.	0	0	Topic :: Multimedia :: Sound/Audio :: MIDI	18 :: 99 :: 113 :: 248
124	2000031701	113	18	speech	Speech	Speech manipulation and intepretation tools.	0	0	Topic :: Multimedia :: Sound/Audio :: Speech	18 :: 99 :: 113 :: 124
122	2000031701	113	18	players	Players	Programs to play audio files to a sound device.	0	0	Topic :: Multimedia :: Sound/Audio :: Players	18 :: 99 :: 113 :: 122
123	2000031701	122	18	mp3	MP3	Programs to play MP3 audio files.	0	0	Topic :: Multimedia :: Sound/Audio :: Players :: MP3	18 :: 99 :: 113 :: 122 :: 123
121	2000031701	113	18	mixers	Mixers	Programs to mix audio.	0	0	Topic :: Multimedia :: Sound/Audio :: Mixers	18 :: 99 :: 113 :: 121
120	2000031701	113	18	editors	Editors	Programs to edit/manipulate sound data.	0	0	Topic :: Multimedia :: Sound/Audio :: Editors	18 :: 99 :: 113 :: 120
119	2000031701	113	18	conversion	Conversion	Programs to convert between audio formats.	0	0	Topic :: Multimedia :: Sound/Audio :: Conversion	18 :: 99 :: 113 :: 119
116	2000031701	113	18	cdaudio	CD Audio	Programs to play and manipulate audio CDs.	0	0	Topic :: Multimedia :: Sound/Audio :: CD Audio	18 :: 99 :: 113 :: 116
88	2000031601	87	18	finger	Finger	The Finger protocol for getting information about users.	0	0	Topic :: Internet :: Finger	18 :: 87 :: 88
118	2000031701	116	18	cdripping	CD Ripping	Software to convert CD Audio to other digital formats.	0	0	Topic :: Multimedia :: Sound/Audio :: CD Audio :: CD Ripping	18 :: 99 :: 113 :: 116 :: 118
117	2000031701	116	18	cdplay	CD Playing	CD Playing software, to include jukebox software.	0	0	Topic :: Multimedia :: Sound/Audio :: CD Audio :: CD Playing	18 :: 99 :: 113 :: 116 :: 117
260	2000071401	52	18	SCCS	SCCS	SCCS	0	0	Topic :: Software Development :: Version Control :: SCCS	18 :: 45 :: 52 :: 260
54	2000031601	52	18	rcs	RCS	Tools for RCS (Revision Control System).	0	0	Topic :: Software Development :: Version Control :: RCS	18 :: 45 :: 52 :: 54
53	2000031601	52	18	cvs	CVS	Tools for CVS (Concurrent Versioning System).	0	0	Topic :: Software Development :: Version Control :: CVS	18 :: 45 :: 52 :: 53
50	2000031601	45	18	objectbrokering	Object Brokering	Object brokering libraries and tools.	0	0	Topic :: Software Development :: Object Brokering	18 :: 45 :: 50
51	2000031601	50	18	corba	CORBA	Tools for implementation and use of CORBA.	0	0	Topic :: Software Development :: Object Brokering :: CORBA	18 :: 45 :: 50 :: 51
49	2000031601	45	18	interpreters	Interpreters	Programs for interpreting and executing high-level languages directly.	0	0	Topic :: Software Development :: Interpreters	18 :: 45 :: 49
48	2000031601	45	18	compilers	Compilers	Programs for compiling high-level languges into machine code.	0	0	Topic :: Software Development :: Compilers	18 :: 45 :: 48
47	2000031601	45	18	debuggers	Debuggers	Programs for controlling and monitoring the execution of compiled binaries.	0	0	Topic :: Software Development :: Debuggers	18 :: 45 :: 47
46	2000031601	45	18	build	Build Tools	Software for the build process.	0	0	Topic :: Software Development :: Build Tools	18 :: 45 :: 46
43	2000031601	18	18	security	Security	Security-related software, to include system administration and cryptography.	0	0	Topic :: Security	18 :: 43
44	2000031601	43	18	cryptography	Cryptography	Cryptography programs, algorithms, and libraries.	0	0	Topic :: Security :: Cryptography	18 :: 43 :: 44
97	2000042701	18	18	scientific	Scientific/Engineering	Scientific applications, to include research, applied and pure mathematics and sciences.	0	0	Topic :: Scientific/Engineering	18 :: 97
98	2000031601	97	18	mathematics	Mathematics	Software to support pure and applied mathematics.	0	0	Topic :: Scientific/Engineering :: Mathematics	18 :: 97 :: 98
272	2000100501	97	18	HMI	Human Machine Interfaces	This applies to the Factory/Machine control/Automation fields where there are already thousands of applications and millions of installations.	0	0	Topic :: Scientific/Engineering :: Human Machine Interfaces	18 :: 97 :: 272
266	2000081601	97	18	medical	Medical Science Apps.	Medical / BioMedical Science Apps.	0	0	Topic :: Scientific/Engineering :: Medical Science Apps.	18 :: 97 :: 266
252	2000071101	97	18	bioinformatics	Bio-Informatics	Category for gene software (e.g. Gene Ontology)	0	0	Topic :: Scientific/Engineering :: Bio-Informatics	18 :: 97 :: 252
246	2000042701	97	18	eda	Electronic Design Automation (EDA)	Tools for circuit design, schematics, board layout, and more.	0	0	Topic :: Scientific/Engineering :: Electronic Design Automation (EDA)	18 :: 97 :: 246
135	2000032001	97	18	visualization	Visualization	Software for scientific visualization.	0	0	Topic :: Scientific/Engineering :: Visualization	18 :: 97 :: 135
134	2000032001	97	18	astronomy	Astronomy	Software and tools related to astronomy.	0	0	Topic :: Scientific/Engineering :: Astronomy	18 :: 97 :: 134
133	2000032001	97	18	ai	Artificial Intelligence	Artificial Intelligence.	0	0	Topic :: Scientific/Engineering :: Artificial Intelligence	18 :: 97 :: 133
87	2000031601	18	18	internet	Internet	Tools to assist human access to the Internet.	0	0	Topic :: Internet	18 :: 87
270	2000083101	87	18	WAP	WAP	Wireless Access Protocol	0	0	Topic :: Internet :: WAP	18 :: 87 :: 270
245	2000042701	87	18	loganalysis	Log Analysis	Software to help analyze various log files.	0	0	Topic :: Internet :: Log Analysis	18 :: 87 :: 245
149	2000032001	87	18	dns	Name Service (DNS)	Domain name system servers and utilities.	0	0	Topic :: Internet :: Name Service (DNS)	18 :: 87 :: 149
90	2000031601	87	18	www	WWW/HTTP	Programs and tools for the World Wide Web.	0	0	Topic :: Internet :: WWW/HTTP	18 :: 87 :: 90
250	2000042801	90	18	httpservers	HTTP Servers	Software designed to serve content via the HTTP protocol.	0	0	Topic :: Internet :: WWW/HTTP :: HTTP Servers	18 :: 87 :: 90 :: 250
243	2000042701	90	18	sitemanagement	Site Management	Tools for maintanance and management of web sites.	0	0	Topic :: Internet :: WWW/HTTP :: Site Management	18 :: 87 :: 90 :: 243
244	2000042701	243	18	linkchecking	Link Checking	Tools to assist in checking for broken links.	0	0	Topic :: Internet :: WWW/HTTP :: Site Management :: Link Checking	18 :: 87 :: 90 :: 243 :: 244
93	2000031601	90	18	indexing	Indexing/Search	Indexing and search tools for the Web.	0	0	Topic :: Internet :: WWW/HTTP :: Indexing/Search	18 :: 87 :: 90 :: 93
92	2000031601	90	18	dynamic	Dynamic Content	Common Gateway Interface scripting and server-side parsing.	0	0	Topic :: Internet :: WWW/HTTP :: Dynamic Content	18 :: 87 :: 90 :: 92
96	2000031601	92	18	cgi	CGI Tools/Libraries	Tools for the Common Gateway Interface	0	0	Topic :: Internet :: WWW/HTTP :: Dynamic Content :: CGI Tools/Libraries	18 :: 87 :: 90 :: 92 :: 96
95	2000031601	92	18	messageboards	Message Boards	Online message boards	0	0	Topic :: Internet :: WWW/HTTP :: Dynamic Content :: Message Boards	18 :: 87 :: 90 :: 92 :: 95
94	2000031601	92	18	counters	Page Counters	Scripts to count numbers of pageviews.	0	0	Topic :: Internet :: WWW/HTTP :: Dynamic Content :: Page Counters	18 :: 87 :: 90 :: 92 :: 94
91	2000031601	90	18	browsers	Browsers	Web Browsers	0	0	Topic :: Internet :: WWW/HTTP :: Browsers	18 :: 87 :: 90 :: 91
89	2000031601	87	18	ftp	File Transfer Protocol (FTP)	Programs and tools for file transfer via FTP.	0	0	Topic :: Internet :: File Transfer Protocol (FTP)	18 :: 87 :: 89
80	2000031601	18	18	games	Games/Entertainment	Games and Entertainment software.	0	0	Topic :: Games/Entertainment	18 :: 80
18	2000031601	0	0	topic	Topic	Topic categorization.	0	0	Topic	18
20	2000032401	18	18	communications	Communications	Programs intended to facilitate communication between people.	0	0	Topic :: Communications	18 :: 20
27	2000031601	20	18	conferencing	Conferencing	Software to support real-time conferencing over the Internet.	0	0	Topic :: Communications :: Conferencing	18 :: 20 :: 27
22	2000031601	20	18	chat	Chat	Programs to support real-time communication over the Internet.	0	0	Topic :: Communications :: Chat	18 :: 20 :: 22
26	2000031601	22	18	aim	AOL Instant Messanger	Programs to support AOL Instant Messanger.	0	0	Topic :: Communications :: Chat :: AOL Instant Messanger	18 :: 20 :: 22 :: 26
25	2000031601	22	18	talk	Unix Talk	Programs to support Unix Talk protocol.	0	0	Topic :: Communications :: Chat :: Unix Talk	18 :: 20 :: 22 :: 25
24	2000041101	22	18	irc	Internet Relay Chat	Programs to support Internet Relay Chat.	0	0	Topic :: Communications :: Chat :: Internet Relay Chat	18 :: 20 :: 22 :: 24
23	2000031601	22	18	icq	ICQ	Programs to support ICQ.	0	0	Topic :: Communications :: Chat :: ICQ	18 :: 20 :: 22 :: 23
21	2000031601	20	18	bbs	BBS	Bulletin Board systems.	0	0	Topic :: Communications :: BBS	18 :: 20 :: 21
251	2000050101	20	18	filesharing	File Sharing	Software for person-to-person online file sharing.	0	0	Topic :: Communications :: File Sharing	18 :: 20 :: 251
241	2000050101	251	18	napster	Napster	Clients and servers for the Napster file sharing protocol.	0	0	Topic :: Communications :: File Sharing :: Napster	18 :: 20 :: 251 :: 241
286	2001032001	251	18	gnutella	Gnutella	Projects based around the gnutella protocol.	0	0	Topic :: Communications :: File Sharing :: Gnutella	18 :: 20 :: 251 :: 286
247	2000042701	20	18	telephony	Telephony	Telephony related applications, to include automated voice response systems.	0	0	Topic :: Communications :: Telephony	18 :: 20 :: 247
40	2000031601	20	18	internetphone	Internet Phone	Software to support real-time speech communication over the Internet.	0	0	Topic :: Communications :: Internet Phone	18 :: 20 :: 40
39	2000031601	20	18	usenet	Usenet News	Software to support USENET news.	0	0	Topic :: Communications :: Usenet News	18 :: 20 :: 39
38	2000031601	20	18	hamradio	Ham Radio	Tools and resources for amateur radio.	0	0	Topic :: Communications :: Ham Radio	18 :: 20 :: 38
37	2000031601	20	18	fido	FIDO	Tools for FIDOnet mail and echoes.	0	0	Topic :: Communications :: FIDO	18 :: 20 :: 37
36	2000031601	20	18	fax	Fax	Tools for sending and receiving facsimile messages.	0	0	Topic :: Communications :: Fax	18 :: 20 :: 36
28	2000031601	20	18	email	Email	Programs for sending, processing, and handling electronic mail.	0	0	Topic :: Communications :: Email	18 :: 20 :: 28
33	2000031601	28	18	postoffice	Post-Office	Programs to support post-office protocols, including POP and IMAP.	0	0	Topic :: Communications :: Email :: Post-Office	18 :: 20 :: 28 :: 33
35	2000031601	33	18	imap	IMAP	Programs to support IMAP protocol (Internet Message Access Protocol).	0	0	Topic :: Communications :: Email :: Post-Office :: IMAP	18 :: 20 :: 28 :: 33 :: 35
34	2000031601	33	18	pop3	POP3	Programs to support POP3 (Post-Office Protocol, version 3).	0	0	Topic :: Communications :: Email :: Post-Office :: POP3	18 :: 20 :: 28 :: 33 :: 34
32	2000031601	28	18	mta	Mail Transport Agents	Email transport and gatewaying software.	0	0	Topic :: Communications :: Email :: Mail Transport Agents	18 :: 20 :: 28 :: 32
31	2000031601	28	18	mua	Email Clients (MUA)	Programs for interactively reading and sending Email.	0	0	Topic :: Communications :: Email :: Email Clients (MUA)	18 :: 20 :: 28 :: 31
30	2000031601	28	18	listservers	Mailing List Servers	Tools for managing electronic mailing lists.	0	0	Topic :: Communications :: Email :: Mailing List Servers	18 :: 20 :: 28 :: 30
29	2000031601	28	18	filters	Filters	Content-driven filters and dispatchers for Email.	0	0	Topic :: Communications :: Email :: Filters	18 :: 20 :: 28 :: 29
301	2001041701	14	13	nosl	Nokia Open Source License	Nokia Open Source License	0	0	License :: OSI Approved :: Nokia Open Source License	13 :: 14 :: 301
299	2001041701	14	13	iosl	Intel Open Source License	Intel Open Source License	0	0	License :: OSI Approved :: Intel Open Source License	13 :: 14 :: 299
297	2001041701	14	13	vsl	Vovida Software License	Vovida Software License	0	0	License :: OSI Approved :: Vovida Software License	13 :: 14 :: 297
195	2000032001	14	13	zlib	zlib/libpng License	zlib/libpng License	0	0	License :: OSI Approved :: zlib/libpng License	13 :: 14 :: 195
194	2000032001	14	13	python	Python License	Python License	0	0	License :: OSI Approved :: Python License	13 :: 14 :: 194
193	2000032001	14	13	ricoh	Ricoh Source Code Public License	Ricoh Source Code Public License	0	0	License :: OSI Approved :: Ricoh Source Code Public License	13 :: 14 :: 193
192	2000032001	14	13	cvw	MITRE Collaborative Virtual Workspace License (CVW)	MITRE Collaborative Virtual Workspace License (CVW)	0	0	License :: OSI Approved :: MITRE Collaborative Virtual Workspace License (CVW)	13 :: 14 :: 192
191	2000032001	14	13	ibm	IBM Public License	IBM Public License	0	0	License :: OSI Approved :: IBM Public License	13 :: 14 :: 191
190	2000032001	14	13	qpl	QT Public License (QPL)	QT Public License	0	0	License :: OSI Approved :: QT Public License (QPL)	13 :: 14 :: 190
189	2000032001	14	13	mpl	Mozilla Public License (MPL)	Mozilla Public License (MPL)	0	0	License :: OSI Approved :: Mozilla Public License (MPL)	13 :: 14 :: 189
305	2001041701	189	13	mpl11	Mozilla Public License 1.1	Mozilla Public License 1.1	0	0	License :: OSI Approved :: Mozilla Public License (MPL) :: Mozilla Public License 1.1	13 :: 14 :: 189 :: 305
199	2000032101	0	0	os	Operating System	What operating system the program requires to run, if any.	0	0	Operating System	199
200	2000032101	199	199	posix	POSIX	POSIX plus standard Berkeley socket facilities. Don't list a more specific OS unless your program requires it.	0	0	Operating System :: POSIX	199 :: 200
201	2000032101	200	199	linux	Linux	Any version of Linux. Don't specify a subcategory unless the program requires a particular distribution.	0	0	Operating System :: POSIX :: Linux	199 :: 200 :: 201
202	2000032101	200	199	bsd	BSD	Any variant of BSD. Don't specify a subcategory unless the program requires a particular BSD flavor.	0	0	Operating System :: POSIX :: BSD	199 :: 200 :: 202
203	2000041101	202	199	freebsd	FreeBSD	FreeBSD	0	0	Operating System :: POSIX :: BSD :: FreeBSD	199 :: 200 :: 202 :: 203
204	2000032101	202	199	netbsd	NetBSD	NetBSD	0	0	Operating System :: POSIX :: BSD :: NetBSD	199 :: 200 :: 202 :: 204
205	2000032101	202	199	openbsd	OpenBSD	OpenBSD	0	0	Operating System :: POSIX :: BSD :: OpenBSD	199 :: 200 :: 202 :: 205
206	2000032101	202	199	bsdos	BSD/OS	BSD/OS	0	0	Operating System :: POSIX :: BSD :: BSD/OS	199 :: 200 :: 202 :: 206
207	2000032101	200	199	sun	SunOS/Solaris	Any Sun Microsystems OS.	0	0	Operating System :: POSIX :: SunOS/Solaris	199 :: 200 :: 207
208	2000032101	200	199	sco	SCO	SCO	0	0	Operating System :: POSIX :: SCO	199 :: 200 :: 208
209	2000032101	200	199	hpux	HP-UX	HP-UX	0	0	Operating System :: POSIX :: HP-UX	199 :: 200 :: 209
210	2000032101	200	199	aix	AIX	AIX	0	0	Operating System :: POSIX :: AIX	199 :: 200 :: 210
211	2000032101	200	199	irix	IRIX	IRIX	0	0	Operating System :: POSIX :: IRIX	199 :: 200 :: 211
212	2000032101	200	199	other	Other	Other specific POSIX OS, specified in description.	0	0	Operating System :: POSIX :: Other	199 :: 200 :: 212
282	2000121901	18	18	Sociology	Sociology	Social / Informational - Family / etc.	0	0	Topic :: Sociology	18 :: 282
214	2000032101	199	199	microsoft	Microsoft	Microsoft operating systems.	0	0	Operating System :: Microsoft	199 :: 214
215	2000032101	214	199	msdos	MS-DOS	Microsoft Disk Operating System (DOS)	0	0	Operating System :: Microsoft :: MS-DOS	199 :: 214 :: 215
216	2000032101	214	199	windows	Windows	Windows software, not specific to any particular version of Windows.	0	0	Operating System :: Microsoft :: Windows	199 :: 214 :: 216
217	2000032101	216	199	win31	Windows 3.1 or Earlier	Windows 3.1 or Earlier	0	0	Operating System :: Microsoft :: Windows :: Windows 3.1 or Earlier	199 :: 214 :: 216 :: 217
218	2000032101	216	199	win95	Windows 95/98/2000	Windows 95, Windows 98, and Windows 2000.	0	0	Operating System :: Microsoft :: Windows :: Windows 95/98/2000	199 :: 214 :: 216 :: 218
219	2000041101	216	199	winnt	Windows NT/2000	Windows NT and Windows 2000.	0	0	Operating System :: Microsoft :: Windows :: Windows NT/2000	199 :: 214 :: 216 :: 219
220	2000032101	199	199	os2	OS/2	OS/2	0	0	Operating System :: OS/2	199 :: 220
221	2000032101	199	199	macos	MacOS	MacOS	0	0	Operating System :: MacOS	199 :: 221
222	2000032101	216	199	wince	Windows CE	Windows CE	0	0	Operating System :: Microsoft :: Windows :: Windows CE	199 :: 214 :: 216 :: 222
223	2000032101	199	199	palmos	PalmOS	PalmOS (for Palm Pilot)	0	0	Operating System :: PalmOS	199 :: 223
224	2000032101	199	199	beos	BeOS	BeOS	0	0	Operating System :: BeOS	199 :: 224
225	2000032101	0	0	environment	Environment	Run-time environment required for this program.	0	0	Environment	225
226	2000041101	225	225	console	Console (Text Based)	Console-based programs.	0	0	Environment :: Console (Text Based)	225 :: 226
227	2000032401	226	225	curses	Curses	Curses-based software.	0	0	Environment :: Console (Text Based) :: Curses	225 :: 226 :: 227
228	2000040701	226	225	newt	Newt	Newt	0	0	Environment :: Console (Text Based) :: Newt	225 :: 226 :: 228
229	2000040701	225	225	x11	X11 Applications	Programs that run in an X windowing environment.	0	0	Environment :: X11 Applications	225 :: 229
230	2000040701	225	225	win32	Win32 (MS Windows)	Programs designed to run in a graphical Microsoft Windows environment.	0	0	Environment :: Win32 (MS Windows)	225 :: 230
231	2000040701	229	225	gnome	Gnome	Programs designed to run in a Gnome environment.	0	0	Environment :: X11 Applications :: Gnome	225 :: 229 :: 231
232	2000040701	229	225	kde	KDE	Programs designed to run in a KDE environment.	0	0	Environment :: X11 Applications :: KDE	225 :: 229 :: 232
233	2000040701	225	225	other	Other Environment	Programs designed to run in an environment other than one listed.	0	0	Environment :: Other Environment	225 :: 233
283	2000121901	282	18	History	History	History / Informational	0	0	Topic :: Sociology :: History	18 :: 282 :: 283
235	2000041001	199	199	independent	OS Independent	This software does not depend on any particular operating system.	0	0	Operating System :: OS Independent	199 :: 235
236	2000040701	199	199	other	Other OS	Program is designe for a nonlisted operating system.	0	0	Operating System :: Other OS	199 :: 236
237	2000041001	225	225	web	Web Environment	This software is designed for a web environment.	0	0	Environment :: Web Environment	225 :: 237
238	2000041101	225	225	daemon	No Input/Output (Daemon)	This program has no input or output, but is intended to run in the background as a daemon.	0	0	Environment :: No Input/Output (Daemon)	225 :: 238
284	2000121901	282	18	Genealogy	Genealogy	Family History / Genealogy	0	0	Topic :: Sociology :: Genealogy	18 :: 282 :: 284
240	2000041301	200	199	gnuhurd	GNU Hurd	GNU Hurd	0	0	Operating System :: POSIX :: GNU Hurd	199 :: 200 :: 240
55	2000031601	18	18	desktop	Desktop Environment	Accessories, managers, and utilities for your GUI desktop.	0	0	Topic :: Desktop Environment	18 :: 55
58	2000031601	55	18	gnome	Gnome	Software for the Gnome desktop.	0	0	Topic :: Desktop Environment :: Gnome	18 :: 55 :: 58
57	2000031601	55	18	kde	K Desktop Environment (KDE)	Software for the KDE desktop.	0	0	Topic :: Desktop Environment :: K Desktop Environment (KDE)	18 :: 55 :: 57
61	2000031601	57	18	themes	Themes	Themes for KDE.	0	0	Topic :: Desktop Environment :: K Desktop Environment (KDE) :: Themes	18 :: 55 :: 57 :: 61
56	2000031601	55	18	windowmanagers	Window Managers	Programs that provide window control and application launching.	0	0	Topic :: Desktop Environment :: Window Managers	18 :: 55 :: 56
59	2000031601	56	18	enlightenment	Enlightenment	Software for the Enlightenment window manager.	0	0	Topic :: Desktop Environment :: Window Managers :: Enlightenment	18 :: 55 :: 56 :: 59
60	2000031601	59	18	themes	Themes	Themes for the Enlightenment window manager.	0	0	Topic :: Desktop Environment :: Window Managers :: Enlightenment :: Themes	18 :: 55 :: 56 :: 59 :: 60
62	2000031601	55	18	screensavers	Screen Savers	Screen savers and lockers.	0	0	Topic :: Desktop Environment :: Screen Savers	18 :: 55 :: 62
259	2000071401	45	18	codegen	Code Generators	Code Generators	0	0	Topic :: Software Development :: Code Generators	18 :: 45 :: 259
52	2000031601	45	18	versioncontrol	Version Control	Tools for managing multiple versions of evolving sources or documents.	0	0	Topic :: Software Development :: Version Control	18 :: 45 :: 52
160	2000032001	0	0	language	Programming Language	Language in which this program was written, or was meant to support.	0	0	Programming Language	160
161	2000032001	160	160	apl	APL	APL	0	0	Programming Language :: APL	160 :: 161
164	2000032001	160	160	c	C	C	0	0	Programming Language :: C	160 :: 164
162	2000032001	160	160	assembly	Assembly	Assembly-level programs. Platform specific.	0	0	Programming Language :: Assembly	160 :: 162
163	2000051001	160	160	ada	Ada	Ada	0	0	Programming Language :: Ada	160 :: 163
165	2000032001	160	160	cpp	C++	C++	0	0	Programming Language :: C++	160 :: 165
166	2000032401	160	160	eiffel	Eiffel	Eiffel	0	0	Programming Language :: Eiffel	160 :: 166
167	2000032001	160	160	euler	Euler	Euler	0	0	Programming Language :: Euler	160 :: 167
168	2000032001	160	160	forth	Forth	Forth	0	0	Programming Language :: Forth	160 :: 168
169	2000032001	160	160	fortran	Fortran	Fortran	0	0	Programming Language :: Fortran	160 :: 169
170	2000032001	160	160	lisp	Lisp	Lisp	0	0	Programming Language :: Lisp	160 :: 170
171	2000041101	160	160	logo	Logo	Logo	0	0	Programming Language :: Logo	160 :: 171
172	2000032001	160	160	ml	ML	ML	0	0	Programming Language :: ML	160 :: 172
173	2000032001	160	160	modula	Modula	Modula-2 or Modula-3	0	0	Programming Language :: Modula	160 :: 173
174	2000032001	160	160	objectivec	Objective C	Objective C	0	0	Programming Language :: Objective C	160 :: 174
175	2000032001	160	160	pascal	Pascal	Pascal	0	0	Programming Language :: Pascal	160 :: 175
176	2000032001	160	160	perl	Perl	Perl	0	0	Programming Language :: Perl	160 :: 176
177	2000032001	160	160	prolog	Prolog	Prolog	0	0	Programming Language :: Prolog	160 :: 177
178	2000032001	160	160	python	Python	Python	0	0	Programming Language :: Python	160 :: 178
179	2000032001	160	160	rexx	Rexx	Rexx	0	0	Programming Language :: Rexx	160 :: 179
180	2000032001	160	160	simula	Simula	Simula	0	0	Programming Language :: Simula	160 :: 180
181	2000032001	160	160	smalltalk	Smalltalk	Smalltalk	0	0	Programming Language :: Smalltalk	160 :: 181
182	2000032001	160	160	tcl	Tcl	Tcl	0	0	Programming Language :: Tcl	160 :: 182
183	2000032001	160	160	php	PHP	PHP	0	0	Programming Language :: PHP	160 :: 183
263	2000080401	160	160	euphoria	Euphoria	Euphoria programming language - http://www.rapideuphoria.com/	0	0	Programming Language :: Euphoria	160 :: 263
264	2000080701	160	160	erlang	Erlang	Erlang - developed by Ericsson - http://www.erlang.org/	0	0	Programming Language :: Erlang	160 :: 264
267	2000082001	160	160	zope	Zope	Zope Object Publishing	0	0	Programming Language :: Zope	160 :: 267
269	2000082801	160	160	asm	Assembly	ASM programming	0	0	Programming Language :: Assembly	160 :: 269
271	2000092001	160	160	csharp	C#	Microsoft's C++/Java Language	0	0	Programming Language :: C#	160 :: 271
273	2000102001	160	160	Pike	Pike	Pike, see http://pike.roxen.com/.	0	0	Programming Language :: Pike	160 :: 273
184	2000032001	160	160	asp	ASP	Active Server Pages	0	0	Programming Language :: ASP	160 :: 184
185	2000032001	160	160	shell	Unix Shell	Unix Shell	0	0	Programming Language :: Unix Shell	160 :: 185
186	2000032001	160	160	visualbasic	Visual Basic	Visual Basic	0	0	Programming Language :: Visual Basic	160 :: 186
198	2000032001	160	160	java	Java	Java	0	0	Programming Language :: Java	160 :: 198
213	2000032101	160	160	other	Other	Other programming language, specified in description.	0	0	Programming Language :: Other	160 :: 213
242	2000042701	160	160	scheme	Scheme	Scheme programming language.	0	0	Programming Language :: Scheme	160 :: 242
254	2000071101	160	160	plsql	PL/SQL	PL/SQL Programming Language	0	0	Programming Language :: PL/SQL	160 :: 254
255	2000071101	160	160	progress	PROGRESS	PROGRESS Programming Language	0	0	Programming Language :: PROGRESS	160 :: 255
258	2000071101	160	160	objectpascal	Object Pascal	Object Pascal	0	0	Programming Language :: Object Pascal	160 :: 258
261	2000072501	160	160	xbasic	XBasic	XBasic programming language	0	0	Programming Language :: XBasic	160 :: 261
262	2000073101	160	160	coldfusion	Cold Fusion	Cold Fusion Language	0	0	Programming Language :: Cold Fusion	160 :: 262
304	2001041701	189	13	mpl10	Mozilla Public License 1.0	Mozilla Public License 1.0	0	0	License :: OSI Approved :: Mozilla Public License (MPL) :: Mozilla Public License 1.0	13 :: 14 :: 189 :: 304
188	2000032001	14	13	mit	MIT/X Consortium License	MIT License, also the X Consortium License.	0	0	License :: OSI Approved :: MIT/X Consortium License	13 :: 14 :: 188
187	2000032001	14	13	bsd	BSD License	BSD License	0	0	License :: OSI Approved :: BSD License	13 :: 14 :: 187
17	2000032001	14	13	artistic	Artistic License	The Perl Artistic License	0	0	License :: OSI Approved :: Artistic License	13 :: 14 :: 17
16	2000050801	14	13	lgpl	GNU Lesser General Public License (LGPL)	GNU Lesser General Public License	0	0	License :: OSI Approved :: GNU Lesser General Public License (LGPL)	13 :: 14 :: 16
15	2000032001	14	13	gpl	GNU General Public License (GPL)	GNU General Public License.	0	0	License :: OSI Approved :: GNU General Public License (GPL)	13 :: 14 :: 15
280	2000110101	160	160	JavaScript	JavaScript	Java Scripting Language	0	0	Programming Language :: JavaScript	160 :: 280
281	2000111401	160	160	REBOL	REBOL	REBOL Programming Language	0	0	Programming Language :: REBOL	160 :: 281
265	2001032001	160	160	Delphi	Delphi/Kylix	Borland/Inprise Delphi or other Object-Pascal based languages	0	0	Programming Language :: Delphi/Kylix	160 :: 265
293	2001032001	160	160	ruby	Ruby	Ruby programming language	0	0	Programming Language :: Ruby	160 :: 293
45	2000031601	18	18	development	Software Development	Software used to aid software development.	0	0	Topic :: Software Development	18 :: 45
99	2000031601	18	18	multimedia	Multimedia	Graphics, sound, video, and multimedia.	0	0	Topic :: Multimedia	18 :: 99
296	2001041701	14	13	asl	Apache Software License	Apache Software License	0	0	License :: OSI Approved :: Apache Software License	13 :: 14 :: 296
274	2000102401	0	0	natlanguage	Natural Language	The oral/written language for the development and use of this software.	0	0	Natural Language	274
295	2001040601	274	274	russian	Russian	Projects having something to do with Russian Language	0	0	Natural Language :: Russian	274 :: 295
275	2000102401	274	274	english	English	English	0	0	Natural Language :: English	274 :: 275
276	2000102401	274	274	french	French	French	0	0	Natural Language :: French	274 :: 276
277	2000102401	274	274	spanish	Spanish	Spanish	0	0	Natural Language :: Spanish	274 :: 277
278	2000102601	274	274	japanese	Japanese	Projects using the Japanese language	0	0	Natural Language :: Japanese	274 :: 278
279	2000102601	274	274	german	German	Projects using the German language	0	0	Natural Language :: German	274 :: 279
298	2001041701	14	13	sissl	Sun Internet Standards Source License	Sun Internet Standards Source License	0	0	License :: OSI Approved :: Sun Internet Standards Source License	13 :: 14 :: 298
300	2001041701	14	13	josl	Jabber Open Source License	Jabber Open Source License	0	0	License :: OSI Approved :: Jabber Open Source License	13 :: 14 :: 300
302	2001041701	14	13	sleepycat	Sleepycat License	Sleepycat License	0	0	License :: OSI Approved :: Sleepycat License	13 :: 14 :: 302
\.


COPY "trove_group_link" FROM stdin;
\.


COPY "user_bookmarks" FROM stdin;
\.


COPY "user_diary" FROM stdin;
\.


COPY "user_diary_monitor" FROM stdin;
\.


COPY "user_group" FROM stdin;
\.


COPY "user_metric" FROM stdin;
\.


COPY "user_metric0" FROM stdin;
\.


COPY "user_preferences" FROM stdin;
\.


COPY "user_ratings" FROM stdin;
\.


COPY "users" FROM stdin;
2	noreply				D	/bin/bash		N	0	shell1	0	\N	0	0	\N	\N	0		GMT	1	0	\N	\N
100	None	noreply@sourceforge.net	*********34343	Nobody	A	/bin/bash		N	0	shell1	0	\N	0	0	\N	\N	0		GMT	1	0	\N	\N
\.


COPY "project_sums_agg" FROM stdin;
\.


COPY "prdb_dbs" FROM stdin;
\.


COPY "prdb_states" FROM stdin;
\.


COPY "prdb_types" FROM stdin;
\.


COPY "prweb_vhost" FROM stdin;
\.


COPY "artifact_group_list" FROM stdin;
100	1	Default	Default Data - Dont Edit	3	0	0		2592000	0	\N	\N	0	\N
\.


COPY "artifact_resolution" FROM stdin;
100	None
102	Accepted
103	Out of Date
104	Postponed
105	Rejected
\.


COPY "artifact_perm" FROM stdin;
\.


COPY "artifact_category" FROM stdin;
100	100	None	100
\.


COPY "artifact_group" FROM stdin;
100	100	None
\.


COPY "artifact_status" FROM stdin;
1	Open
2	Closed
3	Deleted
\.


COPY "artifact" FROM stdin;
\.


COPY "artifact_history" FROM stdin;
\.


COPY "artifact_file" FROM stdin;
\.


COPY "artifact_message" FROM stdin;
\.


COPY "artifact_monitor" FROM stdin;
\.


COPY "artifact_canned_responses" FROM stdin;
\.


COPY "artifact_counts_agg" FROM stdin;
100	0	0
\.


COPY "stats_site_pages_by_day" FROM stdin;
\.


COPY "massmail_queue" FROM stdin;
\.


COPY "stats_agg_site_by_group" FROM stdin;
\.


COPY "stats_project_metric" FROM stdin;
\.


COPY "stats_agg_logo_by_group" FROM stdin;
\.


COPY "stats_subd_pages" FROM stdin;
\.


COPY "stats_cvs_user" FROM stdin;
\.


COPY "stats_cvs_group" FROM stdin;
\.


COPY "stats_project_developers" FROM stdin;
\.


COPY "stats_project" FROM stdin;
\.


COPY "stats_site" FROM stdin;
\.


COPY "activity_log" FROM stdin;
\.


COPY "user_metric_history" FROM stdin;
\.


COPY "frs_dlstats_filetotal_agg" FROM stdin;
\.


COPY "stats_project_months" FROM stdin;
\.


COPY "stats_site_pages_by_month" FROM stdin;
\.


COPY "stats_site_months" FROM stdin;
\.


COPY "trove_agg" FROM stdin;
\.


COPY "trove_treesums" FROM stdin;
\.


COPY "frs_dlstats_file" FROM stdin;
\.


COPY "group_cvs_history" FROM stdin;
\.


COPY "themes" FROM stdin;
1	gforge	Default Theme	t
2	osx	OSX	t
\.


COPY "theme_prefs" FROM stdin;
\.


COPY "supported_languages" FROM stdin;
23	Smpl.Chinese	SimplifiedChinese.class	SimplifiedChinese	zn   
1	English	English.class	English	en   
2	Japanese	Japanese.class	Japanese	ja   
3	Hebrew	Hebrew.class	Hebrew	iw   
4	Spanish	Spanish.class	Spanish	es   
5	Thai	Thai.class	Thai	th   
6	German	German.class	German	de   
8	Italian	Italian.class	Italian	it   
9	Norwegian	Norwegian.class	Norwegian	no   
10	Swedish	Swedish.class	Swedish	sv   
11	Trad.Chinese	Chinese.class	Chinese	zh   
12	Dutch	Dutch.class	Dutch	nl   
13	Esperanto	Esperanto.class	Esperanto	eo   
14	Catalan	Catalan.class	Catalan	ca   
22	Korean	Korean.class	Korean	ko   
20	Bulgarian	Bulgarian.class	Bulgarian	bg   
19	Greek	Greek.class	Greek	el   
21	Indonesian	Indonesian.class	Indonesian	id   
15	Polish	Polish.class	Polish	pl   
18	Portuguese	Portuguese.class	Portuguese	pt   
17	Russian	Russian.class	Russian	ru   
7	French	French.class	French	fr   
16	Pt. Brazilian	PortugueseBrazilian.class	PortugueseBrazilian	pt_BR
24	Latin	Latin.class	Latin	la   
\.


COPY "skills_data_types" FROM stdin;
0	Unspecified
1	Project
2	Training
3	Proposal
4	Investigation
\.


COPY "skills_data" FROM stdin;
\.


COPY "project_category" FROM stdin;
100	1	None
\.


COPY "project_task_artifact" FROM stdin;
\.


COPY "project_group_forum" FROM stdin;
\.


COPY "project_group_doccat" FROM stdin;
\.


COPY "project_messages" FROM stdin;
\.


COPY "plugins" FROM stdin;
\.


COPY "group_plugin" FROM stdin;
\.


COPY "user_plugin" FROM stdin;
\.

CREATE INDEX db_images_group ON db_images USING btree (group_id);


CREATE INDEX doc_group_doc_group ON doc_data USING btree (doc_group);


CREATE INDEX doc_groups_group ON doc_groups USING btree (group_id);


CREATE INDEX filemodule_monitor_id ON filemodule_monitor USING btree (filemodule_id);


CREATE INDEX filemodulemonitor_userid ON filemodule_monitor USING btree (user_id);


CREATE INDEX forum_forumid_msgid ON forum USING btree (group_forum_id, msg_id);


CREATE INDEX forum_group_forum_id ON forum USING btree (group_forum_id);


CREATE INDEX forum_forumid_threadid_mostrece ON forum USING btree (group_forum_id, thread_id, most_recent_date);


CREATE INDEX forum_threadid_isfollowupto ON forum USING btree (thread_id, is_followup_to);


CREATE INDEX forum_forumid_isfollto_mostrece ON forum USING btree (group_forum_id, is_followup_to, most_recent_date);


CREATE INDEX forum_group_list_group_id ON forum_group_list USING btree (group_id);


CREATE INDEX forummonitoredforums_user ON forum_monitored_forums USING btree (user_id);


CREATE INDEX forum_monitor_combo_id ON forum_monitored_forums USING btree (forum_id, user_id);


CREATE INDEX forum_monitor_thread_id ON forum_monitored_forums USING btree (forum_id);


CREATE INDEX frs_file_date ON frs_file USING btree (post_date);


CREATE INDEX frs_file_release_id ON frs_file USING btree (release_id);


CREATE INDEX package_group_id ON frs_package USING btree (group_id);


CREATE INDEX frs_release_package ON frs_release USING btree (package_id);


CREATE INDEX group_history_group_id ON group_history USING btree (group_id);


CREATE UNIQUE INDEX group_unix_uniq ON groups USING btree (unix_group_name);


CREATE INDEX groups_type ON groups USING btree ("type");


CREATE INDEX groups_public ON groups USING btree (is_public);


CREATE INDEX groups_status ON groups USING btree (status);


CREATE INDEX mail_group_list_group ON mail_group_list USING btree (group_id);


CREATE INDEX news_bytes_group ON news_bytes USING btree (group_id);


CREATE INDEX news_bytes_approved ON news_bytes USING btree (is_approved);


CREATE INDEX news_bytes_forum ON news_bytes USING btree (forum_id);


CREATE INDEX news_group_date ON news_bytes USING btree (group_id, date);


CREATE INDEX news_approved_date ON news_bytes USING btree (is_approved, date);


CREATE INDEX people_job_group_id ON people_job USING btree (group_id);


CREATE INDEX project_assigned_to_assigned_to ON project_assigned_to USING btree (assigned_to_id);


CREATE INDEX project_assigned_to_task_id ON project_assigned_to USING btree (project_task_id);


CREATE INDEX project_is_dependent_on_task_id ON project_dependencies USING btree (is_dependent_on_task_id);


CREATE INDEX project_dependencies_task_id ON project_dependencies USING btree (project_task_id);


CREATE INDEX project_group_list_group_id ON project_group_list USING btree (group_id);


CREATE INDEX project_history_task_id ON project_history USING btree (project_task_id);


CREATE INDEX project_metric_group ON project_metric USING btree (group_id);


CREATE INDEX projecttask_projid_status ON project_task USING btree (group_project_id, status_id);


CREATE INDEX project_task_group_project_id ON project_task USING btree (group_project_id);


CREATE INDEX projectweeklymetric_ranking ON project_weekly_metric USING btree (ranking);


CREATE INDEX project_metric_weekly_group ON project_weekly_metric USING btree (group_id);


CREATE INDEX session_user_id ON "session" USING btree (user_id);


CREATE INDEX session_time ON "session" USING btree ("time");


CREATE INDEX snippet_language ON snippet USING btree ("language");


CREATE INDEX snippet_category ON snippet USING btree (category);


CREATE INDEX snippet_package_language ON snippet_package USING btree ("language");


CREATE INDEX snippet_package_category ON snippet_package USING btree (category);


CREATE INDEX snippet_package_item_pkg_ver ON snippet_package_item USING btree (snippet_package_version_id);


CREATE INDEX snippet_package_version_pkg_id ON snippet_package_version USING btree (snippet_package_id);


CREATE INDEX snippet_version_snippet_id ON snippet_version USING btree (snippet_id);


CREATE INDEX pages_by_day_day ON stats_agg_pages_by_day USING btree ("day");


CREATE INDEX survey_questions_group ON survey_questions USING btree (group_id);


CREATE INDEX survey_rating_aggregate_type_id ON survey_rating_aggregate USING btree ("type", id);


CREATE INDEX survey_rating_responses_user_ty ON survey_rating_response USING btree (user_id, "type", id);


CREATE INDEX survey_rating_responses_type_id ON survey_rating_response USING btree ("type", id);


CREATE INDEX survey_responses_group_id ON survey_responses USING btree (group_id);


CREATE INDEX survey_responses_user_survey_qu ON survey_responses USING btree (user_id, survey_id, question_id);


CREATE INDEX survey_responses_user_survey ON survey_responses USING btree (user_id, survey_id);


CREATE INDEX survey_responses_survey_questio ON survey_responses USING btree (survey_id, question_id);


CREATE INDEX surveys_group ON surveys USING btree (group_id);


CREATE INDEX parent_idx ON trove_cat USING btree (parent);


CREATE INDEX root_parent_idx ON trove_cat USING btree (root_parent);


CREATE INDEX version_idx ON trove_cat USING btree ("version");


CREATE INDEX trove_group_link_group_id ON trove_group_link USING btree (group_id);


CREATE INDEX trove_group_link_cat_id ON trove_group_link USING btree (trove_cat_id);


CREATE INDEX user_bookmark_user_id ON user_bookmarks USING btree (user_id);


CREATE INDEX user_diary_user ON user_diary USING btree (user_id);


CREATE INDEX user_diary_user_date ON user_diary USING btree (user_id, date_posted);


CREATE INDEX user_diary_date ON user_diary USING btree (date_posted);


CREATE INDEX user_diary_monitor_user ON user_diary_monitor USING btree (user_id);


CREATE INDEX user_diary_monitor_monitored_us ON user_diary_monitor USING btree (monitored_user);


CREATE INDEX user_group_group_id ON user_group USING btree (group_id);


CREATE INDEX project_flags_idx ON user_group USING btree (project_flags);


CREATE INDEX user_group_user_id ON user_group USING btree (user_id);


CREATE INDEX admin_flags_idx ON user_group USING btree (admin_flags);


CREATE INDEX forum_flags_idx ON user_group USING btree (forum_flags);


CREATE UNIQUE INDEX usergroup_uniq_groupid_userid ON user_group USING btree (group_id, user_id);


CREATE INDEX user_metric0_user_id ON user_metric0 USING btree (user_id);


CREATE INDEX user_pref_user_id ON user_preferences USING btree (user_id);


CREATE INDEX user_ratings_rated_by ON user_ratings USING btree (rated_by);


CREATE INDEX user_ratings_user_id ON user_ratings USING btree (user_id);


CREATE UNIQUE INDEX users_namename_uniq ON users USING btree (user_name);


CREATE INDEX users_status ON users USING btree (status);


CREATE INDEX users_user_pw ON users USING btree (user_pw);


CREATE INDEX projectsumsagg_groupid ON project_sums_agg USING btree (group_id);


CREATE UNIQUE INDEX idx_prdb_dbname ON prdb_dbs USING btree (dbname);


CREATE INDEX idx_vhost_groups ON prweb_vhost USING btree (group_id);


CREATE UNIQUE INDEX idx_vhost_hostnames ON prweb_vhost USING btree (vhost_name);


CREATE INDEX artgrouplist_groupid ON artifact_group_list USING btree (group_id);


CREATE INDEX artgrouplist_groupid_public ON artifact_group_list USING btree (group_id, is_public);


CREATE UNIQUE INDEX artperm_groupartifactid_userid ON artifact_perm USING btree (group_artifact_id, user_id);


CREATE INDEX artperm_groupartifactid ON artifact_perm USING btree (group_artifact_id);


CREATE INDEX artcategory_groupartifactid ON artifact_category USING btree (group_artifact_id);


CREATE INDEX artgroup_groupartifactid ON artifact_group USING btree (group_artifact_id);


CREATE INDEX art_groupartid ON artifact USING btree (group_artifact_id);


CREATE INDEX art_groupartid_statusid ON artifact USING btree (group_artifact_id, status_id);


CREATE INDEX art_groupartid_assign ON artifact USING btree (group_artifact_id, assigned_to);


CREATE INDEX art_groupartid_submit ON artifact USING btree (group_artifact_id, submitted_by);


CREATE INDEX art_submit_status ON artifact USING btree (submitted_by, status_id);


CREATE INDEX art_assign_status ON artifact USING btree (assigned_to, status_id);


CREATE INDEX art_groupartid_artifactid ON artifact USING btree (group_artifact_id, artifact_id);


CREATE INDEX arthistory_artid ON artifact_history USING btree (artifact_id);


CREATE INDEX arthistory_artid_entrydate ON artifact_history USING btree (artifact_id, entrydate);


CREATE INDEX artfile_artid ON artifact_file USING btree (artifact_id);


CREATE INDEX artfile_artid_adddate ON artifact_file USING btree (artifact_id, adddate);


CREATE INDEX artmessage_artid ON artifact_message USING btree (artifact_id);


CREATE INDEX artmessage_artid_adddate ON artifact_message USING btree (artifact_id, adddate);


CREATE INDEX artmonitor_artifactid ON artifact_monitor USING btree (artifact_id);


CREATE INDEX artifactcannedresponses_groupid ON artifact_canned_responses USING btree (group_artifact_id);


CREATE INDEX artifactcountsagg_groupartid ON artifact_counts_agg USING btree (group_artifact_id);


CREATE UNIQUE INDEX statssitepgsbyday_oid ON stats_site_pages_by_day USING btree (oid);


CREATE INDEX statssitepagesbyday_month_day ON stats_site_pages_by_day USING btree ("month", "day");


CREATE UNIQUE INDEX statsaggsitebygrp_oid ON stats_agg_site_by_group USING btree (oid);


CREATE UNIQUE INDEX statssitebygroup_month_day_grou ON stats_agg_site_by_group USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statsprojectmetric_oid ON stats_project_metric USING btree (oid);


CREATE UNIQUE INDEX statsprojectmetric_month_day_gr ON stats_project_metric USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statsagglogobygrp_oid ON stats_agg_logo_by_group USING btree (oid);


CREATE UNIQUE INDEX statslogobygroup_month_day_grou ON stats_agg_logo_by_group USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statssubdpages_oid ON stats_subd_pages USING btree (oid);


CREATE UNIQUE INDEX statssubdpages_month_day_group ON stats_subd_pages USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statscvsgrp_oid ON stats_cvs_group USING btree (oid);


CREATE UNIQUE INDEX statscvsgroup_month_day_group ON stats_cvs_group USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statsprojectdevelop_oid ON stats_project_developers USING btree (oid);


CREATE UNIQUE INDEX statsprojectdev_month_day_group ON stats_project_developers USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statsproject_oid ON stats_project USING btree (oid);


CREATE UNIQUE INDEX statsproject_month_day_group ON stats_project USING btree ("month", "day", group_id);


CREATE UNIQUE INDEX statssite_oid ON stats_site USING btree (oid);


CREATE UNIQUE INDEX statssite_month_day ON stats_site USING btree ("month", "day");


CREATE INDEX user_metric_history_date_userid ON user_metric_history USING btree ("month", "day", user_id);


CREATE INDEX frsdlfiletotal_fileid ON frs_dlstats_filetotal_agg USING btree (file_id);


CREATE INDEX statsprojectmonths_groupid ON stats_project_months USING btree (group_id);


CREATE INDEX statsprojectmonths_groupid_mont ON stats_project_months USING btree (group_id, "month");


CREATE INDEX statssitemonths_month ON stats_site_months USING btree ("month");


CREATE INDEX troveagg_trovecatid ON trove_agg USING btree (trove_cat_id);


CREATE INDEX troveagg_trovecatid_ranking ON trove_agg USING btree (trove_cat_id, ranking);


CREATE UNIQUE INDEX group_cvs_history_id_key ON group_cvs_history USING btree (id);


CREATE INDEX groupcvshistory_groupid ON group_cvs_history USING btree (group_id);


CREATE UNIQUE INDEX themes_theme_id_key ON themes USING btree (theme_id);


CREATE INDEX themeprefs_userid ON theme_prefs USING btree (user_id);


CREATE UNIQUE INDEX supported_langu_language_id_key ON supported_languages USING btree (language_id);


CREATE UNIQUE INDEX project_categor_category_id_key ON project_category USING btree (category_id);


CREATE INDEX projectcategory_groupprojectid ON project_category USING btree (group_project_id);


CREATE INDEX projecttaskartifact_projecttask ON project_task_artifact USING btree (project_task_id);


CREATE INDEX projecttaskartifact_artifactid ON project_task_artifact USING btree (artifact_id);


CREATE INDEX projectgroupforum_groupprojecti ON project_group_forum USING btree (group_project_id);


CREATE INDEX projectgroupforum_groupforumid ON project_group_forum USING btree (group_forum_id);


CREATE INDEX projectgroupdoccat_groupproject ON project_group_forum USING btree (group_project_id);


CREATE INDEX projectgroupdoccat_groupgroupid ON project_group_doccat USING btree (doc_group_id);


CREATE UNIQUE INDEX project_messa_project_messa_key ON project_messages USING btree (project_message_id);


CREATE UNIQUE INDEX plugins_plugin_name_key ON plugins USING btree (plugin_name);


CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER INSERT OR UPDATE ON "user_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER INSERT OR UPDATE ON "user_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER DELETE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER INSERT OR UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER DELETE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER INSERT OR UPDATE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER DELETE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER UPDATE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER INSERT OR UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_monitor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER INSERT OR UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER INSERT OR UPDATE ON "artifact_perm"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER INSERT OR UPDATE ON "artifact_perm"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER INSERT OR UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER INSERT OR UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER INSERT OR UPDATE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');


CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER DELETE ON "artifact_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER UPDATE ON "artifact_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER DELETE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER DELETE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER UPDATE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');


CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER DELETE ON "artifact_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');


CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER UPDATE ON "artifact_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');


CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_history"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER INSERT OR UPDATE ON "artifact_history"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_message"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact_message"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_monitor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');


CREATE TRIGGER "artifactgrouplist_insert_trig" AFTER INSERT ON "artifact_group_list"  FOR EACH ROW EXECUTE PROCEDURE "artifactgrouplist_insert_agg" ();


CREATE TRIGGER "artifactgroup_update_trig" AFTER UPDATE ON "artifact"  FOR EACH ROW EXECUTE PROCEDURE "artifactgroup_update_agg" ();


CREATE TRIGGER "forumgrouplist_insert_trig" AFTER INSERT ON "forum_group_list"  FOR EACH ROW EXECUTE PROCEDURE "forumgrouplist_insert_agg" ();


CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');


CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER DELETE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');


CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');


CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');


CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER DELETE ON "frs_filetype"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');


CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER UPDATE ON "frs_filetype"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');


CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');


CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER DELETE ON "frs_processor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');


CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER UPDATE ON "frs_processor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');


CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER INSERT OR UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER INSERT OR UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER DELETE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER UPDATE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');


CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER DELETE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');


CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');


CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER DELETE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER UPDATE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');


CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');


CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');


CREATE CONSTRAINT TRIGGER "tgl_group_id_fk" AFTER INSERT OR UPDATE ON "trove_group_link"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('tgl_group_id_fk', 'trove_group_link', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "tgl_group_id_fk" AFTER DELETE ON "groups"  FROM "trove_group_link" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('tgl_group_id_fk', 'trove_group_link', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "tgl_group_id_fk" AFTER UPDATE ON "groups"  FROM "trove_group_link" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('tgl_group_id_fk', 'trove_group_link', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "tgl_cat_id_fk" AFTER INSERT OR UPDATE ON "trove_group_link"  FROM "trove_cat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('tgl_cat_id_fk', 'trove_group_link', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "tgl_cat_id_fk" AFTER DELETE ON "trove_cat"  FROM "trove_group_link" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('tgl_cat_id_fk', 'trove_group_link', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "tgl_cat_id_fk" AFTER UPDATE ON "trove_cat"  FROM "trove_group_link" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('tgl_cat_id_fk', 'trove_group_link', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_agg_cat_id_fk" AFTER INSERT OR UPDATE ON "trove_agg"  FROM "trove_cat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('trove_agg_cat_id_fk', 'trove_agg', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_agg_cat_id_fk" AFTER DELETE ON "trove_cat"  FROM "trove_agg" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('trove_agg_cat_id_fk', 'trove_agg', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_agg_cat_id_fk" AFTER UPDATE ON "trove_cat"  FROM "trove_agg" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('trove_agg_cat_id_fk', 'trove_agg', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_agg_group_id_fk" AFTER INSERT OR UPDATE ON "trove_agg"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('trove_agg_group_id_fk', 'trove_agg', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "trove_agg_group_id_fk" AFTER DELETE ON "groups"  FROM "trove_agg" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('trove_agg_group_id_fk', 'trove_agg', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "trove_agg_group_id_fk" AFTER UPDATE ON "groups"  FROM "trove_agg" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('trove_agg_group_id_fk', 'trove_agg', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "trove_treesums_cat_id_fk" AFTER INSERT OR UPDATE ON "trove_treesums"  FROM "trove_cat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('trove_treesums_cat_id_fk', 'trove_treesums', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_treesums_cat_id_fk" AFTER DELETE ON "trove_cat"  FROM "trove_treesums" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('trove_treesums_cat_id_fk', 'trove_treesums', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "trove_treesums_cat_id_fk" AFTER UPDATE ON "trove_cat"  FROM "trove_treesums" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('trove_treesums_cat_id_fk', 'trove_treesums', 'trove_cat', 'FULL', 'trove_cat_id', 'trove_cat_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER INSERT OR UPDATE ON "users"  FROM "supported_languages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER DELETE ON "supported_languages"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER UPDATE ON "supported_languages"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "docdata_languageid_fk" AFTER INSERT OR UPDATE ON "doc_data"  FROM "supported_languages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('docdata_languageid_fk', 'doc_data', 'supported_languages', 'FULL', 'language_id', 'language_id');


CREATE CONSTRAINT TRIGGER "docdata_languageid_fk" AFTER DELETE ON "supported_languages"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('docdata_languageid_fk', 'doc_data', 'supported_languages', 'FULL', 'language_id', 'language_id');


CREATE CONSTRAINT TRIGGER "docdata_languageid_fk" AFTER UPDATE ON "supported_languages"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('docdata_languageid_fk', 'doc_data', 'supported_languages', 'FULL', 'language_id', 'language_id');


CREATE CONSTRAINT TRIGGER "forumgrouplist_groupid" AFTER INSERT OR UPDATE ON "forum_group_list"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forumgrouplist_groupid', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forumgrouplist_groupid" AFTER DELETE ON "groups"  FROM "forum_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('forumgrouplist_groupid', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forumgrouplist_groupid" AFTER UPDATE ON "groups"  FROM "forum_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forumgrouplist_groupid', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "forum_groupforumid" AFTER INSERT OR UPDATE ON "forum"  FROM "forum_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_groupforumid', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_groupforumid" AFTER DELETE ON "forum_group_list"  FROM "forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('forum_groupforumid', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_groupforumid" AFTER UPDATE ON "forum_group_list"  FROM "forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_groupforumid', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "forum_userid" AFTER INSERT OR UPDATE ON "forum"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_userid', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_userid" AFTER DELETE ON "users"  FROM "forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_userid', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "forum_userid" AFTER UPDATE ON "users"  FROM "forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_userid', 'forum', 'users', 'FULL', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER INSERT OR UPDATE ON "skills_data"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('<unnamed>', 'skills_data', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER DELETE ON "users"  FROM "skills_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('<unnamed>', 'skills_data', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER UPDATE ON "users"  FROM "skills_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('<unnamed>', 'skills_data', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER INSERT OR UPDATE ON "skills_data"  FROM "skills_data_types" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('<unnamed>', 'skills_data', 'skills_data_types', 'UNSPECIFIED', 'type', 'type_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER DELETE ON "skills_data_types"  FROM "skills_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('<unnamed>', 'skills_data', 'skills_data_types', 'UNSPECIFIED', 'type', 'type_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER UPDATE ON "skills_data_types"  FROM "skills_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('<unnamed>', 'skills_data', 'skills_data_types', 'UNSPECIFIED', 'type', 'type_id');


CREATE CONSTRAINT TRIGGER "themeprefs_userid" AFTER INSERT OR UPDATE ON "theme_prefs"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('themeprefs_userid', 'theme_prefs', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "themeprefs_userid" AFTER DELETE ON "users"  FROM "theme_prefs" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('themeprefs_userid', 'theme_prefs', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "themeprefs_userid" AFTER UPDATE ON "users"  FROM "theme_prefs" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('themeprefs_userid', 'theme_prefs', 'users', 'UNSPECIFIED', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "themeprefs_themeid" AFTER INSERT OR UPDATE ON "theme_prefs"  FROM "themes" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('themeprefs_themeid', 'theme_prefs', 'themes', 'UNSPECIFIED', 'user_theme', 'theme_id');


CREATE CONSTRAINT TRIGGER "themeprefs_themeid" AFTER DELETE ON "themes"  FROM "theme_prefs" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('themeprefs_themeid', 'theme_prefs', 'themes', 'UNSPECIFIED', 'user_theme', 'theme_id');


CREATE CONSTRAINT TRIGGER "themeprefs_themeid" AFTER UPDATE ON "themes"  FROM "theme_prefs" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('themeprefs_themeid', 'theme_prefs', 'themes', 'UNSPECIFIED', 'user_theme', 'theme_id');


CREATE CONSTRAINT TRIGGER "projecttask_groupprojectid_fk" AFTER INSERT OR UPDATE ON "project_task"  FROM "project_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projecttask_groupprojectid_fk', 'project_task', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projecttask_groupprojectid_fk" AFTER DELETE ON "project_group_list"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projecttask_groupprojectid_fk', 'project_task', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projecttask_groupprojectid_fk" AFTER UPDATE ON "project_group_list"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projecttask_groupprojectid_fk', 'project_task', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projcat_projgroupid_fk" AFTER INSERT OR UPDATE ON "project_category"  FROM "project_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projcat_projgroupid_fk', 'project_category', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projcat_projgroupid_fk" AFTER DELETE ON "project_group_list"  FROM "project_category" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projcat_projgroupid_fk', 'project_category', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projcat_projgroupid_fk" AFTER UPDATE ON "project_group_list"  FROM "project_category" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projcat_projgroupid_fk', 'project_category', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER INSERT OR UPDATE ON "project_task"  FROM "project_category" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('<unnamed>', 'project_task', 'project_category', 'UNSPECIFIED', 'category_id', 'category_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER DELETE ON "project_category"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('<unnamed>', 'project_task', 'project_category', 'UNSPECIFIED', 'category_id', 'category_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER UPDATE ON "project_category"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('<unnamed>', 'project_task', 'project_category', 'UNSPECIFIED', 'category_id', 'category_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_projtaskid_fk" AFTER INSERT OR UPDATE ON "project_task_artifact"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projtaskartifact_projtaskid_fk', 'project_task_artifact', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_projtaskid_fk" AFTER DELETE ON "project_task"  FROM "project_task_artifact" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projtaskartifact_projtaskid_fk', 'project_task_artifact', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_projtaskid_fk" AFTER UPDATE ON "project_task"  FROM "project_task_artifact" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projtaskartifact_projtaskid_fk', 'project_task_artifact', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_artifactid_fk" AFTER INSERT OR UPDATE ON "project_task_artifact"  FROM "artifact" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projtaskartifact_artifactid_fk', 'project_task_artifact', 'artifact', 'UNSPECIFIED', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_artifactid_fk" AFTER DELETE ON "artifact"  FROM "project_task_artifact" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projtaskartifact_artifactid_fk', 'project_task_artifact', 'artifact', 'UNSPECIFIED', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "projtaskartifact_artifactid_fk" AFTER UPDATE ON "artifact"  FROM "project_task_artifact" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projtaskartifact_artifactid_fk', 'project_task_artifact', 'artifact', 'UNSPECIFIED', 'artifact_id', 'artifact_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_projgroupid_fk" AFTER INSERT OR UPDATE ON "project_group_forum"  FROM "project_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projgroupforum_projgroupid_fk', 'project_group_forum', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_projgroupid_fk" AFTER DELETE ON "project_group_list"  FROM "project_group_forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projgroupforum_projgroupid_fk', 'project_group_forum', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_projgroupid_fk" AFTER UPDATE ON "project_group_list"  FROM "project_group_forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projgroupforum_projgroupid_fk', 'project_group_forum', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_groupforumid_fk" AFTER INSERT OR UPDATE ON "project_group_forum"  FROM "forum_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projgroupforum_groupforumid_fk', 'project_group_forum', 'forum_group_list', 'UNSPECIFIED', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_groupforumid_fk" AFTER DELETE ON "forum_group_list"  FROM "project_group_forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projgroupforum_groupforumid_fk', 'project_group_forum', 'forum_group_list', 'UNSPECIFIED', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "projgroupforum_groupforumid_fk" AFTER UPDATE ON "forum_group_list"  FROM "project_group_forum" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projgroupforum_groupforumid_fk', 'project_group_forum', 'forum_group_list', 'UNSPECIFIED', 'group_forum_id', 'group_forum_id');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_projgroupid_fk" AFTER INSERT OR UPDATE ON "project_group_doccat"  FROM "project_group_list" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projgroupdoccat_projgroupid_fk', 'project_group_doccat', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_projgroupid_fk" AFTER DELETE ON "project_group_list"  FROM "project_group_doccat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projgroupdoccat_projgroupid_fk', 'project_group_doccat', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_projgroupid_fk" AFTER UPDATE ON "project_group_list"  FROM "project_group_doccat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projgroupdoccat_projgroupid_fk', 'project_group_doccat', 'project_group_list', 'UNSPECIFIED', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_docgroupid_fk" AFTER INSERT OR UPDATE ON "project_group_doccat"  FROM "doc_groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('projgroupdoccat_docgroupid_fk', 'project_group_doccat', 'doc_groups', 'UNSPECIFIED', 'doc_group_id', 'doc_group');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_docgroupid_fk" AFTER DELETE ON "doc_groups"  FROM "project_group_doccat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('projgroupdoccat_docgroupid_fk', 'project_group_doccat', 'doc_groups', 'UNSPECIFIED', 'doc_group_id', 'doc_group');


CREATE CONSTRAINT TRIGGER "projgroupdoccat_docgroupid_fk" AFTER UPDATE ON "doc_groups"  FROM "project_group_doccat" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('projgroupdoccat_docgroupid_fk', 'project_group_doccat', 'doc_groups', 'UNSPECIFIED', 'doc_group_id', 'doc_group');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER INSERT OR UPDATE ON "project_messages"  FROM "project_task" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('<unnamed>', 'project_messages', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER DELETE ON "project_task"  FROM "project_messages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('<unnamed>', 'project_messages', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER UPDATE ON "project_task"  FROM "project_messages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('<unnamed>', 'project_messages', 'project_task', 'UNSPECIFIED', 'project_task_id', 'project_task_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER INSERT OR UPDATE ON "project_messages"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('<unnamed>', 'project_messages', 'users', 'UNSPECIFIED', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER DELETE ON "users"  FROM "project_messages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('<unnamed>', 'project_messages', 'users', 'UNSPECIFIED', 'posted_by', 'user_id');


CREATE CONSTRAINT TRIGGER "<unnamed>" AFTER UPDATE ON "users"  FROM "project_messages" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('<unnamed>', 'project_messages', 'users', 'UNSPECIFIED', 'posted_by', 'user_id');


CREATE TRIGGER "projtask_update_depend_trig" AFTER UPDATE ON "project_task"  FOR EACH ROW EXECUTE PROCEDURE "projtask_update_depend" ();


CREATE TRIGGER "projtask_insert_depend_trig" BEFORE INSERT OR UPDATE ON "project_task"  FOR EACH ROW EXECUTE PROCEDURE "projtask_insert_depend" ();


CREATE CONSTRAINT TRIGGER "docdata_groupid" AFTER INSERT OR UPDATE ON "doc_data"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('docdata_groupid', 'doc_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "docdata_groupid" AFTER DELETE ON "groups"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('docdata_groupid', 'doc_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "docdata_groupid" AFTER UPDATE ON "groups"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('docdata_groupid', 'doc_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "docdata_docgroupid" AFTER INSERT OR UPDATE ON "doc_data"  FROM "doc_groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('docdata_docgroupid', 'doc_data', 'doc_groups', 'UNSPECIFIED', 'doc_group', 'doc_group');


CREATE CONSTRAINT TRIGGER "docdata_docgroupid" AFTER DELETE ON "doc_groups"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('docdata_docgroupid', 'doc_data', 'doc_groups', 'UNSPECIFIED', 'doc_group', 'doc_group');


CREATE CONSTRAINT TRIGGER "docdata_docgroupid" AFTER UPDATE ON "doc_groups"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('docdata_docgroupid', 'doc_data', 'doc_groups', 'UNSPECIFIED', 'doc_group', 'doc_group');


CREATE CONSTRAINT TRIGGER "docdata_stateid" AFTER INSERT OR UPDATE ON "doc_data"  FROM "doc_states" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('docdata_stateid', 'doc_data', 'doc_states', 'UNSPECIFIED', 'stateid', 'stateid');


CREATE CONSTRAINT TRIGGER "docdata_stateid" AFTER DELETE ON "doc_states"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('docdata_stateid', 'doc_data', 'doc_states', 'UNSPECIFIED', 'stateid', 'stateid');


CREATE CONSTRAINT TRIGGER "docdata_stateid" AFTER UPDATE ON "doc_states"  FROM "doc_data" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('docdata_stateid', 'doc_data', 'doc_states', 'UNSPECIFIED', 'stateid', 'stateid');


CREATE CONSTRAINT TRIGGER "docgroups_groupid" AFTER INSERT OR UPDATE ON "doc_groups"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('docgroups_groupid', 'doc_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "docgroups_groupid" AFTER DELETE ON "groups"  FROM "doc_groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_cascade_del" ('docgroups_groupid', 'doc_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "docgroups_groupid" AFTER UPDATE ON "groups"  FROM "doc_groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('docgroups_groupid', 'doc_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


CREATE TRIGGER "frs_file_insert_trig" AFTER INSERT ON "frs_file"  FOR EACH ROW EXECUTE PROCEDURE "frs_dlstats_filetotal_insert_ag" ();


CREATE CONSTRAINT TRIGGER "group_plugin_group_id_fk" AFTER INSERT OR UPDATE ON "group_plugin"  FROM "groups" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('group_plugin_group_id_fk', 'group_plugin', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "group_plugin_group_id_fk" AFTER DELETE ON "groups"  FROM "group_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('group_plugin_group_id_fk', 'group_plugin', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "group_plugin_group_id_fk" AFTER UPDATE ON "groups"  FROM "group_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('group_plugin_group_id_fk', 'group_plugin', 'groups', 'FULL', 'group_id', 'group_id');


CREATE CONSTRAINT TRIGGER "group_plugin_plugin_id_fk" AFTER INSERT OR UPDATE ON "group_plugin"  FROM "plugins" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('group_plugin_plugin_id_fk', 'group_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE CONSTRAINT TRIGGER "group_plugin_plugin_id_fk" AFTER DELETE ON "plugins"  FROM "group_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('group_plugin_plugin_id_fk', 'group_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE CONSTRAINT TRIGGER "group_plugin_plugin_id_fk" AFTER UPDATE ON "plugins"  FROM "group_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('group_plugin_plugin_id_fk', 'group_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE CONSTRAINT TRIGGER "user_plugin_user_id_fk" AFTER INSERT OR UPDATE ON "user_plugin"  FROM "users" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_plugin_user_id_fk', 'user_plugin', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_plugin_user_id_fk" AFTER DELETE ON "users"  FROM "user_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_plugin_user_id_fk', 'user_plugin', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_plugin_user_id_fk" AFTER UPDATE ON "users"  FROM "user_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_plugin_user_id_fk', 'user_plugin', 'users', 'FULL', 'user_id', 'user_id');


CREATE CONSTRAINT TRIGGER "user_plugin_plugin_id_fk" AFTER INSERT OR UPDATE ON "user_plugin"  FROM "plugins" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_plugin_plugin_id_fk', 'user_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE CONSTRAINT TRIGGER "user_plugin_plugin_id_fk" AFTER DELETE ON "plugins"  FROM "user_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_plugin_plugin_id_fk', 'user_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE CONSTRAINT TRIGGER "user_plugin_plugin_id_fk" AFTER UPDATE ON "plugins"  FROM "user_plugin" NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_plugin_plugin_id_fk', 'user_plugin', 'plugins', 'FULL', 'plugin_id', 'plugin_id');


CREATE TRIGGER "surveys_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "surveys"  FOR EACH ROW EXECUTE PROCEDURE "project_sums" ('surv');


CREATE TRIGGER "mail_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "mail_group_list"  FOR EACH ROW EXECUTE PROCEDURE "project_sums" ('mail');


CREATE TRIGGER "fmsg_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "forum"  FOR EACH ROW EXECUTE PROCEDURE "project_sums" ('fmsg');


CREATE TRIGGER "fora_agg_trig" AFTER INSERT OR DELETE OR UPDATE ON "forum_group_list"  FOR EACH ROW EXECUTE PROCEDURE "project_sums" ('fora');


CREATE RULE forum_insert_agg AS ON INSERT TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count + 1) WHERE (forum_agg_msg_count.group_forum_id = new.group_forum_id);

CREATE RULE forum_delete_agg AS ON DELETE TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count - 1) WHERE (forum_agg_msg_count.group_forum_id = old.group_forum_id);

CREATE RULE artifact_insert_agg AS ON INSERT TO artifact DO UPDATE artifact_counts_agg SET count = (artifact_counts_agg.count + 1), open_count = (artifact_counts_agg.open_count + 1) WHERE (artifact_counts_agg.group_artifact_id = new.group_artifact_id);

CREATE RULE frs_dlstats_file_rule AS ON INSERT TO frs_dlstats_file DO UPDATE frs_dlstats_filetotal_agg SET downloads = (frs_dlstats_filetotal_agg.downloads + 1) WHERE (frs_dlstats_filetotal_agg.file_id = new.file_id);

SELECT setval ('"canned_responses_pk_seq"', 1, false);


SELECT setval ('"db_images_pk_seq"', 1, false);


SELECT setval ('"doc_data_pk_seq"', 1, false);


SELECT setval ('"doc_groups_pk_seq"', 1, false);


SELECT setval ('"doc_states_pk_seq"', 1, false);


SELECT setval ('"filemodule_monitor_pk_seq"', 1, false);


SELECT setval ('"forum_pk_seq"', 1, false);


SELECT setval ('"forum_group_list_pk_seq"', 1, false);


SELECT setval ('"forum_monitored_forums_pk_seq"', 1, false);


SELECT setval ('"forum_saved_place_pk_seq"', 1, false);


SELECT setval ('"foundry_news_pk_seq"', 1, false);


SELECT setval ('"frs_file_pk_seq"', 1, false);


SELECT setval ('"frs_filetype_pk_seq"', 9999, true);


SELECT setval ('"frs_package_pk_seq"', 1, false);


SELECT setval ('"frs_processor_pk_seq"', 9999, true);


SELECT setval ('"frs_release_pk_seq"', 1, false);


SELECT setval ('"frs_status_pk_seq"', 3, true);


SELECT setval ('"group_history_pk_seq"', 1, false);


SELECT setval ('"groups_pk_seq"', 4, true);


SELECT setval ('"mail_group_list_pk_seq"', 1, false);


SELECT setval ('"news_bytes_pk_seq"', 1, false);


SELECT setval ('"people_job_pk_seq"', 1, false);


SELECT setval ('"people_job_category_pk_seq"', 7, true);


SELECT setval ('"people_job_inventory_pk_seq"', 1, false);


SELECT setval ('"people_job_status_pk_seq"', 1, false);


SELECT setval ('"people_skill_pk_seq"', 1, false);


SELECT setval ('"people_skill_inventory_pk_seq"', 1, false);


SELECT setval ('"people_skill_level_pk_seq"', 5, true);


SELECT setval ('"people_skill_year_pk_seq"', 5, true);


SELECT setval ('"project_assigned_to_pk_seq"', 1, false);


SELECT setval ('"project_dependencies_pk_seq"', 1, false);


SELECT setval ('"project_group_list_pk_seq"', 1, true);


SELECT setval ('"project_history_pk_seq"', 1, false);


SELECT setval ('"project_metric_pk_seq"', 1, false);


SELECT setval ('"project_metric_tmp1_pk_seq"', 1, false);


SELECT setval ('"project_status_pk_seq"', 1, false);


SELECT setval ('"project_task_pk_seq"', 1, true);


SELECT setval ('"project_weekly_metric_pk_seq"', 1, false);


SELECT setval ('"snippet_pk_seq"', 1, false);


SELECT setval ('"snippet_package_pk_seq"', 1, false);


SELECT setval ('"snippet_package_item_pk_seq"', 1, false);


SELECT setval ('"snippet_package_version_pk_seq"', 1, false);


SELECT setval ('"snippet_version_pk_seq"', 1, false);


SELECT setval ('"survey_question_types_pk_seq"', 1, false);


SELECT setval ('"survey_questions_pk_seq"', 1, false);


SELECT setval ('"surveys_pk_seq"', 1, false);


SELECT setval ('"themes_pk_seq"', 1, true);


SELECT setval ('"trove_cat_pk_seq"', 305, true);


SELECT setval ('"trove_group_link_pk_seq"', 1, false);


SELECT setval ('"trove_treesums_pk_seq"', 1, false);


SELECT setval ('"user_bookmarks_pk_seq"', 1, false);


SELECT setval ('"user_diary_pk_seq"', 1, false);


SELECT setval ('"user_diary_monitor_pk_seq"', 1, false);


SELECT setval ('"user_group_pk_seq"', 1, false);


SELECT setval ('"user_metric_pk_seq"', 1, false);


SELECT setval ('"user_metric0_pk_seq"', 1, false);


SELECT setval ('"users_pk_seq"', 101, true);


SELECT setval ('"unix_uid_seq"', 1, false);


SELECT setval ('"forum_thread_seq"', 1, false);


SELECT setval ('"project_metric_wee_ranking1_seq"', 1, false);


SELECT setval ('"prdb_dbs_dbid_seq"', 1, false);


SELECT setval ('"prweb_vhost_vhostid_seq"', 1, false);


SELECT setval ('"artifact_grou_group_artifac_seq"', 100, true);


SELECT setval ('"artifact_resolution_id_seq"', 101, true);


SELECT setval ('"artifact_perm_id_seq"', 1, false);


SELECT setval ('"artifact_category_id_seq"', 100, true);


SELECT setval ('"artifact_group_id_seq"', 1, false);


SELECT setval ('"artifact_status_id_seq"', 3, true);


SELECT setval ('"artifact_artifact_id_seq"', 1, false);


SELECT setval ('"artifact_history_id_seq"', 1, false);


SELECT setval ('"artifact_file_id_seq"', 1, false);


SELECT setval ('"artifact_message_id_seq"', 1, false);


SELECT setval ('"artifact_monitor_id_seq"', 1, false);


SELECT setval ('"artifact_canned_response_id_seq"', 1, false);


SELECT setval ('"massmail_queue_id_seq"', 1, false);


SELECT setval ('"trove_treesum_trove_treesum_seq"', 1, false);


SELECT setval ('"group_cvs_history_id_seq"', 1, false);


SELECT setval ('"themes_theme_id_seq"', 3, true);


SELECT setval ('"supported_langu_language_id_seq"', 24, true);


SELECT setval ('"skills_data_pk_seq"', 1, false);


SELECT setval ('"skills_data_types_pk_seq"', 4, true);


SELECT setval ('"project_categor_category_id_seq"', 100, true);


SELECT setval ('"project_messa_project_messa_seq"', 1, false);


SELECT setval ('"plugins_pk_seq"', 1, false);


SELECT setval ('"group_plugin_pk_seq"', 1, false);


SELECT setval ('"user_plugin_pk_seq"', 1, false);


--
--	SourceForge: Breaking Down the Barriers to Open Source Development
--	Copyright 1999-2001 (c) VA Linux Systems
--	http://sourceforge.net
--
--	$Id: SourceForge.sql,v 1.19 2001/06/27 00:29:32 tperdue Exp $	
--

CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "canned_responses" (
	"response_id" integer DEFAULT nextval('canned_responses_pk_seq'::text) NOT NULL,
	"response_title" character varying(25),
	"response_text" text,
	Constraint "canned_responses_pkey" Primary Key ("response_id")
);


CREATE SEQUENCE "db_images_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "doc_data_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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
	Constraint "doc_data_pkey" Primary Key ("docid")
);


CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "doc_groups" (
	"doc_group" integer DEFAULT nextval('doc_groups_pk_seq'::text) NOT NULL,
	"groupname" character varying(255) DEFAULT '' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "doc_groups_pkey" Primary Key ("doc_group")
);


CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "doc_states" (
	"stateid" integer DEFAULT nextval('doc_states_pk_seq'::text) NOT NULL,
	"name" character varying(255) DEFAULT '' NOT NULL,
	Constraint "doc_states_pkey" Primary Key ("stateid")
);


CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "filemodule_monitor" (
	"id" integer DEFAULT nextval('filemodule_monitor_pk_seq'::text) NOT NULL,
	"filemodule_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "filemodule_monitor_pkey" Primary Key ("id")
);


CREATE SEQUENCE "forum_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "forum_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "forum_monitored_forums" (
	"monitor_id" integer DEFAULT nextval('forum_monitored_forums_pk_seq'::text) NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "forum_monitored_forums_pkey" Primary Key ("monitor_id")
);


CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "forum_saved_place" (
	"saved_place_id" integer DEFAULT nextval('forum_saved_place_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"save_date" integer DEFAULT '0' NOT NULL,
	Constraint "forum_saved_place_pkey" Primary Key ("saved_place_id")
);


CREATE TABLE "foundry_data" (
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"freeform1_html" text,
	"freeform2_html" text,
	"sponsor1_html" text,
	"sponsor2_html" text,
	"guide_image_id" integer DEFAULT '0' NOT NULL,
	"logo_image_id" integer DEFAULT '0' NOT NULL,
	"trove_categories" text,
	Constraint "foundry_data_pkey" Primary Key ("foundry_id")
);


CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "foundry_news" (
	"foundry_news_id" integer DEFAULT nextval('foundry_news_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"news_id" integer DEFAULT '0' NOT NULL,
	"approve_date" integer DEFAULT '0' NOT NULL,
	"is_approved" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_news_pkey" Primary Key ("foundry_news_id")
);


CREATE SEQUENCE "foundry_preferred_projec_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "foundry_preferred_projects" (
	"foundry_project_id" integer DEFAULT nextval('foundry_preferred_projec_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"rank" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_preferred_projects_pkey" Primary Key ("foundry_project_id")
);


CREATE SEQUENCE "foundry_projects_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "foundry_projects" (
	"id" integer DEFAULT nextval('foundry_projects_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"project_id" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_projects_pkey" Primary Key ("id")
);


CREATE SEQUENCE "frs_file_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "frs_filetype" (
	"type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_filetype_pkey" Primary Key ("type_id")
);


CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "frs_package" (
	"package_id" integer DEFAULT nextval('frs_package_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"name" text,
	"status_id" integer DEFAULT '0' NOT NULL,
	Constraint "frs_package_pkey" Primary Key ("package_id")
);




CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "frs_processor" (
	"processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_processor_pkey" Primary Key ("processor_id")
);


CREATE SEQUENCE "frs_release_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "frs_status" (
	"status_id" integer DEFAULT nextval('frs_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "group_cvs_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "group_history" (
	"group_history_id" integer DEFAULT nextval('group_history_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	Constraint "group_history_pkey" Primary Key ("group_history_id")
);


CREATE SEQUENCE "group_type_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "group_type" (
	"type_id" integer DEFAULT nextval('group_type_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "group_type_pkey" Primary Key ("type_id")
);


CREATE SEQUENCE "groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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
	"new_task_address" text DEFAULT '' NOT NULL,
	"send_all_tasks" integer DEFAULT '0' NOT NULL,
	"dead10" integer DEFAULT '1' NOT NULL,
	"use_pm_depend_box" integer DEFAULT '1' NOT NULL,
	"dead11" integer,
	"dead12" integer,
	"dead13" integer,
	Constraint "groups_pkey" Primary Key ("group_id")
);




CREATE TABLE "intel_agreement" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"message" text,
	"is_approved" integer DEFAULT '0' NOT NULL,
	Constraint "intel_agreement_pkey" Primary Key ("user_id")
);


CREATE SEQUENCE "mail_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "news_bytes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "people_job_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_job_category" (
	"category_id" integer DEFAULT nextval('people_job_category_pk_seq'::text) NOT NULL,
	"name" text,
	"private_flag" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_category_pkey" Primary Key ("category_id")
);


CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_job_inventory" (
	"job_inventory_id" integer DEFAULT nextval('people_job_inventory_pk_seq'::text) NOT NULL,
	"job_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_inventory_pkey" Primary Key ("job_inventory_id")
);


CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_job_status" (
	"status_id" integer DEFAULT nextval('people_job_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_job_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_skill" (
	"skill_id" integer DEFAULT nextval('people_skill_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_pkey" Primary Key ("skill_id")
);


CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_skill_inventory" (
	"skill_inventory_id" integer DEFAULT nextval('people_skill_inventory_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_skill_inventory_pkey" Primary Key ("skill_inventory_id")
);


CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_skill_level" (
	"skill_level_id" integer DEFAULT nextval('people_skill_level_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_level_pkey" Primary Key ("skill_level_id")
);


CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "people_skill_year" (
	"skill_year_id" integer DEFAULT nextval('people_skill_year_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_year_pkey" Primary Key ("skill_year_id")
);


CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_assigned_to" (
	"project_assigned_id" integer DEFAULT nextval('project_assigned_to_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"assigned_to_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_assigned_to_pkey" Primary Key ("project_assigned_id")
);


CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_dependencies" (
	"project_depend_id" integer DEFAULT nextval('project_dependencies_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_task_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_dependencies_pkey" Primary Key ("project_depend_id")
);


CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_group_list" (
	"group_project_id" integer DEFAULT nextval('project_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"project_name" text DEFAULT '' NOT NULL,
	"is_public" integer DEFAULT '0' NOT NULL,
	"description" text,
	Constraint "project_group_list_pkey" Primary Key ("group_project_id")
);


CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_history" (
	"project_history_id" integer DEFAULT nextval('project_history_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	Constraint "project_history_pkey" Primary Key ("project_history_id")
);


CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_metric" (
	"ranking" integer DEFAULT nextval('project_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_metric_pkey" Primary Key ("ranking")
);




CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_metric_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	Constraint "project_metric_tmp1_pkey" Primary Key ("ranking")
);


CREATE SEQUENCE "project_metric_weekly_tm_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_status" (
	"status_id" integer DEFAULT nextval('project_status_pk_seq'::text) NOT NULL,
	"status_name" text DEFAULT '' NOT NULL,
	Constraint "project_status_pkey" Primary Key ("status_id")
);


CREATE SEQUENCE "project_task_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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
	Constraint "project_task_pkey" Primary Key ("project_task_id")
);




CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "snippet_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "snippet_package" (
	"snippet_package_id" integer DEFAULT nextval('snippet_package_pk_seq'::text) NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"name" text,
	"description" text,
	"category" integer DEFAULT '0' NOT NULL,
	"language" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_pkey" Primary Key ("snippet_package_id")
);


CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "snippet_package_item" (
	"snippet_package_item_id" integer DEFAULT nextval('snippet_package_item_pk_seq'::text) NOT NULL,
	"snippet_package_version_id" integer DEFAULT '0' NOT NULL,
	"snippet_version_id" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_item_pkey" Primary Key ("snippet_package_item_id")
);


CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "snippet_package_version" (
	"snippet_package_version_id" integer DEFAULT nextval('snippet_package_version_pk_seq'::text) NOT NULL,
	"snippet_package_id" integer DEFAULT '0' NOT NULL,
	"changes" text,
	"version" text,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_version_pkey" Primary Key ("snippet_package_version_id")
);


CREATE SEQUENCE "snippet_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE TABLE "stats_ftp_downloads" (
	"day" integer DEFAULT '0' NOT NULL,
	"filerelease_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);




CREATE TABLE "stats_http_downloads" (
	"day" integer DEFAULT '0' NOT NULL,
	"filerelease_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);




CREATE SEQUENCE "supported_languages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "supported_languages" (
	"language_id" integer DEFAULT nextval('supported_languages_pk_seq'::text) NOT NULL,
	"name" text,
	"filename" text,
	"classname" text,
	"language_code" character(2),
	Constraint "supported_languages_pkey" Primary Key ("language_id")
);


CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "survey_question_types" (
	"id" integer DEFAULT nextval('survey_question_types_pk_seq'::text) NOT NULL,
	"type" text DEFAULT '' NOT NULL,
	Constraint "survey_question_types_pkey" Primary Key ("id")
);


CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "surveys_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "surveys" (
	"survey_id" integer DEFAULT nextval('surveys_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"survey_title" text DEFAULT '' NOT NULL,
	"survey_questions" text DEFAULT '' NOT NULL,
	"is_active" integer DEFAULT '1' NOT NULL,
	Constraint "surveys_pkey" Primary Key ("survey_id")
);


CREATE SEQUENCE "system_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "system_machines_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "system_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "system_services_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "system_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "themes" (
	"theme_id" integer DEFAULT nextval('themes_pk_seq'::text) NOT NULL,
	"dirname" character varying(80),
	"fullname" character varying(80),
	Constraint "themes_pkey" Primary Key ("theme_id")
);


CREATE TABLE "top_group" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"group_name" character varying(40),
	"downloads_all" integer DEFAULT '0' NOT NULL,
	"rank_downloads_all" integer DEFAULT '0' NOT NULL,
	"rank_downloads_all_old" integer DEFAULT '0' NOT NULL,
	"downloads_week" integer DEFAULT '0' NOT NULL,
	"rank_downloads_week" integer DEFAULT '0' NOT NULL,
	"rank_downloads_week_old" integer DEFAULT '0' NOT NULL,
	"userrank" integer DEFAULT '0' NOT NULL,
	"rank_userrank" integer DEFAULT '0' NOT NULL,
	"rank_userrank_old" integer DEFAULT '0' NOT NULL,
	"forumposts_week" integer DEFAULT '0' NOT NULL,
	"rank_forumposts_week" integer DEFAULT '0' NOT NULL,
	"rank_forumposts_week_old" integer DEFAULT '0' NOT NULL,
	"pageviews_proj" integer DEFAULT '0' NOT NULL,
	"rank_pageviews_proj" integer DEFAULT '0' NOT NULL,
	"rank_pageviews_proj_old" integer DEFAULT '0' NOT NULL
);


CREATE SEQUENCE "trove_cat_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "trove_group_link" (
	"trove_group_id" integer DEFAULT nextval('trove_group_link_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_version" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_root" integer DEFAULT '0' NOT NULL,
	Constraint "trove_group_link_pkey" Primary Key ("trove_group_id")
);


CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "user_bookmarks" (
	"bookmark_id" integer DEFAULT nextval('user_bookmarks_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"bookmark_url" text,
	"bookmark_title" text,
	Constraint "user_bookmarks_pkey" Primary Key ("bookmark_id")
);


CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "user_diary" (
	"id" integer DEFAULT nextval('user_diary_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"date_posted" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	"is_public" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_pkey" Primary Key ("id")
);


CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "user_diary_monitor" (
	"monitor_id" integer DEFAULT nextval('user_diary_monitor_pk_seq'::text) NOT NULL,
	"monitored_user" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_monitor_pkey" Primary Key ("monitor_id")
);


CREATE SEQUENCE "user_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "user_group" (
	"user_group_id" integer DEFAULT nextval('user_group_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"admin_flags" character(16) DEFAULT '' NOT NULL,
	"bug_flags" integer DEFAULT '0' NOT NULL,
	"forum_flags" integer DEFAULT '0' NOT NULL,
	"project_flags" integer DEFAULT '2' NOT NULL,
	"patch_flags" integer DEFAULT '1' NOT NULL,
	"support_flags" integer DEFAULT '1' NOT NULL,
	"doc_flags" integer DEFAULT '0' NOT NULL,
	"cvs_flags" integer DEFAULT '1' NOT NULL,
	"member_role" integer DEFAULT '100' NOT NULL,
	"release_flags" integer DEFAULT '0' NOT NULL,
	"artifact_flags" integer,
	Constraint "user_group_pkey" Primary Key ("user_group_id")
);




CREATE SEQUENCE "user_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "users_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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
	Constraint "users_pkey" Primary Key ("user_id")
);




CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "project_sums_agg" (
	"group_id" integer DEFAULT 0 NOT NULL,
	"type" character(4),
	"count" integer DEFAULT 0 NOT NULL
);


CREATE SEQUENCE "project_metric_wee_ranking1_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "prweb_vhost" (
	"vhostid" integer DEFAULT nextval('"prweb_vhost_vhostid_seq"'::text) NOT NULL,
	"vhost_name" text,
	"docdir" text,
	"cgidir" text,
	"group_id" integer NOT NULL,
	Constraint "prweb_vhost_pkey" Primary Key ("vhostid")
);




CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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




CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_resolution" (
	"id" integer DEFAULT nextval('"artifact_resolution_id_seq"'::text) NOT NULL,
	"resolution_name" text,
	Constraint "artifact_resolution_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_perm" (
	"id" integer DEFAULT nextval('"artifact_perm_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"perm_level" integer DEFAULT 0 NOT NULL,
	Constraint "artifact_perm_pkey" Primary Key ("id")
);


CREATE VIEW "artifactperm_user_vw" as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id);

CREATE VIEW "artifactperm_artgrouplist_vw" as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id);

CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_category" (
	"id" integer DEFAULT nextval('"artifact_category_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"category_name" text NOT NULL,
	"auto_assign_to" integer DEFAULT 100 NOT NULL,
	Constraint "artifact_category_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_group" (
	"id" integer DEFAULT nextval('"artifact_group_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"group_name" text NOT NULL,
	Constraint "artifact_group_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_status" (
	"id" integer DEFAULT nextval('"artifact_status_id_seq"'::text) NOT NULL,
	"status_name" text NOT NULL,
	Constraint "artifact_status_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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

CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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

CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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

CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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

CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "artifact_monitor" (
	"id" integer DEFAULT nextval('"artifact_monitor_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"email" text,
	Constraint "artifact_monitor_pkey" Primary Key ("id")
);


CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE SEQUENCE "kernel_traffic_kt_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


CREATE TABLE "kernel_traffic" (
	"kt_id" integer DEFAULT nextval('"kernel_traffic_kt_id_seq"'::text) NOT NULL,
	"kt_data" text,
	Constraint "kernel_traffic_pkey" Primary Key ("kt_id")
);


CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);


CREATE FUNCTION "plpgsql_call_handler" () RETURNS opaque AS '/usr/local/pgsql/lib/plpgsql.so', 'plpgsql_call_handler' LANGUAGE 'C';


CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER "plpgsql_call_handler" LANCOMPILER 'PL/pgSQL';


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
        UPDATE artifact_counts_agg SET count=count+1, open_count=open_count+1 
            WHERE group_artifact_id=NEW.group_artifact_id;
        --
        --  now see how to increment/decrement the old types sums
        --
        IF NEW.status_id <> OLD.status_id THEN 
            IF OLD.status_id = 2 THEN
                UPDATE artifact_counts_agg SET count=count-1 
                    WHERE group_artifact_id=OLD.group_artifact_id;
            --
            --  no need to do anything if it was in deleted status
            --
            END IF;
        ELSE
            --
            --  Was already in open status before
            --
            UPDATE artifact_counts_agg SET count=count-1, open_count=open_count-1 
                WHERE group_artifact_id=OLD.group_artifact_id;
        END IF;
    ELSE
        --
        -- just need to evaluate the status flag and 
        -- increment/decrement the counter as necessary
        --
        IF NEW.status_id <> OLD.status_id THEN
            IF new.status_id = 1 THEN
                UPDATE artifact_counts_agg SET open_count=open_count+1 
                    WHERE group_artifact_id=new.group_artifact_id;
            ELSE 
                IF new.status_id = 2 THEN
                    UPDATE artifact_counts_agg SET open_count=open_count-1 
                        WHERE group_artifact_id=new.group_artifact_id;
                ELSE 
                    IF new.status_id = 3 THEN
                        UPDATE artifact_counts_agg SET open_count=open_count-1,count=count-1 
                            WHERE group_artifact_id=new.group_artifact_id;
                    END IF;
                END IF;
            END IF;
        END IF; 
    END IF;
    RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;


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


CREATE TABLE "frs_dlstats_file_agg" (
	"month" integer,
	"day" integer,
	"file_id" integer,
	"downloads" integer
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


CREATE TABLE "activity_log_old_old" (
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


CREATE TABLE "activity_log_old" (
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


CREATE TABLE "cache_store" (
	"name" character varying(255) NOT NULL,
	"data" text,
	"indate" integer DEFAULT 0 NOT NULL,
	Constraint "cache_store_pkey" Primary Key ("name")
);


CREATE  INDEX "db_images_group" on "db_images" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "doc_group_doc_group" on "doc_data" using btree ( "doc_group" "int4_ops" );


CREATE  INDEX "doc_groups_group" on "doc_groups" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "filemodule_monitor_id" on "filemodule_monitor" using btree ( "filemodule_id" "int4_ops" );


CREATE  INDEX "filemodulemonitor_userid" on "filemodule_monitor" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "forum_forumid_msgid" on "forum" using btree ( "group_forum_id" "int4_ops", "msg_id" "int4_ops" );


CREATE  INDEX "forum_group_forum_id" on "forum" using btree ( "group_forum_id" "int4_ops" );


CREATE  INDEX "forum_forumid_isfollowupto" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops" );


CREATE  INDEX "forum_forumid_threadid_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "thread_id" "int4_ops", "most_recent_date" "int4_ops" );


CREATE  INDEX "forum_threadid_isfollowupto" on "forum" using btree ( "thread_id" "int4_ops", "is_followup_to" "int4_ops" );


CREATE  INDEX "forum_forumid_isfollto_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops", "most_recent_date" "int4_ops" );


CREATE  INDEX "forum_group_list_group_id" on "forum_group_list" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "forummonitoredforums_user" on "forum_monitored_forums" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "forum_monitor_combo_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops", "user_id" "int4_ops" );


CREATE  INDEX "forum_monitor_thread_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops" );


CREATE  INDEX "foundry_news_foundry_approved_d" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops", "approve_date" "int4_ops" );


CREATE  INDEX "foundry_news_foundry_approved" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops" );


CREATE  INDEX "foundry_news_foundry" on "foundry_news" using btree ( "foundry_id" "int4_ops" );


CREATE  INDEX "foundrynews_foundry_date_approv" on "foundry_news" using btree ( "foundry_id" "int4_ops", "approve_date" "int4_ops", "is_approved" "int4_ops" );


CREATE  INDEX "foundry_project_group_rank" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops", "rank" "int4_ops" );


CREATE  INDEX "foundry_project_group" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "foundry_projects_foundry" on "foundry_projects" using btree ( "foundry_id" "int4_ops" );


CREATE  INDEX "frs_file_date" on "frs_file" using btree ( "post_date" "int4_ops" );


CREATE  INDEX "frs_file_release_id" on "frs_file" using btree ( "release_id" "int4_ops" );


CREATE  INDEX "package_group_id" on "frs_package" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "frs_release_package" on "frs_release" using btree ( "package_id" "int4_ops" );


CREATE  INDEX "group_history_group_id" on "group_history" using btree ( "group_id" "int4_ops" );


CREATE UNIQUE INDEX "group_unix_uniq" on "groups" using btree ( "unix_group_name" "varchar_ops" );


CREATE  INDEX "groups_type" on "groups" using btree ( "type" "int4_ops" );


CREATE  INDEX "groups_public" on "groups" using btree ( "is_public" "int4_ops" );


CREATE  INDEX "groups_status" on "groups" using btree ( "status" "bpchar_ops" );


CREATE  INDEX "mail_group_list_group" on "mail_group_list" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "news_bytes_group" on "news_bytes" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "news_bytes_approved" on "news_bytes" using btree ( "is_approved" "int4_ops" );


CREATE  INDEX "news_bytes_forum" on "news_bytes" using btree ( "forum_id" "int4_ops" );


CREATE  INDEX "news_group_date" on "news_bytes" using btree ( "group_id" "int4_ops", "date" "int4_ops" );


CREATE  INDEX "news_approved_date" on "news_bytes" using btree ( "is_approved" "int4_ops", "date" "int4_ops" );


CREATE  INDEX "people_job_group_id" on "people_job" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "project_assigned_to_assigned_to" on "project_assigned_to" using btree ( "assigned_to_id" "int4_ops" );


CREATE  INDEX "project_assigned_to_task_id" on "project_assigned_to" using btree ( "project_task_id" "int4_ops" );


CREATE  INDEX "project_is_dependent_on_task_id" on "project_dependencies" using btree ( "is_dependent_on_task_id" "int4_ops" );


CREATE  INDEX "project_dependencies_task_id" on "project_dependencies" using btree ( "project_task_id" "int4_ops" );


CREATE  INDEX "project_group_list_group_id" on "project_group_list" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "project_history_task_id" on "project_history" using btree ( "project_task_id" "int4_ops" );


CREATE  INDEX "project_metric_group" on "project_metric" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "projecttask_projid_status" on "project_task" using btree ( "group_project_id" "int4_ops", "status_id" "int4_ops" );


CREATE  INDEX "project_task_group_project_id" on "project_task" using btree ( "group_project_id" "int4_ops" );


CREATE  INDEX "projectweeklymetric_ranking" on "project_weekly_metric" using btree ( "ranking" "int4_ops" );


CREATE  INDEX "project_metric_weekly_group" on "project_weekly_metric" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "session_user_id" on "session" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "session_time" on "session" using btree ( "time" "int4_ops" );


CREATE  INDEX "snippet_language" on "snippet" using btree ( "language" "int4_ops" );


CREATE  INDEX "snippet_category" on "snippet" using btree ( "category" "int4_ops" );


CREATE  INDEX "snippet_package_language" on "snippet_package" using btree ( "language" "int4_ops" );


CREATE  INDEX "snippet_package_category" on "snippet_package" using btree ( "category" "int4_ops" );


CREATE  INDEX "snippet_package_item_pkg_ver" on "snippet_package_item" using btree ( "snippet_package_version_id" "int4_ops" );


CREATE  INDEX "snippet_package_version_pkg_id" on "snippet_package_version" using btree ( "snippet_package_id" "int4_ops" );


CREATE  INDEX "snippet_version_snippet_id" on "snippet_version" using btree ( "snippet_id" "int4_ops" );


CREATE  INDEX "pages_by_day_day" on "stats_agg_pages_by_day" using btree ( "day" "int4_ops" );


CREATE  INDEX "statsftpdl_day_fileid" on "stats_ftp_downloads" using btree ( "day" "int4_ops", "filerelease_id" "int4_ops" );


CREATE  INDEX "statshttpdl_day_fileid" on "stats_http_downloads" using btree ( "day" "int4_ops", "filerelease_id" "int4_ops" );


CREATE  INDEX "supported_languages_code" on "supported_languages" using btree ( "language_code" "bpchar_ops" );


CREATE  INDEX "survey_questions_group" on "survey_questions" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "survey_rating_aggregate_type_id" on "survey_rating_aggregate" using btree ( "type" "int4_ops", "id" "int4_ops" );


CREATE  INDEX "survey_rating_responses_user_ty" on "survey_rating_response" using btree ( "user_id" "int4_ops", "type" "int4_ops", "id" "int4_ops" );


CREATE  INDEX "survey_rating_responses_type_id" on "survey_rating_response" using btree ( "type" "int4_ops", "id" "int4_ops" );


CREATE  INDEX "survey_responses_group_id" on "survey_responses" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "survey_responses_user_survey_qu" on "survey_responses" using btree ( "user_id" "int4_ops", "survey_id" "int4_ops", "question_id" "int4_ops" );


CREATE  INDEX "survey_responses_user_survey" on "survey_responses" using btree ( "user_id" "int4_ops", "survey_id" "int4_ops" );


CREATE  INDEX "survey_responses_survey_questio" on "survey_responses" using btree ( "survey_id" "int4_ops", "question_id" "int4_ops" );


CREATE  INDEX "surveys_group" on "surveys" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "rank_forumposts_week_idx" on "top_group" using btree ( "rank_forumposts_week" "int4_ops" );


CREATE  INDEX "rank_downloads_week_idx" on "top_group" using btree ( "rank_downloads_week" "int4_ops" );


CREATE  INDEX "pageviews_proj_idx" on "top_group" using btree ( "pageviews_proj" "int4_ops" );


CREATE  INDEX "rank_userrank_idx" on "top_group" using btree ( "rank_userrank" "int4_ops" );


CREATE  INDEX "rank_downloads_all_idx" on "top_group" using btree ( "rank_downloads_all" "int4_ops" );


CREATE  INDEX "parent_idx" on "trove_cat" using btree ( "parent" "int4_ops" );


CREATE  INDEX "root_parent_idx" on "trove_cat" using btree ( "root_parent" "int4_ops" );


CREATE  INDEX "version_idx" on "trove_cat" using btree ( "version" "int4_ops" );


CREATE  INDEX "trove_group_link_group_id" on "trove_group_link" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "trove_group_link_cat_id" on "trove_group_link" using btree ( "trove_cat_id" "int4_ops" );


CREATE  INDEX "user_bookmark_user_id" on "user_bookmarks" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "user_diary_user" on "user_diary" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "user_diary_user_date" on "user_diary" using btree ( "user_id" "int4_ops", "date_posted" "int4_ops" );


CREATE  INDEX "user_diary_date" on "user_diary" using btree ( "date_posted" "int4_ops" );


CREATE  INDEX "user_diary_monitor_user" on "user_diary_monitor" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "user_diary_monitor_monitored_us" on "user_diary_monitor" using btree ( "monitored_user" "int4_ops" );


CREATE  INDEX "user_group_group_id" on "user_group" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "bug_flags_idx" on "user_group" using btree ( "bug_flags" "int4_ops" );


CREATE  INDEX "project_flags_idx" on "user_group" using btree ( "project_flags" "int4_ops" );


CREATE  INDEX "user_group_user_id" on "user_group" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "admin_flags_idx" on "user_group" using btree ( "admin_flags" "bpchar_ops" );


CREATE  INDEX "forum_flags_idx" on "user_group" using btree ( "forum_flags" "int4_ops" );


CREATE UNIQUE INDEX "usergroup_uniq_groupid_userid" on "user_group" using btree ( "group_id" "int4_ops", "user_id" "int4_ops" );


CREATE  INDEX "user_metric0_user_id" on "user_metric0" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "user_pref_user_id" on "user_preferences" using btree ( "user_id" "int4_ops" );


CREATE  INDEX "user_ratings_rated_by" on "user_ratings" using btree ( "rated_by" "int4_ops" );


CREATE  INDEX "user_ratings_user_id" on "user_ratings" using btree ( "user_id" "int4_ops" );


CREATE UNIQUE INDEX "users_namename_uniq" on "users" using btree ( "user_name" "text_ops" );


CREATE  INDEX "users_status" on "users" using btree ( "status" "bpchar_ops" );


CREATE  INDEX "users_user_pw" on "users" using btree ( "user_pw" "varchar_ops" );


CREATE  INDEX "projectsumsagg_groupid" on "project_sums_agg" using btree ( "group_id" "int4_ops" );


CREATE UNIQUE INDEX "idx_prdb_dbname" on "prdb_dbs" using btree ( "dbname" "text_ops" );


CREATE  INDEX "idx_vhost_groups" on "prweb_vhost" using btree ( "group_id" "int4_ops" );


CREATE UNIQUE INDEX "idx_vhost_hostnames" on "prweb_vhost" using btree ( "vhost_name" "text_ops" );


CREATE  INDEX "artgrouplist_groupid" on "artifact_group_list" using btree ( "group_id" "int4_ops" );


CREATE  INDEX "artgrouplist_groupid_public" on "artifact_group_list" using btree ( "group_id" "int4_ops", "is_public" "int4_ops" );


CREATE UNIQUE INDEX "artperm_groupartifactid_userid" on "artifact_perm" using btree ( "group_artifact_id" "int4_ops", "user_id" "int4_ops" );


CREATE  INDEX "artperm_groupartifactid" on "artifact_perm" using btree ( "group_artifact_id" "int4_ops" );


CREATE  INDEX "artcategory_groupartifactid" on "artifact_category" using btree ( "group_artifact_id" "int4_ops" );


CREATE  INDEX "artgroup_groupartifactid" on "artifact_group" using btree ( "group_artifact_id" "int4_ops" );


CREATE  INDEX "art_groupartid" on "artifact" using btree ( "group_artifact_id" "int4_ops" );


CREATE  INDEX "art_groupartid_statusid" on "artifact" using btree ( "group_artifact_id" "int4_ops", "status_id" "int4_ops" );


CREATE  INDEX "art_groupartid_assign" on "artifact" using btree ( "group_artifact_id" "int4_ops", "assigned_to" "int4_ops" );


CREATE  INDEX "art_groupartid_submit" on "artifact" using btree ( "group_artifact_id" "int4_ops", "submitted_by" "int4_ops" );


CREATE  INDEX "art_submit_status" on "artifact" using btree ( "submitted_by" "int4_ops", "status_id" "int4_ops" );


CREATE  INDEX "art_assign_status" on "artifact" using btree ( "assigned_to" "int4_ops", "status_id" "int4_ops" );


CREATE  INDEX "art_groupartid_artifactid" on "artifact" using btree ( "group_artifact_id" "int4_ops", "artifact_id" "int4_ops" );


CREATE  INDEX "arthistory_artid" on "artifact_history" using btree ( "artifact_id" "int4_ops" );


CREATE  INDEX "arthistory_artid_entrydate" on "artifact_history" using btree ( "artifact_id" "int4_ops", "entrydate" "int4_ops" );


CREATE  INDEX "artfile_artid" on "artifact_file" using btree ( "artifact_id" "int4_ops" );


CREATE  INDEX "artfile_artid_adddate" on "artifact_file" using btree ( "artifact_id" "int4_ops", "adddate" "int4_ops" );


CREATE  INDEX "artmessage_artid" on "artifact_message" using btree ( "artifact_id" "int4_ops" );


CREATE  INDEX "artmessage_artid_adddate" on "artifact_message" using btree ( "artifact_id" "int4_ops", "adddate" "int4_ops" );


CREATE  INDEX "artmonitor_artifactid" on "artifact_monitor" using btree ( "artifact_id" "int4_ops" );


CREATE  INDEX "artifactcannedresponses_groupid" on "artifact_canned_responses" using btree ( "group_artifact_id" "int4_ops" );


CREATE  INDEX "artifactcountsagg_groupartid" on "artifact_counts_agg" using btree ( "group_artifact_id" "int4_ops" );


CREATE UNIQUE INDEX "statssitepgsbyday_oid" on "stats_site_pages_by_day" using btree ( "oid" "oid_ops" );


CREATE  INDEX "statssitepagesbyday_month_day" on "stats_site_pages_by_day" using btree ( "month" "int4_ops", "day" "int4_ops" );


CREATE UNIQUE INDEX "frsdlfileagg_oid" on "frs_dlstats_file_agg" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "frsdlfileagg_month_day_file" on "frs_dlstats_file_agg" using btree ( "month" "int4_ops", "day" "int4_ops", "file_id" "int4_ops" );


CREATE UNIQUE INDEX "statsaggsitebygrp_oid" on "stats_agg_site_by_group" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statssitebygroup_month_day_grou" on "stats_agg_site_by_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statsprojectmetric_oid" on "stats_project_metric" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statsprojectmetric_month_day_gr" on "stats_project_metric" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statsagglogobygrp_oid" on "stats_agg_logo_by_group" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statslogobygroup_month_day_grou" on "stats_agg_logo_by_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statssubdpages_oid" on "stats_subd_pages" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statssubdpages_month_day_group" on "stats_subd_pages" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statscvsgrp_oid" on "stats_cvs_group" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statscvsgroup_month_day_group" on "stats_cvs_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statsprojectdevelop_oid" on "stats_project_developers" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statsprojectdev_month_day_group" on "stats_project_developers" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statsproject_oid" on "stats_project" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statsproject_month_day_group" on "stats_project" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE UNIQUE INDEX "statssite_oid" on "stats_site" using btree ( "oid" "oid_ops" );


CREATE UNIQUE INDEX "statssite_month_day" on "stats_site" using btree ( "month" "int4_ops", "day" "int4_ops" );


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


CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER DELETE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER UPDATE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER DELETE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER UPDATE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER INSERT OR UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER DELETE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER UPDATE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


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

CREATE RULE forum_insert_agg AS ON INSERT TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count + 1) WHERE (forum_agg_msg_count.group_forum_id = new.group_forum_id);

CREATE RULE forum_delete_agg AS ON DELETE TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count - 1) WHERE (forum_agg_msg_count.group_forum_id = old.group_forum_id);

CREATE RULE artifact_insert_agg AS ON INSERT TO artifact DO UPDATE artifact_counts_agg SET count = (artifact_counts_agg.count + 1), open_count = (artifact_counts_agg.open_count + 1) WHERE (artifact_counts_agg.group_artifact_id = new.group_artifact_id);

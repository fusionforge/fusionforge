CREATE SEQUENCE "bug_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug" (
	"bug_id" integer DEFAULT nextval('bug_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"status_id" integer DEFAULT '0' NOT NULL,
	"priority" integer DEFAULT '0' NOT NULL,
	"category_id" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"assigned_to" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	"close_date" integer,
	"bug_group_id" integer DEFAULT '0' NOT NULL,
	"resolution_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("bug_id")
);
CREATE SEQUENCE "bug_bug_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_bug_dependencies" (
	"bug_depend_id" integer DEFAULT nextval('bug_bug_dependencies_pk_seq'::text) NOT NULL,
	"bug_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_bug_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("bug_depend_id")
);
CREATE SEQUENCE "bug_canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_canned_responses" (
	"bug_canned_id" integer DEFAULT nextval('bug_canned_responses_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"title" text,
	"body" text,
	PRIMARY KEY ("bug_canned_id")
);
CREATE SEQUENCE "bug_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_category" (
	"bug_category_id" integer DEFAULT nextval('bug_category_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"category_name" text,
	PRIMARY KEY ("bug_category_id")
);
CREATE SEQUENCE "bug_filter_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_filter" (
	"filter_id" integer DEFAULT nextval('bug_filter_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"sql_clause" text DEFAULT '' NOT NULL,
	"is_active" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("filter_id")
);
CREATE SEQUENCE "bug_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_group" (
	"bug_group_id" integer DEFAULT nextval('bug_group_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"group_name" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("bug_group_id")
);
CREATE SEQUENCE "bug_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_history" (
	"bug_history_id" integer DEFAULT nextval('bug_history_pk_seq'::text) NOT NULL,
	"bug_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	PRIMARY KEY ("bug_history_id")
);
CREATE SEQUENCE "bug_resolution_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_resolution" (
	"resolution_id" integer DEFAULT nextval('bug_resolution_pk_seq'::text) NOT NULL,
	"resolution_name" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("resolution_id")
);
CREATE SEQUENCE "bug_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_status" (
	"status_id" integer DEFAULT nextval('bug_status_pk_seq'::text) NOT NULL,
	"status_name" text,
	PRIMARY KEY ("status_id")
);
CREATE SEQUENCE "bug_task_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "bug_task_dependencies" (
	"bug_depend_id" integer DEFAULT nextval('bug_task_dependencies_pk_seq'::text) NOT NULL,
	"bug_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_task_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("bug_depend_id")
);
CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "canned_responses" (
	"response_id" integer DEFAULT nextval('canned_responses_pk_seq'::text) NOT NULL,
	"response_title" character varying(25),
	"response_text" text,
	PRIMARY KEY ("response_id")
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
	PRIMARY KEY ("id")
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
	PRIMARY KEY ("docid")
);
CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "doc_groups" (
	"doc_group" integer DEFAULT nextval('doc_groups_pk_seq'::text) NOT NULL,
	"groupname" character varying(255) DEFAULT '' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("doc_group")
);
CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "doc_states" (
	"stateid" integer DEFAULT nextval('doc_states_pk_seq'::text) NOT NULL,
	"name" character varying(255) DEFAULT '' NOT NULL,
	PRIMARY KEY ("stateid")
);
CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "filemodule_monitor" (
	"id" integer DEFAULT nextval('filemodule_monitor_pk_seq'::text) NOT NULL,
	"filemodule_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("id")
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
	PRIMARY KEY ("msg_id")
);
CREATE TABLE "forum_agg_msg_count" (
	"group_forum_id" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("group_forum_id")
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
	PRIMARY KEY ("group_forum_id")
);
CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "forum_monitored_forums" (
	"monitor_id" integer DEFAULT nextval('forum_monitored_forums_pk_seq'::text) NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("monitor_id")
);
CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "forum_saved_place" (
	"saved_place_id" integer DEFAULT nextval('forum_saved_place_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"save_date" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("saved_place_id")
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
	PRIMARY KEY ("foundry_id")
);
CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "foundry_news" (
	"foundry_news_id" integer DEFAULT nextval('foundry_news_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"news_id" integer DEFAULT '0' NOT NULL,
	"approve_date" integer DEFAULT '0' NOT NULL,
	"is_approved" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("foundry_news_id")
);
CREATE SEQUENCE "foundry_preferred_projec_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "foundry_preferred_projects" (
	"foundry_project_id" integer DEFAULT nextval('foundry_preferred_projec_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"rank" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("foundry_project_id")
);
CREATE SEQUENCE "foundry_projects_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "foundry_projects" (
	"id" integer DEFAULT nextval('foundry_projects_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"project_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("id")
);
CREATE TABLE "frs_dlstats_agg" (
	"file_id" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"downloads_http" integer DEFAULT '0' NOT NULL,
	"downloads_ftp" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "frs_dlstats_file_agg" (
	"file_id" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "frs_dlstats_filetotal_agg" (
	"file_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("file_id")
);
CREATE TABLE "frs_dlstats_filetotal_agg_old" (
	"file_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "frs_dlstats_group_agg" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "frs_dlstats_grouptotal_agg" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
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
	PRIMARY KEY ("file_id")
);
CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "frs_filetype" (
	"type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("type_id")
);
CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "frs_package" (
	"package_id" integer DEFAULT nextval('frs_package_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"name" text,
	"status_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("package_id")
);
CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "frs_processor" (
	"processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("processor_id")
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
	PRIMARY KEY ("release_id")
);
CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "frs_status" (
	"status_id" integer DEFAULT nextval('frs_status_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("status_id")
);
CREATE SEQUENCE "group_cvs_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "group_cvs_history" (
	"id" integer DEFAULT nextval('group_cvs_history_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"user_name" character varying(80) DEFAULT '' NOT NULL,
	"cvs_commits" integer DEFAULT '0' NOT NULL,
	"cvs_commits_wk" integer DEFAULT '0' NOT NULL,
	"cvs_adds" integer DEFAULT '0' NOT NULL,
	"cvs_adds_wk" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("id")
);
CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "group_history" (
	"group_history_id" integer DEFAULT nextval('group_history_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	PRIMARY KEY ("group_history_id")
);
CREATE SEQUENCE "group_type_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "group_type" (
	"type_id" integer DEFAULT nextval('group_type_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("type_id")
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
	"use_bugs" integer DEFAULT '1' NOT NULL,
	"rand_hash" text,
	"use_mail" integer DEFAULT '1' NOT NULL,
	"use_survey" integer DEFAULT '1' NOT NULL,
	"use_patch" integer DEFAULT '1' NOT NULL,
	"use_forum" integer DEFAULT '1' NOT NULL,
	"use_pm" integer DEFAULT '1' NOT NULL,
	"use_cvs" integer DEFAULT '1' NOT NULL,
	"use_news" integer DEFAULT '1' NOT NULL,
	"use_support" integer DEFAULT '1' NOT NULL,
	"new_bug_address" text DEFAULT '' NOT NULL,
	"new_patch_address" text DEFAULT '' NOT NULL,
	"new_support_address" text DEFAULT '' NOT NULL,
	"type" integer DEFAULT '1' NOT NULL,
	"use_docman" integer DEFAULT '1' NOT NULL,
	"send_all_bugs" integer DEFAULT '0' NOT NULL,
	"send_all_patches" integer DEFAULT '0' NOT NULL,
	"send_all_support" integer DEFAULT '0' NOT NULL,
	"new_task_address" text DEFAULT '' NOT NULL,
	"send_all_tasks" integer DEFAULT '0' NOT NULL,
	"use_bug_depend_box" integer DEFAULT '1' NOT NULL,
	"use_pm_depend_box" integer DEFAULT '1' NOT NULL,
	PRIMARY KEY ("group_id")
);
CREATE TABLE "intel_agreement" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"message" text,
	"is_approved" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("user_id")
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
	PRIMARY KEY ("group_list_id")
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
	PRIMARY KEY ("id")
);
CREATE SEQUENCE "patch_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "patch" (
	"patch_id" integer DEFAULT nextval('patch_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"patch_status_id" integer DEFAULT '0' NOT NULL,
	"patch_category_id" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"assigned_to" integer DEFAULT '0' NOT NULL,
	"open_date" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"code" text,
	"close_date" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("patch_id")
);
CREATE SEQUENCE "patch_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "patch_category" (
	"patch_category_id" integer DEFAULT nextval('patch_category_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"category_name" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("patch_category_id")
);
CREATE SEQUENCE "patch_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "patch_history" (
	"patch_history_id" integer DEFAULT nextval('patch_history_pk_seq'::text) NOT NULL,
	"patch_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	PRIMARY KEY ("patch_history_id")
);
CREATE SEQUENCE "patch_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "patch_status" (
	"patch_status_id" integer DEFAULT nextval('patch_status_pk_seq'::text) NOT NULL,
	"status_name" text,
	PRIMARY KEY ("patch_status_id")
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
	PRIMARY KEY ("job_id")
);
CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_job_category" (
	"category_id" integer DEFAULT nextval('people_job_category_pk_seq'::text) NOT NULL,
	"name" text,
	"private_flag" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("category_id")
);
CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_job_inventory" (
	"job_inventory_id" integer DEFAULT nextval('people_job_inventory_pk_seq'::text) NOT NULL,
	"job_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("job_inventory_id")
);
CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_job_status" (
	"status_id" integer DEFAULT nextval('people_job_status_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("status_id")
);
CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_skill" (
	"skill_id" integer DEFAULT nextval('people_skill_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("skill_id")
);
CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_skill_inventory" (
	"skill_inventory_id" integer DEFAULT nextval('people_skill_inventory_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("skill_inventory_id")
);
CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_skill_level" (
	"skill_level_id" integer DEFAULT nextval('people_skill_level_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("skill_level_id")
);
CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "people_skill_year" (
	"skill_year_id" integer DEFAULT nextval('people_skill_year_pk_seq'::text) NOT NULL,
	"name" text,
	PRIMARY KEY ("skill_year_id")
);
CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_assigned_to" (
	"project_assigned_id" integer DEFAULT nextval('project_assigned_to_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"assigned_to_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("project_assigned_id")
);
CREATE TABLE "project_counts_tmp" (
	"group_id" integer,
	"type" text,
	"count" double precision
);
CREATE TABLE "project_counts_weekly_tmp" (
	"group_id" integer,
	"type" text,
	"count" double precision
);
CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_dependencies" (
	"project_depend_id" integer DEFAULT nextval('project_dependencies_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_task_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("project_depend_id")
);
CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_group_list" (
	"group_project_id" integer DEFAULT nextval('project_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"project_name" text DEFAULT '' NOT NULL,
	"is_public" integer DEFAULT '0' NOT NULL,
	"description" text,
	PRIMARY KEY ("group_project_id")
);
CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_history" (
	"project_history_id" integer DEFAULT nextval('project_history_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("project_history_id")
);
CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_metric" (
	"ranking" integer DEFAULT nextval('project_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("ranking")
);
CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_metric_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	PRIMARY KEY ("ranking")
);
CREATE SEQUENCE "project_metric_weekly_tm_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_metric_weekly_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_weekly_tm_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	PRIMARY KEY ("ranking")
);
CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_status" (
	"status_id" integer DEFAULT nextval('project_status_pk_seq'::text) NOT NULL,
	"status_name" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("status_id")
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
	PRIMARY KEY ("project_task_id")
);
CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "project_weekly_metric" (
	"ranking" integer DEFAULT nextval('project_weekly_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("ranking")
);
CREATE TABLE "session" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"session_hash" character(32) DEFAULT '' NOT NULL,
	"ip_addr" character(15) DEFAULT '' NOT NULL,
	"time" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("session_hash")
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
	PRIMARY KEY ("snippet_id")
);
CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "snippet_package" (
	"snippet_package_id" integer DEFAULT nextval('snippet_package_pk_seq'::text) NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"name" text,
	"description" text,
	"category" integer DEFAULT '0' NOT NULL,
	"language" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("snippet_package_id")
);
CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "snippet_package_item" (
	"snippet_package_item_id" integer DEFAULT nextval('snippet_package_item_pk_seq'::text) NOT NULL,
	"snippet_package_version_id" integer DEFAULT '0' NOT NULL,
	"snippet_version_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("snippet_package_item_id")
);
CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "snippet_package_version" (
	"snippet_package_version_id" integer DEFAULT nextval('snippet_package_version_pk_seq'::text) NOT NULL,
	"snippet_package_id" integer DEFAULT '0' NOT NULL,
	"changes" text,
	"version" text,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("snippet_package_version_id")
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
	PRIMARY KEY ("snippet_version_id")
);
CREATE TABLE "stats_agg_logo_by_day" (
	"day" integer,
	"count" integer
);
CREATE TABLE "stats_agg_logo_by_group" (
	"day" integer,
	"group_id" integer,
	"count" integer
);
CREATE TABLE "stats_agg_pages_by_browser" (
	"browser" character varying(8),
	"count" integer
);
CREATE TABLE "stats_agg_pages_by_day" (
	"day" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_agg_pages_by_day_old" (
	"day" integer,
	"count" integer
);
CREATE TABLE "stats_agg_site_by_day" (
	"day" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_agg_site_by_group" (
	"day" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_agr_filerelease" (
	"filerelease_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_agr_project" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"group_ranking" integer DEFAULT '0' NOT NULL,
	"group_metric" double precision DEFAULT '0.00000' NOT NULL,
	"developers" integer DEFAULT '0' NOT NULL,
	"file_releases" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	"site_views" integer DEFAULT '0' NOT NULL,
	"logo_views" integer DEFAULT '0' NOT NULL,
	"msg_posted" integer DEFAULT '0' NOT NULL,
	"msg_uniq_auth" integer DEFAULT '0' NOT NULL,
	"bugs_opened" integer DEFAULT '0' NOT NULL,
	"bugs_closed" integer DEFAULT '0' NOT NULL,
	"support_opened" integer DEFAULT '0' NOT NULL,
	"support_closed" integer DEFAULT '0' NOT NULL,
	"patches_opened" integer DEFAULT '0' NOT NULL,
	"patches_closed" integer DEFAULT '0' NOT NULL,
	"tasks_opened" integer DEFAULT '0' NOT NULL,
	"tasks_closed" integer DEFAULT '0' NOT NULL,
	"help_requests" integer DEFAULT '0' NOT NULL,
	"cvs_checkouts" integer DEFAULT '0' NOT NULL,
	"cvs_commits" integer DEFAULT '0' NOT NULL,
	"cvs_adds" integer DEFAULT '0' NOT NULL
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
CREATE TABLE "stats_project" (
	"month" integer DEFAULT '0' NOT NULL,
	"week" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"group_ranking" integer DEFAULT '0' NOT NULL,
	"group_metric" double precision DEFAULT '0.00000' NOT NULL,
	"developers" integer DEFAULT '0' NOT NULL,
	"file_releases" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	"site_views" integer DEFAULT '0' NOT NULL,
	"subdomain_views" integer DEFAULT '0' NOT NULL,
	"msg_posted" integer DEFAULT '0' NOT NULL,
	"msg_uniq_auth" integer DEFAULT '0' NOT NULL,
	"bugs_opened" integer DEFAULT '0' NOT NULL,
	"bugs_closed" integer DEFAULT '0' NOT NULL,
	"support_opened" integer DEFAULT '0' NOT NULL,
	"support_closed" integer DEFAULT '0' NOT NULL,
	"patches_opened" integer DEFAULT '0' NOT NULL,
	"patches_closed" integer DEFAULT '0' NOT NULL,
	"tasks_opened" integer DEFAULT '0' NOT NULL,
	"tasks_closed" integer DEFAULT '0' NOT NULL,
	"help_requests" integer DEFAULT '0' NOT NULL,
	"cvs_checkouts" integer DEFAULT '0' NOT NULL,
	"cvs_commits" integer DEFAULT '0' NOT NULL,
	"cvs_adds" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_project_tmp" (
	"month" integer DEFAULT '0' NOT NULL,
	"week" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"group_ranking" integer DEFAULT '0' NOT NULL,
	"group_metric" double precision DEFAULT '0.00000' NOT NULL,
	"developers" integer DEFAULT '0' NOT NULL,
	"file_releases" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	"site_views" integer DEFAULT '0' NOT NULL,
	"subdomain_views" integer DEFAULT '0' NOT NULL,
	"msg_posted" integer DEFAULT '0' NOT NULL,
	"msg_uniq_auth" integer DEFAULT '0' NOT NULL,
	"bugs_opened" integer DEFAULT '0' NOT NULL,
	"bugs_closed" integer DEFAULT '0' NOT NULL,
	"support_opened" integer DEFAULT '0' NOT NULL,
	"support_closed" integer DEFAULT '0' NOT NULL,
	"patches_opened" integer DEFAULT '0' NOT NULL,
	"patches_closed" integer DEFAULT '0' NOT NULL,
	"tasks_opened" integer DEFAULT '0' NOT NULL,
	"tasks_closed" integer DEFAULT '0' NOT NULL,
	"help_requests" integer DEFAULT '0' NOT NULL,
	"cvs_checkouts" integer DEFAULT '0' NOT NULL,
	"cvs_commits" integer DEFAULT '0' NOT NULL,
	"cvs_adds" integer DEFAULT '0' NOT NULL
);
CREATE TABLE "stats_site" (
	"month" integer DEFAULT '0' NOT NULL,
	"week" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"site_views" integer DEFAULT '0' NOT NULL,
	"subdomain_views" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	"uniq_users" integer DEFAULT '0' NOT NULL,
	"sessions" integer DEFAULT '0' NOT NULL,
	"total_users" integer DEFAULT '0' NOT NULL,
	"new_users" integer DEFAULT '0' NOT NULL,
	"new_projects" integer DEFAULT '0' NOT NULL
);
CREATE SEQUENCE "support_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support" (
	"support_id" integer DEFAULT nextval('support_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"support_status_id" integer DEFAULT '0' NOT NULL,
	"support_category_id" integer DEFAULT '0' NOT NULL,
	"priority" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"assigned_to" integer DEFAULT '0' NOT NULL,
	"open_date" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"close_date" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("support_id")
);
CREATE SEQUENCE "support_canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support_canned_responses" (
	"support_canned_id" integer DEFAULT nextval('support_canned_responses_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"title" text,
	"body" text,
	PRIMARY KEY ("support_canned_id")
);
CREATE SEQUENCE "support_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support_category" (
	"support_category_id" integer DEFAULT nextval('support_category_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"category_name" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("support_category_id")
);
CREATE SEQUENCE "support_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support_history" (
	"support_history_id" integer DEFAULT nextval('support_history_pk_seq'::text) NOT NULL,
	"support_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	PRIMARY KEY ("support_history_id")
);
CREATE SEQUENCE "support_messages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support_messages" (
	"support_message_id" integer DEFAULT nextval('support_messages_pk_seq'::text) NOT NULL,
	"support_id" integer DEFAULT '0' NOT NULL,
	"from_email" text,
	"date" integer DEFAULT '0' NOT NULL,
	"body" text,
	PRIMARY KEY ("support_message_id")
);
CREATE SEQUENCE "support_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "support_status" (
	"support_status_id" integer DEFAULT nextval('support_status_pk_seq'::text) NOT NULL,
	"status_name" text,
	PRIMARY KEY ("support_status_id")
);
CREATE SEQUENCE "supported_languages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "supported_languages" (
	"language_id" integer DEFAULT nextval('supported_languages_pk_seq'::text) NOT NULL,
	"name" text,
	"filename" text,
	"classname" text,
	"language_code" character(2),
	PRIMARY KEY ("language_id")
);
CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "survey_question_types" (
	"id" integer DEFAULT nextval('survey_question_types_pk_seq'::text) NOT NULL,
	"type" text DEFAULT '' NOT NULL,
	PRIMARY KEY ("id")
);
CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "survey_questions" (
	"question_id" integer DEFAULT nextval('survey_questions_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"question" text DEFAULT '' NOT NULL,
	"question_type" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("question_id")
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
	PRIMARY KEY ("survey_id")
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
	PRIMARY KEY ("user_id")
);
CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "themes" (
	"theme_id" integer DEFAULT nextval('themes_pk_seq'::text) NOT NULL,
	"dirname" character varying(80),
	"fullname" character varying(80),
	PRIMARY KEY ("theme_id")
);
CREATE TABLE "tmp_projs_releases_tmp" (
	"year" integer DEFAULT '0' NOT NULL,
	"month" integer DEFAULT '0' NOT NULL,
	"total_proj" integer DEFAULT '0' NOT NULL,
	"total_releases" integer DEFAULT '0' NOT NULL
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
	PRIMARY KEY ("trove_cat_id")
);
CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "trove_group_link" (
	"trove_group_id" integer DEFAULT nextval('trove_group_link_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_version" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_root" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("trove_group_id")
);
CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "trove_treesums" (
	"trove_treesums_id" integer DEFAULT nextval('trove_treesums_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"limit_1" integer DEFAULT '0' NOT NULL,
	"subprojects" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("trove_treesums_id")
);
CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "user_bookmarks" (
	"bookmark_id" integer DEFAULT nextval('user_bookmarks_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"bookmark_url" text,
	"bookmark_title" text,
	PRIMARY KEY ("bookmark_id")
);
CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "user_diary" (
	"id" integer DEFAULT nextval('user_diary_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"date_posted" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	"is_public" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("id")
);
CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "user_diary_monitor" (
	"monitor_id" integer DEFAULT nextval('user_diary_monitor_pk_seq'::text) NOT NULL,
	"monitored_user" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	PRIMARY KEY ("monitor_id")
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
	PRIMARY KEY ("user_group_id")
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
	PRIMARY KEY ("ranking")
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
	PRIMARY KEY ("ranking")
);
CREATE TABLE "user_preferences" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"preference_name" character varying(20),
	"preference_value" character varying(20),
	"set_date" integer DEFAULT '0' NOT NULL
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
	PRIMARY KEY ("user_id")
);
CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
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
CREATE  INDEX "bug_group_id" on "bug" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "bug_groupid_statusid" on "bug" using btree ( "group_id" "int4_ops", "status_id" "int4_ops" );
CREATE  INDEX "bug_groupid_assignedto_statusid" on "bug" using btree ( "group_id" "int4_ops", "assigned_to" "int4_ops", "status_id" "int4_ops" );
CREATE  INDEX "bug_bug_dependencies_bug_id" on "bug_bug_dependencies" using btree ( "bug_id" "int4_ops" );
CREATE  INDEX "bug_bug_is_dependent_on_task_id" on "bug_bug_dependencies" using btree ( "is_dependent_on_bug_id" "int4_ops" );
CREATE  INDEX "bug_canned_response_group_id" on "bug_canned_responses" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "bug_category_group_id" on "bug_category" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "bug_group_group_id" on "bug_group" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "bug_history_bug_id" on "bug_history" using btree ( "bug_id" "int4_ops" );
CREATE  INDEX "bug_task_dependencies_bug_id" on "bug_task_dependencies" using btree ( "bug_id" "int4_ops" );
CREATE  INDEX "bug_task_is_dependent_on_task_i" on "bug_task_dependencies" using btree ( "is_dependent_on_task_id" "int4_ops" );
CREATE  INDEX "db_images_group" on "db_images" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "doc_group_doc_group" on "doc_data" using btree ( "doc_group" "int4_ops" );
CREATE  INDEX "doc_groups_group" on "doc_groups" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "filemodule_monitor_id" on "filemodule_monitor" using btree ( "filemodule_id" "int4_ops" );
CREATE  INDEX "forum_forumid_msgid" on "forum" using btree ( "group_forum_id" "int4_ops", "msg_id" "int4_ops" );
CREATE  INDEX "forum_group_forum_id" on "forum" using btree ( "group_forum_id" "int4_ops" );
CREATE  INDEX "forum_forumid_isfollowupto" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops" );
CREATE  INDEX "forum_forumid_threadid_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "thread_id" "int4_ops", "most_recent_date" "int4_ops" );
CREATE  INDEX "forum_threadid_isfollowupto" on "forum" using btree ( "thread_id" "int4_ops", "is_followup_to" "int4_ops" );
CREATE  INDEX "forum_forumid_isfollto_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops", "most_recent_date" "int4_ops" );
CREATE  INDEX "forum_group_list_group_id" on "forum_group_list" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "forum_monitor_combo_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops", "user_id" "int4_ops" );
CREATE  INDEX "forum_monitor_thread_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops" );
CREATE  INDEX "foundry_news_foundry_approved_d" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops", "approve_date" "int4_ops" );
CREATE  INDEX "foundry_news_foundry_approved" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops" );
CREATE  INDEX "foundry_news_foundry" on "foundry_news" using btree ( "foundry_id" "int4_ops" );
CREATE  INDEX "foundrynews_foundry_date_approv" on "foundry_news" using btree ( "foundry_id" "int4_ops", "approve_date" "int4_ops", "is_approved" "int4_ops" );
CREATE  INDEX "foundry_project_group_rank" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops", "rank" "int4_ops" );
CREATE  INDEX "foundry_project_group" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "foundry_projects_foundry" on "foundry_projects" using btree ( "foundry_id" "int4_ops" );
CREATE  INDEX "downloads_http_idx" on "frs_dlstats_agg" using btree ( "downloads_http" "int4_ops" );
CREATE  INDEX "downloads_ftp_idx" on "frs_dlstats_agg" using btree ( "downloads_ftp" "int4_ops" );
CREATE  INDEX "file_id_idx" on "frs_dlstats_agg" using btree ( "file_id" "int4_ops" );
CREATE  INDEX "day_idx" on "frs_dlstats_agg" using btree ( "day" "int4_ops" );
CREATE  INDEX "dlstats_file_down" on "frs_dlstats_file_agg" using btree ( "downloads" "int4_ops" );
CREATE  INDEX "dlstats_file_file_id" on "frs_dlstats_file_agg" using btree ( "file_id" "int4_ops" );
CREATE  INDEX "dlstats_file_day" on "frs_dlstats_file_agg" using btree ( "day" "int4_ops" );
CREATE  INDEX "stats_agr_tmp_fid" on "frs_dlstats_filetotal_agg" using btree ( "file_id" "int4_ops" );
CREATE  INDEX "frs_dlstats_filetotal_agg_old_f" on "frs_dlstats_filetotal_agg_old" using btree ( "file_id" "int4_ops" );
CREATE  INDEX "frsdlstatsgroupagg_day_dls" on "frs_dlstats_group_agg" using btree ( "day" "int4_ops", "downloads" "int4_ops" );
CREATE  INDEX "group_id_idx" on "frs_dlstats_group_agg" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "frs_dlstats_group_agg_day" on "frs_dlstats_group_agg" using btree ( "day" "int4_ops" );
CREATE  INDEX "stats_agr_tmp_gid" on "frs_dlstats_grouptotal_agg" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "frs_file_name" on "frs_file" using btree ( "filename" "text_ops" );
CREATE  INDEX "frs_file_date" on "frs_file" using btree ( "post_date" "int4_ops" );
CREATE  INDEX "frs_file_processor" on "frs_file" using btree ( "processor_id" "int4_ops" );
CREATE  INDEX "frs_file_release_id" on "frs_file" using btree ( "release_id" "int4_ops" );
CREATE  INDEX "frs_file_type" on "frs_file" using btree ( "type_id" "int4_ops" );
CREATE  INDEX "package_group_id" on "frs_package" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "frs_release_package" on "frs_release" using btree ( "package_id" "int4_ops" );
CREATE  INDEX "frs_release_date" on "frs_release" using btree ( "release_date" "int4_ops" );
CREATE  INDEX "frs_release_by" on "frs_release" using btree ( "released_by" "int4_ops" );
CREATE  INDEX "group_cvs_history_group_id" on "group_cvs_history" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "user_name_idx" on "group_cvs_history" using btree ( "user_name" "varchar_ops" );
CREATE  INDEX "group_history_group_id" on "group_history" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "groups_unix" on "groups" using btree ( "unix_group_name" "varchar_ops" );
CREATE  INDEX "groups_type" on "groups" using btree ( "type" "int4_ops" );
CREATE  INDEX "groups_public" on "groups" using btree ( "is_public" "int4_ops" );
CREATE  INDEX "groups_status" on "groups" using btree ( "status" "bpchar_ops" );
CREATE  INDEX "mail_group_list_group" on "mail_group_list" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "news_bytes_group" on "news_bytes" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "news_bytes_approved" on "news_bytes" using btree ( "is_approved" "int4_ops" );
CREATE  INDEX "news_bytes_forum" on "news_bytes" using btree ( "forum_id" "int4_ops" );
CREATE  INDEX "news_group_date" on "news_bytes" using btree ( "group_id" "int4_ops", "date" "int4_ops" );
CREATE  INDEX "news_approved_date" on "news_bytes" using btree ( "is_approved" "int4_ops", "date" "int4_ops" );
CREATE  INDEX "patch_group_id" on "patch" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "patch_groupid_assignedto_status" on "patch" using btree ( "group_id" "int4_ops", "assigned_to" "int4_ops", "patch_status_id" "int4_ops" );
CREATE  INDEX "patch_groupid_assignedto" on "patch" using btree ( "group_id" "int4_ops", "assigned_to" "int4_ops" );
CREATE  INDEX "patch_groupid_status" on "patch" using btree ( "group_id" "int4_ops", "patch_status_id" "int4_ops" );
CREATE  INDEX "patch_group_group_id" on "patch_category" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "patch_history_patch_id" on "patch_history" using btree ( "patch_id" "int4_ops" );
CREATE  INDEX "people_job_group_id" on "people_job" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "project_assigned_to_assigned_to" on "project_assigned_to" using btree ( "assigned_to_id" "int4_ops" );
CREATE  INDEX "project_assigned_to_task_id" on "project_assigned_to" using btree ( "project_task_id" "int4_ops" );
CREATE  INDEX "project_is_dependent_on_task_id" on "project_dependencies" using btree ( "is_dependent_on_task_id" "int4_ops" );
CREATE  INDEX "project_dependencies_task_id" on "project_dependencies" using btree ( "project_task_id" "int4_ops" );
CREATE  INDEX "project_group_list_group_id" on "project_group_list" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "project_history_task_id" on "project_history" using btree ( "project_task_id" "int4_ops" );
CREATE  INDEX "project_metric_group" on "project_metric" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "project_task_group_project_id" on "project_task" using btree ( "group_project_id" "int4_ops" );
CREATE  INDEX "projecttask_projid_status" on "project_task" using btree ( "group_project_id" "int4_ops", "status_id" "int4_ops" );
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
CREATE  INDEX "stats_agr_filerelease_group_id" on "stats_agr_filerelease" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "stats_agr_filerelease_filerelea" on "stats_agr_filerelease" using btree ( "filerelease_id" "int4_ops" );
CREATE  INDEX "project_agr_log_group" on "stats_agr_project" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "ftpdl_group_id" on "stats_ftp_downloads" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "ftpdl_fid" on "stats_ftp_downloads" using btree ( "filerelease_id" "int4_ops" );
CREATE  INDEX "ftpdl_day" on "stats_ftp_downloads" using btree ( "day" "int4_ops" );
CREATE  INDEX "httpdl_group_id" on "stats_http_downloads" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "httpdl_fid" on "stats_http_downloads" using btree ( "filerelease_id" "int4_ops" );
CREATE  INDEX "httpdl_day" on "stats_http_downloads" using btree ( "day" "int4_ops" );
CREATE  INDEX "archive_project_monthday" on "stats_project" using btree ( "month" "int4_ops", "day" "int4_ops" );
CREATE  INDEX "project_log_group" on "stats_project" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "archive_project_week" on "stats_project" using btree ( "week" "int4_ops" );
CREATE  INDEX "archive_project_day" on "stats_project" using btree ( "day" "int4_ops" );
CREATE  INDEX "archive_project_month" on "stats_project" using btree ( "month" "int4_ops" );
CREATE  INDEX "stats_project_tmp_group_id" on "stats_project_tmp" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "project_stats_week" on "stats_project_tmp" using btree ( "week" "int4_ops" );
CREATE  INDEX "project_stats_month" on "stats_project_tmp" using btree ( "month" "int4_ops" );
CREATE  INDEX "project_stats_day" on "stats_project_tmp" using btree ( "day" "int4_ops" );
CREATE  INDEX "stats_site_monthday" on "stats_site" using btree ( "month" "int4_ops", "day" "int4_ops" );
CREATE  INDEX "stats_site_week" on "stats_site" using btree ( "week" "int4_ops" );
CREATE  INDEX "stats_site_day" on "stats_site" using btree ( "day" "int4_ops" );
CREATE  INDEX "stats_site_month" on "stats_site" using btree ( "month" "int4_ops" );
CREATE  INDEX "support_group_id" on "support" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "support_groupid_assignedto" on "support" using btree ( "group_id" "int4_ops", "assigned_to" "int4_ops" );
CREATE  INDEX "support_groupid_assignedto_stat" on "support" using btree ( "group_id" "int4_ops", "assigned_to" "int4_ops", "support_status_id" "int4_ops" );
CREATE  INDEX "support_groupid_status" on "support" using btree ( "group_id" "int4_ops", "support_status_id" "int4_ops" );
CREATE  INDEX "support_canned_response_group_i" on "support_canned_responses" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "support_group_group_id" on "support_category" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "support_history_support_id" on "support_history" using btree ( "support_id" "int4_ops" );
CREATE  INDEX "support_messages_support_id" on "support_messages" using btree ( "support_id" "int4_ops" );
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
CREATE  INDEX "user_metric0_user_id" on "user_metric0" using btree ( "user_id" "int4_ops" );
CREATE  INDEX "user_pref_user_id" on "user_preferences" using btree ( "user_id" "int4_ops" );
CREATE  INDEX "user_ratings_rated_by" on "user_ratings" using btree ( "rated_by" "int4_ops" );
CREATE  INDEX "user_ratings_user_id" on "user_ratings" using btree ( "user_id" "int4_ops" );
CREATE  INDEX "users_status" on "users" using btree ( "status" "bpchar_ops" );
CREATE  INDEX "user_user" on "users" using btree ( "status" "bpchar_ops" );
CREATE  INDEX "idx_users_username" on "users" using btree ( "user_name" "text_ops" );
CREATE  INDEX "users_user_pw" on "users" using btree ( "user_pw" "varchar_ops" );
CREATE  INDEX "troveagg_trovecatid" on "trove_agg" using btree ( "trove_cat_id" "int4_ops" );
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
CREATE CONSTRAINT TRIGGER "bug_group_group_fk" AFTER INSERT OR UPDATE ON "bug_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_group_group_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_group_group_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_category_group_fk" AFTER INSERT OR UPDATE ON "bug_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_category_group_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_category_group_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "bug_submitted_by_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_submitted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_submitted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_assigned_to_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_assigned_to_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_assigned_to_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "bug_status_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');
CREATE CONSTRAINT TRIGGER "bug_status_fk" AFTER DELETE ON "bug_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');
CREATE CONSTRAINT TRIGGER "bug_status_fk" AFTER UPDATE ON "bug_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');
CREATE CONSTRAINT TRIGGER "bug_category_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');
CREATE CONSTRAINT TRIGGER "bug_category_fk" AFTER DELETE ON "bug_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');
CREATE CONSTRAINT TRIGGER "bug_category_fk" AFTER UPDATE ON "bug_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');
CREATE CONSTRAINT TRIGGER "bug_resolution_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');
CREATE CONSTRAINT TRIGGER "bug_resolution_fk" AFTER DELETE ON "bug_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');
CREATE CONSTRAINT TRIGGER "bug_resolution_fk" AFTER UPDATE ON "bug_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');
CREATE CONSTRAINT TRIGGER "bug_group_fk" AFTER INSERT OR UPDATE ON "bug"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');
CREATE CONSTRAINT TRIGGER "bug_group_fk" AFTER DELETE ON "bug_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');
CREATE CONSTRAINT TRIGGER "bug_group_fk" AFTER UPDATE ON "bug_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');
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
CREATE CONSTRAINT TRIGGER "patch_status_id_fk" AFTER INSERT OR UPDATE ON "patch"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');
CREATE CONSTRAINT TRIGGER "patch_status_id_fk" AFTER DELETE ON "patch_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');
CREATE CONSTRAINT TRIGGER "patch_status_id_fk" AFTER UPDATE ON "patch_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');
CREATE CONSTRAINT TRIGGER "patch_category_id_fk" AFTER INSERT OR UPDATE ON "patch"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');
CREATE CONSTRAINT TRIGGER "patch_category_id_fk" AFTER DELETE ON "patch_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');
CREATE CONSTRAINT TRIGGER "patch_category_id_fk" AFTER UPDATE ON "patch_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');
CREATE CONSTRAINT TRIGGER "patch_submitted_by_fk" AFTER INSERT OR UPDATE ON "patch"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_submitted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_submitted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_assigned_to_fk" AFTER INSERT OR UPDATE ON "patch"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_assigned_to_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_assigned_to_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "patch_category_group_id_fk" AFTER INSERT OR UPDATE ON "patch_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "patch_category_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "patch_category_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "support_status_id_fk" AFTER INSERT OR UPDATE ON "support"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');
CREATE CONSTRAINT TRIGGER "support_status_id_fk" AFTER DELETE ON "support_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');
CREATE CONSTRAINT TRIGGER "support_status_id_fk" AFTER UPDATE ON "support_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');
CREATE CONSTRAINT TRIGGER "support_category_id_fk" AFTER INSERT OR UPDATE ON "support"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');
CREATE CONSTRAINT TRIGGER "support_category_id_fk" AFTER DELETE ON "support_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');
CREATE CONSTRAINT TRIGGER "support_category_id_fk" AFTER UPDATE ON "support_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');
CREATE CONSTRAINT TRIGGER "support_submitted_by_fk" AFTER INSERT OR UPDATE ON "support"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "support_submitted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "support_submitted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');
CREATE CONSTRAINT TRIGGER "support_assigned_to_fk" AFTER INSERT OR UPDATE ON "support"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "support_assigned_to_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "support_assigned_to_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');
CREATE CONSTRAINT TRIGGER "support_category_group_id_fk" AFTER INSERT OR UPDATE ON "support_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "support_category_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "support_category_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');
CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER INSERT OR UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');
CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER DELETE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');
CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER UPDATE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');


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

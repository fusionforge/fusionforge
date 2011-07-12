--
-- TOC Entry ID 2 (OID 18724)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 176 (OID 18743)
--
-- Name: canned_responses Type: TABLE Owner: tperdue
--

CREATE TABLE "canned_responses" (
	"response_id" integer DEFAULT nextval('canned_responses_pk_seq'::text) NOT NULL,
	"response_title" character varying(25),
	"response_text" text,
	Constraint "canned_responses_pkey" Primary Key ("response_id")
);

--
-- TOC Entry ID 4 (OID 18774)
--
-- Name: db_images_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "db_images_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 177 (OID 18793)
--
-- Name: db_images Type: TABLE Owner: tperdue
--

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
	"upload_date" integer DEFAULT '0',
	"version" integer DEFAULT '0',
	Constraint "db_images_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 6 (OID 18840)
--
-- Name: doc_data_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_data_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 178 (OID 18859)
--
-- Name: doc_data Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 8 (OID 18905)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 179 (OID 18924)
--
-- Name: doc_groups Type: TABLE Owner: tperdue
--

CREATE TABLE "doc_groups" (
	"doc_group" integer DEFAULT nextval('doc_groups_pk_seq'::text) NOT NULL,
	"groupname" character varying(255) DEFAULT '' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "doc_groups_pkey" Primary Key ("doc_group")
);

--
-- TOC Entry ID 10 (OID 18942)
--
-- Name: doc_states_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 180 (OID 18961)
--
-- Name: doc_states Type: TABLE Owner: tperdue
--

CREATE TABLE "doc_states" (
	"stateid" integer DEFAULT nextval('doc_states_pk_seq'::text) NOT NULL,
	"name" character varying(255) DEFAULT '' NOT NULL,
	Constraint "doc_states_pkey" Primary Key ("stateid")
);

--
-- TOC Entry ID 12 (OID 18977)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 181 (OID 18996)
--
-- Name: filemodule_monitor Type: TABLE Owner: tperdue
--

CREATE TABLE "filemodule_monitor" (
	"id" integer DEFAULT nextval('filemodule_monitor_pk_seq'::text) NOT NULL,
	"filemodule_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "filemodule_monitor_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 14 (OID 19014)
--
-- Name: forum_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 182 (OID 19033)
--
-- Name: forum Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 183 (OID 19080)
--
-- Name: forum_agg_msg_count Type: TABLE Owner: tperdue
--

CREATE TABLE "forum_agg_msg_count" (
	"group_forum_id" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL,
	Constraint "forum_agg_msg_count_pkey" Primary Key ("group_forum_id")
);

--
-- TOC Entry ID 16 (OID 19096)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 184 (OID 19115)
--
-- Name: forum_group_list Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 18 (OID 19154)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 185 (OID 19173)
--
-- Name: forum_monitored_forums Type: TABLE Owner: tperdue
--

CREATE TABLE "forum_monitored_forums" (
	"monitor_id" integer DEFAULT nextval('forum_monitored_forums_pk_seq'::text) NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "forum_monitored_forums_pkey" Primary Key ("monitor_id")
);

--
-- TOC Entry ID 20 (OID 19191)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 186 (OID 19210)
--
-- Name: forum_saved_place Type: TABLE Owner: tperdue
--

CREATE TABLE "forum_saved_place" (
	"saved_place_id" integer DEFAULT nextval('forum_saved_place_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"forum_id" integer DEFAULT '0' NOT NULL,
	"save_date" integer DEFAULT '0' NOT NULL,
	Constraint "forum_saved_place_pkey" Primary Key ("saved_place_id")
);

--
-- TOC Entry ID 187 (OID 19230)
--
-- Name: foundry_data Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 22 (OID 19268)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 188 (OID 19287)
--
-- Name: foundry_news Type: TABLE Owner: tperdue
--

CREATE TABLE "foundry_news" (
	"foundry_news_id" integer DEFAULT nextval('foundry_news_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"news_id" integer DEFAULT '0' NOT NULL,
	"approve_date" integer DEFAULT '0' NOT NULL,
	"is_approved" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_news_pkey" Primary Key ("foundry_news_id")
);

--
-- TOC Entry ID 24 (OID 19309)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_preferred_projec_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 189 (OID 19328)
--
-- Name: foundry_preferred_projects Type: TABLE Owner: tperdue
--

CREATE TABLE "foundry_preferred_projects" (
	"foundry_project_id" integer DEFAULT nextval('foundry_preferred_projec_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"rank" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_preferred_projects_pkey" Primary Key ("foundry_project_id")
);

--
-- TOC Entry ID 26 (OID 19348)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_projects_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 190 (OID 19367)
--
-- Name: foundry_projects Type: TABLE Owner: tperdue
--

CREATE TABLE "foundry_projects" (
	"id" integer DEFAULT nextval('foundry_projects_pk_seq'::text) NOT NULL,
	"foundry_id" integer DEFAULT '0' NOT NULL,
	"project_id" integer DEFAULT '0' NOT NULL,
	Constraint "foundry_projects_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 28 (OID 19385)
--
-- Name: frs_file_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_file_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 191 (OID 19404)
--
-- Name: frs_file Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 30 (OID 19446)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 192 (OID 19465)
--
-- Name: frs_filetype Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_filetype" (
	"type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_filetype_pkey" Primary Key ("type_id")
);

--
-- TOC Entry ID 32 (OID 19495)
--
-- Name: frs_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 193 (OID 19514)
--
-- Name: frs_package Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_package" (
	"package_id" integer DEFAULT nextval('frs_package_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"name" text,
	"status_id" integer DEFAULT '0' NOT NULL,
	Constraint "frs_package_pkey" Primary Key ("package_id")
);

--
-- TOC Entry ID 34 (OID 19548)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 194 (OID 19567)
--
-- Name: frs_processor Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_processor" (
	"processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_processor_pkey" Primary Key ("processor_id")
);

--
-- TOC Entry ID 36 (OID 19597)
--
-- Name: frs_release_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_release_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 195 (OID 19616)
--
-- Name: frs_release Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 38 (OID 19658)
--
-- Name: frs_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 196 (OID 19677)
--
-- Name: frs_status Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_status" (
	"status_id" integer DEFAULT nextval('frs_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 40 (OID 19707)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_cvs_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 42 (OID 19726)
--
-- Name: group_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 197 (OID 19745)
--
-- Name: group_history Type: TABLE Owner: tperdue
--

CREATE TABLE "group_history" (
	"group_history_id" integer DEFAULT nextval('group_history_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer,
	Constraint "group_history_pkey" Primary Key ("group_history_id")
);

--
-- TOC Entry ID 44 (OID 19783)
--
-- Name: group_type_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_type_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 198 (OID 19802)
--
-- Name: group_type Type: TABLE Owner: tperdue
--

CREATE TABLE "group_type" (
	"type_id" integer DEFAULT nextval('group_type_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "group_type_pkey" Primary Key ("type_id")
);

--
-- TOC Entry ID 46 (OID 19832)
--
-- Name: groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 199 (OID 19851)
--
-- Name: groups Type: TABLE Owner: tperdue
--

CREATE TABLE "groups" (
	"group_id" integer DEFAULT nextval('groups_pk_seq'::text) NOT NULL,
	"group_name" character varying(40),
	"homepage" character varying(128),
	"is_public" integer DEFAULT '0' NOT NULL,
	"status" character(1) DEFAULT 'A' NOT NULL,
	"unix_group_name" character varying(30) DEFAULT '' NOT NULL,
	"unix_box" character varying(20) DEFAULT 'shell' NOT NULL,
	"http_domain" character varying(80),
	"short_description" character varying(255),
	"cvs_box" character varying(20) DEFAULT 'cvs' NOT NULL,
	"license" character varying(16),
	"register_purpose" text,
	"license_other" text,
	"register_time" integer DEFAULT '0' NOT NULL,
	"rand_hash" text,
	"use_mail" integer DEFAULT '1' NOT NULL,
	"use_survey" integer DEFAULT '1' NOT NULL,
	"use_forum" integer DEFAULT '1' NOT NULL,
	"use_pm" integer DEFAULT '1' NOT NULL,
	"use_cvs" integer DEFAULT '1' NOT NULL,
	"use_news" integer DEFAULT '1' NOT NULL,
	"type" integer DEFAULT '1' NOT NULL,
	"use_docman" integer DEFAULT '1' NOT NULL,
	"new_task_address" text DEFAULT '' NOT NULL,
	"send_all_tasks" integer DEFAULT '0' NOT NULL,
	"use_pm_depend_box" integer DEFAULT '1' NOT NULL,
	Constraint "groups_pkey" Primary Key ("group_id")
);

--
-- TOC Entry ID 48 (OID 19977)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "mail_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 201 (OID 19996)
--
-- Name: mail_group_list Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 50 (OID 20036)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "news_bytes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 202 (OID 20055)
--
-- Name: news_bytes Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 52 (OID 20096)
--
-- Name: people_job_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 203 (OID 20115)
--
-- Name: people_job Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 54 (OID 20156)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 204 (OID 20175)
--
-- Name: people_job_category Type: TABLE Owner: tperdue
--

CREATE TABLE "people_job_category" (
	"category_id" integer DEFAULT nextval('people_job_category_pk_seq'::text) NOT NULL,
	"name" text,
	"private_flag" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_category_pkey" Primary Key ("category_id")
);

--
-- TOC Entry ID 56 (OID 20207)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 205 (OID 20226)
--
-- Name: people_job_inventory Type: TABLE Owner: tperdue
--

CREATE TABLE "people_job_inventory" (
	"job_inventory_id" integer DEFAULT nextval('people_job_inventory_pk_seq'::text) NOT NULL,
	"job_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_job_inventory_pkey" Primary Key ("job_inventory_id")
);

--
-- TOC Entry ID 58 (OID 20248)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 206 (OID 20267)
--
-- Name: people_job_status Type: TABLE Owner: tperdue
--

CREATE TABLE "people_job_status" (
	"status_id" integer DEFAULT nextval('people_job_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_job_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 60 (OID 20297)
--
-- Name: people_skill_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 207 (OID 20316)
--
-- Name: people_skill Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill" (
	"skill_id" integer DEFAULT nextval('people_skill_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_pkey" Primary Key ("skill_id")
);

--
-- TOC Entry ID 62 (OID 20346)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 208 (OID 20365)
--
-- Name: people_skill_inventory Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill_inventory" (
	"skill_inventory_id" integer DEFAULT nextval('people_skill_inventory_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"skill_id" integer DEFAULT '0' NOT NULL,
	"skill_level_id" integer DEFAULT '0' NOT NULL,
	"skill_year_id" integer DEFAULT '0' NOT NULL,
	Constraint "people_skill_inventory_pkey" Primary Key ("skill_inventory_id")
);

--
-- TOC Entry ID 64 (OID 20387)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 209 (OID 20406)
--
-- Name: people_skill_level Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill_level" (
	"skill_level_id" integer DEFAULT nextval('people_skill_level_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_level_pkey" Primary Key ("skill_level_id")
);

--
-- TOC Entry ID 66 (OID 20436)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 210 (OID 20455)
--
-- Name: people_skill_year Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill_year" (
	"skill_year_id" integer DEFAULT nextval('people_skill_year_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_year_pkey" Primary Key ("skill_year_id")
);

--
-- TOC Entry ID 68 (OID 20485)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 211 (OID 20504)
--
-- Name: project_assigned_to Type: TABLE Owner: tperdue
--

CREATE TABLE "project_assigned_to" (
	"project_assigned_id" integer DEFAULT nextval('project_assigned_to_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"assigned_to_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_assigned_to_pkey" Primary Key ("project_assigned_id")
);

--
-- TOC Entry ID 70 (OID 20522)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 212 (OID 20541)
--
-- Name: project_dependencies Type: TABLE Owner: tperdue
--

CREATE TABLE "project_dependencies" (
	"project_depend_id" integer DEFAULT nextval('project_dependencies_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"is_dependent_on_task_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_dependencies_pkey" Primary Key ("project_depend_id")
);

--
-- TOC Entry ID 72 (OID 20559)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 213 (OID 20578)
--
-- Name: project_group_list Type: TABLE Owner: tperdue
--

CREATE TABLE "project_group_list" (
	"group_project_id" integer DEFAULT nextval('project_group_list_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"project_name" text DEFAULT '' NOT NULL,
	"is_public" integer DEFAULT '0' NOT NULL,
	"description" text,
	Constraint "project_group_list_pkey" Primary Key ("group_project_id")
);

--
-- TOC Entry ID 74 (OID 20614)
--
-- Name: project_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 214 (OID 20633)
--
-- Name: project_history Type: TABLE Owner: tperdue
--

CREATE TABLE "project_history" (
	"project_history_id" integer DEFAULT nextval('project_history_pk_seq'::text) NOT NULL,
	"project_task_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	Constraint "project_history_pkey" Primary Key ("project_history_id")
);

--
-- TOC Entry ID 76 (OID 20672)
--
-- Name: project_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 215 (OID 20691)
--
-- Name: project_metric Type: TABLE Owner: tperdue
--

CREATE TABLE "project_metric" (
	"ranking" integer DEFAULT nextval('project_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_metric_pkey" Primary Key ("ranking")
);

--
-- TOC Entry ID 78 (OID 20708)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE Owner: tperdue
--

-- CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 216 (OID 20727)
--
-- Name: project_metric_tmp1 Type: TABLE Owner: tperdue
--

-- CREATE TABLE "project_metric_tmp1" (
--	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
-- 	"group_id" integer DEFAULT '0' NOT NULL,
-- 	"value" double precision,
-- 	Constraint "project_metric_tmp1_pkey" Primary Key ("ranking")
-- );

--
-- TOC Entry ID 80 (OID 20744)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_weekly_tm_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 82 (OID 20763)
--
-- Name: project_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 217 (OID 20782)
--
-- Name: project_status Type: TABLE Owner: tperdue
--

CREATE TABLE "project_status" (
	"status_id" integer DEFAULT nextval('project_status_pk_seq'::text) NOT NULL,
	"status_name" text DEFAULT '' NOT NULL,
	Constraint "project_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 84 (OID 20813)
--
-- Name: project_task_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_task_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 218 (OID 20832)
--
-- Name: project_task Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 86 (OID 20881)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 219 (OID 20900)
--
-- Name: project_weekly_metric Type: TABLE Owner: tperdue
--

CREATE TABLE "project_weekly_metric" (
	"ranking" integer DEFAULT nextval('project_weekly_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_weekly_metric_pkey" Primary Key ("ranking")
);

--
-- TOC Entry ID 220 (OID 20914)
--
-- Name: session Type: TABLE Owner: tperdue
--

CREATE TABLE "session" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"session_hash" character(32) DEFAULT '' NOT NULL,
	"ip_addr" character(15) DEFAULT '' NOT NULL,
	"time" integer DEFAULT '0' NOT NULL,
	Constraint "session_pkey" Primary Key ("session_hash")
);

--
-- TOC Entry ID 88 (OID 20934)
--
-- Name: snippet_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 221 (OID 20953)
--
-- Name: snippet Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 90 (OID 20994)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 222 (OID 21013)
--
-- Name: snippet_package Type: TABLE Owner: tperdue
--

CREATE TABLE "snippet_package" (
	"snippet_package_id" integer DEFAULT nextval('snippet_package_pk_seq'::text) NOT NULL,
	"created_by" integer DEFAULT '0' NOT NULL,
	"name" text,
	"description" text,
	"category" integer DEFAULT '0' NOT NULL,
	"language" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_pkey" Primary Key ("snippet_package_id")
);

--
-- TOC Entry ID 92 (OID 21050)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 223 (OID 21069)
--
-- Name: snippet_package_item Type: TABLE Owner: tperdue
--

CREATE TABLE "snippet_package_item" (
	"snippet_package_item_id" integer DEFAULT nextval('snippet_package_item_pk_seq'::text) NOT NULL,
	"snippet_package_version_id" integer DEFAULT '0' NOT NULL,
	"snippet_version_id" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_item_pkey" Primary Key ("snippet_package_item_id")
);

--
-- TOC Entry ID 94 (OID 21087)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 224 (OID 21106)
--
-- Name: snippet_package_version Type: TABLE Owner: tperdue
--

CREATE TABLE "snippet_package_version" (
	"snippet_package_version_id" integer DEFAULT nextval('snippet_package_version_pk_seq'::text) NOT NULL,
	"snippet_package_id" integer DEFAULT '0' NOT NULL,
	"changes" text,
	"version" text,
	"submitted_by" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL,
	Constraint "snippet_package_version_pkey" Primary Key ("snippet_package_version_id")
);

--
-- TOC Entry ID 96 (OID 21143)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 225 (OID 21162)
--
-- Name: snippet_version Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 226 (OID 21200)
--
-- Name: stats_agg_logo_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_logo_by_day" (
	"day" integer,
	"count" integer
);

--
-- TOC Entry ID 227 (OID 21211)
--
-- Name: stats_agg_pages_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_pages_by_day" (
	"day" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 228 (OID 21224)
--
-- Name: stats_ftp_downloads Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_ftp_downloads" (
	"day" integer DEFAULT '0' NOT NULL,
	"filerelease_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 229 (OID 21241)
--
-- Name: stats_http_downloads Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_http_downloads" (
	"day" integer DEFAULT '0' NOT NULL,
	"filerelease_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 98 (OID 21258)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "supported_languages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 230 (OID 21277)
--
-- Name: supported_languages Type: TABLE Owner: tperdue
--

CREATE TABLE "supported_languages" (
	"language_id" integer DEFAULT nextval('supported_languages_pk_seq'::text) NOT NULL,
	"name" text,
	"filename" text,
	"classname" text,
	"language_code" character(2),
	Constraint "supported_languages_pkey" Primary Key ("language_id")
);

--
-- TOC Entry ID 100 (OID 21310)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 231 (OID 21329)
--
-- Name: survey_question_types Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_question_types" (
	"id" integer DEFAULT nextval('survey_question_types_pk_seq'::text) NOT NULL,
	"type" text DEFAULT '' NOT NULL,
	Constraint "survey_question_types_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 102 (OID 21360)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 232 (OID 21379)
--
-- Name: survey_questions Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_questions" (
	"question_id" integer DEFAULT nextval('survey_questions_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"question" text DEFAULT '' NOT NULL,
	"question_type" integer DEFAULT '0' NOT NULL,
	Constraint "survey_questions_pkey" Primary Key ("question_id")
);

--
-- TOC Entry ID 233 (OID 21414)
--
-- Name: survey_rating_aggregate Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_rating_aggregate" (
	"type" integer DEFAULT '0' NOT NULL,
	"id" integer DEFAULT '0' NOT NULL,
	"response" double precision DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 234 (OID 21431)
--
-- Name: survey_rating_response Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_rating_response" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"type" integer DEFAULT '0' NOT NULL,
	"id" integer DEFAULT '0' NOT NULL,
	"response" integer DEFAULT '0' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 235 (OID 21450)
--
-- Name: survey_responses Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_responses" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"survey_id" integer DEFAULT '0' NOT NULL,
	"question_id" integer DEFAULT '0' NOT NULL,
	"response" text DEFAULT '' NOT NULL,
	"date" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 104 (OID 21486)
--
-- Name: surveys_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "surveys_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 236 (OID 21505)
--
-- Name: surveys Type: TABLE Owner: tperdue
--

CREATE TABLE "surveys" (
	"survey_id" integer DEFAULT nextval('surveys_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"survey_title" text DEFAULT '' NOT NULL,
	"survey_questions" text DEFAULT '' NOT NULL,
	"is_active" integer DEFAULT '1' NOT NULL,
	Constraint "surveys_pkey" Primary Key ("survey_id")
);

--
-- TOC Entry ID 106 (OID 21542)
--
-- Name: system_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 108 (OID 21561)
--
-- Name: system_machines_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_machines_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 110 (OID 21580)
--
-- Name: system_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 112 (OID 21599)
--
-- Name: system_services_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_services_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 114 (OID 21618)
--
-- Name: system_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 237 (OID 21637)
--
-- Name: theme_prefs Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 116 (OID 21665)
--
-- Name: themes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 238 (OID 21684)
--
-- Name: themes Type: TABLE Owner: tperdue
--

CREATE TABLE "themes" (
	"theme_id" integer DEFAULT nextval('themes_pk_seq'::text) NOT NULL,
	"dirname" character varying(80),
	"fullname" character varying(80),
	Constraint "themes_pkey" Primary Key ("theme_id")
);

--
-- TOC Entry ID 239 (OID 21700)
--
-- Name: top_group Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 118 (OID 21742)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_cat_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 240 (OID 21761)
--
-- Name: trove_cat Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 120 (OID 21806)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 241 (OID 21825)
--
-- Name: trove_group_link Type: TABLE Owner: tperdue
--

CREATE TABLE "trove_group_link" (
	"trove_group_id" integer DEFAULT nextval('trove_group_link_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_version" integer DEFAULT '0' NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"trove_cat_root" integer DEFAULT '0' NOT NULL,
	Constraint "trove_group_link_pkey" Primary Key ("trove_group_id")
);

--
-- TOC Entry ID 122 (OID 21847)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 124 (OID 21866)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 242 (OID 21885)
--
-- Name: user_bookmarks Type: TABLE Owner: tperdue
--

CREATE TABLE "user_bookmarks" (
	"bookmark_id" integer DEFAULT nextval('user_bookmarks_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"bookmark_url" text,
	"bookmark_title" text,
	Constraint "user_bookmarks_pkey" Primary Key ("bookmark_id")
);

--
-- TOC Entry ID 126 (OID 21918)
--
-- Name: user_diary_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 243 (OID 21937)
--
-- Name: user_diary Type: TABLE Owner: tperdue
--

CREATE TABLE "user_diary" (
	"id" integer DEFAULT nextval('user_diary_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"date_posted" integer DEFAULT '0' NOT NULL,
	"summary" text,
	"details" text,
	"is_public" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 128 (OID 21974)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 244 (OID 21993)
--
-- Name: user_diary_monitor Type: TABLE Owner: tperdue
--

CREATE TABLE "user_diary_monitor" (
	"monitor_id" integer DEFAULT nextval('user_diary_monitor_pk_seq'::text) NOT NULL,
	"monitored_user" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	Constraint "user_diary_monitor_pkey" Primary Key ("monitor_id")
);

--
-- TOC Entry ID 130 (OID 22011)
--
-- Name: user_group_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 245 (OID 22030)
--
-- Name: user_group Type: TABLE Owner: tperdue
--

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
	"artifact_flags" integer DEFAULT '0',
	Constraint "user_group_pkey" Primary Key ("user_group_id")
);

--
-- TOC Entry ID 132 (OID 22069)
--
-- Name: user_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 246 (OID 22088)
--
-- Name: user_metric Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 134 (OID 22116)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 247 (OID 22135)
--
-- Name: user_metric0 Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 248 (OID 22163)
--
-- Name: user_preferences Type: TABLE Owner: tperdue
--

CREATE TABLE "user_preferences" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"preference_name" character varying(20),
	"preference_value" text,
	"set_date" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 249 (OID 22194)
--
-- Name: user_ratings Type: TABLE Owner: tperdue
--

CREATE TABLE "user_ratings" (
	"rated_by" integer DEFAULT '0' NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL,
	"rate_field" integer DEFAULT '0' NOT NULL,
	"rating" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 136 (OID 22211)
--
-- Name: users_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "users_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 250 (OID 22230)
--
-- Name: users Type: TABLE Owner: tperdue
--

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
	"unix_box" character varying(10) DEFAULT 'shell' NOT NULL,
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

--
-- TOC Entry ID 138 (OID 22298)
--
-- Name: unix_uid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 140 (OID 22317)
--
-- Name: forum_thread_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 251 (OID 22336)
--
-- Name: project_sums_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "project_sums_agg" (
	"group_id" integer DEFAULT 0 NOT NULL,
	"type" character(4),
	"count" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 142 (OID 22350)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE Owner: tperdue
--

-- CREATE SEQUENCE "project_metric_wee_ranking1_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 144 (OID 22369)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 252 (OID 22388)
--
-- Name: prdb_dbs Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 253 (OID 22425)
--
-- Name: prdb_states Type: TABLE Owner: tperdue
--

CREATE TABLE "prdb_states" (
	"stateid" integer NOT NULL,
	"statename" text
);

--
-- TOC Entry ID 254 (OID 22451)
--
-- Name: prdb_types Type: TABLE Owner: tperdue
--

CREATE TABLE "prdb_types" (
	"dbtypeid" integer NOT NULL,
	"dbservername" text NOT NULL,
	"dbsoftware" text NOT NULL,
	Constraint "prdb_types_pkey" Primary Key ("dbtypeid")
);

--
-- TOC Entry ID 146 (OID 22481)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 255 (OID 22500)
--
-- Name: prweb_vhost Type: TABLE Owner: tperdue
--

CREATE TABLE "prweb_vhost" (
	"vhostid" integer DEFAULT nextval('"prweb_vhost_vhostid_seq"'::text) NOT NULL,
	"vhost_name" text,
	"docdir" text,
	"cgidir" text,
	"group_id" integer NOT NULL,
	Constraint "prweb_vhost_pkey" Primary Key ("vhostid")
);

--
-- TOC Entry ID 148 (OID 22533)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 256 (OID 22552)
--
-- Name: artifact_group_list Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 150 (OID 22600)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 257 (OID 22619)
--
-- Name: artifact_resolution Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_resolution" (
	"id" integer DEFAULT nextval('"artifact_resolution_id_seq"'::text) NOT NULL,
	"resolution_name" text,
	Constraint "artifact_resolution_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 152 (OID 22649)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 258 (OID 22668)
--
-- Name: artifact_perm Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_perm" (
	"id" integer DEFAULT nextval('"artifact_perm_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"perm_level" integer DEFAULT 0 NOT NULL,
	Constraint "artifact_perm_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 259 (OID 22701)
--
-- Name: artifactperm_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifactperm_user_vw" as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id);

--
-- TOC Entry ID 260 (OID 22717)
--
-- Name: artifactperm_artgrouplist_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifactperm_artgrouplist_vw" as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id);

--
-- TOC Entry ID 154 (OID 22718)
--
-- Name: artifact_category_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 261 (OID 22737)
--
-- Name: artifact_category Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_category" (
	"id" integer DEFAULT nextval('"artifact_category_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"category_name" text NOT NULL,
	"auto_assign_to" integer DEFAULT 100 NOT NULL,
	Constraint "artifact_category_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 156 (OID 22770)
--
-- Name: artifact_group_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 262 (OID 22789)
--
-- Name: artifact_group Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_group" (
	"id" integer DEFAULT nextval('"artifact_group_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"group_name" text NOT NULL,
	Constraint "artifact_group_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 158 (OID 22820)
--
-- Name: artifact_status_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 263 (OID 22839)
--
-- Name: artifact_status Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_status" (
	"id" integer DEFAULT nextval('"artifact_status_id_seq"'::text) NOT NULL,
	"status_name" text NOT NULL,
	Constraint "artifact_status_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 160 (OID 22869)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 264 (OID 22888)
--
-- Name: artifact Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 265 (OID 22970)
--
-- Name: artifact_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_vw" as SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.category_id, artifact.artifact_group_id, artifact.resolution_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact_category.category_name, artifact_group.group_name, artifact_resolution.resolution_name FROM users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution WHERE ((((((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id)) AND (artifact.category_id = artifact_category.id)) AND (artifact.artifact_group_id = artifact_group.id)) AND (artifact.resolution_id = artifact_resolution.id));

--
-- TOC Entry ID 162 (OID 22974)
--
-- Name: artifact_history_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 266 (OID 22993)
--
-- Name: artifact_history Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_history" (
	"id" integer DEFAULT nextval('"artifact_history_id_seq"'::text) NOT NULL,
	"artifact_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"entrydate" integer DEFAULT '0' NOT NULL,
	Constraint "artifact_history_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 267 (OID 23047)
--
-- Name: artifact_history_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_history_user_vw" as SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name FROM artifact_history ah, users WHERE (ah.mod_by = users.user_id);

--
-- TOC Entry ID 164 (OID 23048)
--
-- Name: artifact_file_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 268 (OID 23067)
--
-- Name: artifact_file Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 269 (OID 23125)
--
-- Name: artifact_file_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_file_user_vw" as SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname FROM artifact_file af, users WHERE (af.submitted_by = users.user_id);

--
-- TOC Entry ID 166 (OID 23126)
--
-- Name: artifact_message_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 270 (OID 23145)
--
-- Name: artifact_message Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_message" (
	"id" integer DEFAULT nextval('"artifact_message_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"submitted_by" integer NOT NULL,
	"from_email" text NOT NULL,
	"adddate" integer DEFAULT '0' NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_message_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 271 (OID 23198)
--
-- Name: artifact_message_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_message_user_vw" as SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname FROM artifact_message am, users WHERE (am.submitted_by = users.user_id);

--
-- TOC Entry ID 168 (OID 23199)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 272 (OID 23218)
--
-- Name: artifact_monitor Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_monitor" (
	"id" integer DEFAULT nextval('"artifact_monitor_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"email" text,
	Constraint "artifact_monitor_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 170 (OID 23250)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 273 (OID 23269)
--
-- Name: artifact_canned_responses Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_canned_responses" (
	"id" integer DEFAULT nextval('"artifact_canned_response_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"title" text NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_canned_responses_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 274 (OID 23301)
--
-- Name: artifact_counts_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_counts_agg" (
	"group_artifact_id" integer NOT NULL,
	"count" integer NOT NULL,
	"open_count" integer
);

--
-- TOC Entry ID 275 (OID 23313)
--
-- Name: stats_site_pages_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);

--
-- TOC Entry ID 469 (OID 23325)
--
-- Name: "plpgsql_call_handler" () Type: FUNCTION Owner: tperdue
--

-- CREATE FUNCTION "plpgsql_call_handler" () RETURNS opaque AS '/usr/lib/postgresql/lib/plpgsql.so', 'plpgsql_call_handler' LANGUAGE 'C';

--
-- TOC Entry ID 470 (OID 23326)
--
-- Name: plpgsql Type: PROCEDURAL LANGUAGE Owner:
--

-- CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER "plpgsql_call_handler" LANCOMPILER 'PL/pgSQL';

--
-- TOC Entry ID 471 (OID 23327)
--
-- Name: "forumgrouplist_insert_agg" () Type: FUNCTION Owner: tperdue
--

CREATE FUNCTION "forumgrouplist_insert_agg" () RETURNS opaque AS '
BEGIN
        INSERT INTO forum_agg_msg_count (group_forum_id,count)
                VALUES (NEW.group_forum_id,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';

--
-- TOC Entry ID 472 (OID 23328)
--
-- Name: "artifactgrouplist_insert_agg" () Type: FUNCTION Owner: tperdue
--

CREATE FUNCTION "artifactgrouplist_insert_agg" () RETURNS opaque AS '
BEGIN
    INSERT INTO artifact_counts_agg (group_artifact_id,count,open_count)
        VALUES (NEW.group_artifact_id,0,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';

--
-- TOC Entry ID 473 (OID 23329)
--
-- Name: "artifactgroup_update_agg" () Type: FUNCTION Owner: tperdue
--

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

--
-- TOC Entry ID 172 (OID 23330)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 276 (OID 23349)
--
-- Name: massmail_queue Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 277 (OID 23388)
--
-- Name: frs_dlstats_file_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_file_agg" (
	"month" integer,
	"day" integer,
	"file_id" integer,
	"downloads" integer
);

--
-- TOC Entry ID 278 (OID 23401)
--
-- Name: stats_agg_site_by_group Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_site_by_group" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"count" integer
);

--
-- TOC Entry ID 279 (OID 23414)
--
-- Name: stats_project_metric Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_project_metric" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"ranking" integer DEFAULT 0 NOT NULL,
	"percentile" double precision DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 280 (OID 23433)
--
-- Name: stats_agg_logo_by_group Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_logo_by_group" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"count" integer
);

--
-- TOC Entry ID 281 (OID 23446)
--
-- Name: stats_subd_pages Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_subd_pages" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"pages" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 282 (OID 23463)
--
-- Name: stats_cvs_user Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_cvs_user" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"user_id" integer DEFAULT 0 NOT NULL,
	"checkouts" integer DEFAULT 0 NOT NULL,
	"commits" integer DEFAULT 0 NOT NULL,
	"adds" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 283 (OID 23486)
--
-- Name: stats_cvs_group Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_cvs_group" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"checkouts" integer DEFAULT 0 NOT NULL,
	"commits" integer DEFAULT 0 NOT NULL,
	"adds" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 284 (OID 23507)
--
-- Name: stats_project_developers Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_project_developers" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"developers" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 285 (OID 23524)
--
-- Name: stats_project Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 286 (OID 23567)
--
-- Name: stats_site Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site" (
	"month" integer,
	"day" integer,
	"uniq_users" integer,
	"sessions" integer,
	"total_users" integer,
	"new_users" integer,
	"new_projects" integer
);

--
-- TOC Entry ID 287 (OID 23583)
--
-- Name: activity_log_old_old Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 288 (OID 23624)
--
-- Name: activity_log_old Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 289 (OID 23665)
--
-- Name: activity_log Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 290 (OID 23706)
--
-- Name: cache_store Type: TABLE Owner: tperdue
--

CREATE TABLE "cache_store" (
	"name" character varying(255) NOT NULL,
	"data" text,
	"indate" integer DEFAULT 0 NOT NULL,
	Constraint "cache_store_pkey" Primary Key ("name")
);

--
-- TOC Entry ID 291 (OID 25693)
--
-- Name: user_metric_history Type: TABLE Owner: tperdue
--

CREATE TABLE "user_metric_history" (
	"month" integer NOT NULL,
	"day" integer NOT NULL,
	"user_id" integer NOT NULL,
	"ranking" integer NOT NULL,
	"metric" double precision NOT NULL
);

--
-- TOC Entry ID 292 (OID 25710)
--
-- Name: foundry_project_rankings_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "foundry_project_rankings_agg" (
	"foundry_id" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30),
	"ranking" integer,
	"percentile" double precision
);

--
-- TOC Entry ID 293 (OID 25728)
--
-- Name: foundry_project_downloads_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "foundry_project_downloads_agg" (
	"foundry_id" integer,
	"downloads" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30)
);

--
-- TOC Entry ID 294 (OID 25745)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_filetotal_agg" (
	"file_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	Constraint "frs_dlstats_filetotal_agg_pkey" Primary Key ("file_id")
);

--
-- TOC Entry ID 295 (OID 25759)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_grouptotal_agg" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 296 (OID 25773)
--
-- Name: frs_dlstats_group_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_group_agg" (
	"group_id" integer DEFAULT '0' NOT NULL,
	"day" integer DEFAULT '0' NOT NULL,
	"downloads" integer DEFAULT '0' NOT NULL,
	"month" integer DEFAULT '0'
);

--
-- TOC Entry ID 297 (OID 25792)
--
-- Name: stats_project_months Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 298 (OID 25834)
--
-- Name: stats_project_all Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_project_all" (
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
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

--
-- TOC Entry ID 299 (OID 25871)
--
-- Name: stats_project_developers_last30 Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_project_developers_last30" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"developers" integer
);

--
-- TOC Entry ID 300 (OID 25884)
--
-- Name: stats_project_last_30 Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_project_last_30" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
	"filereleases" integer,
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

--
-- TOC Entry ID 301 (OID 25924)
--
-- Name: stats_site_pages_by_month Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_pages_by_month" (
	"month" integer,
	"site_page_views" integer
);

--
-- TOC Entry ID 302 (OID 25935)
--
-- Name: stats_site_last_30 Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_last_30" (
	"month" integer,
	"day" integer,
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

--
-- TOC Entry ID 303 (OID 25967)
--
-- Name: stats_site_months Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 304 (OID 25998)
--
-- Name: stats_site_all Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_all" (
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

--
-- TOC Entry ID 305 (OID 26026)
--
-- Name: trove_agg Type: TABLE Owner: tperdue
--

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

--
-- TOC Entry ID 174 (OID 26050)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE Owner: tperdue
--

-- CREATE SEQUENCE "trove_treesum_trove_treesum_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 306 (OID 26069)
--
-- Name: trove_treesums Type: TABLE Owner: tperdue
--

CREATE TABLE "trove_treesums" (
	"trove_treesums_id" integer DEFAULT nextval('trove_treesums_pk_seq'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"limit_1" integer DEFAULT '0' NOT NULL,
	"subprojects" integer DEFAULT '0' NOT NULL,
	Constraint "trove_treesums_pkey" Primary Key ("trove_treesums_id")
);

--
-- Data for TOC Entry ID 474 (OID 18743)
--
-- Name: canned_responses Type: TABLE DATA Owner: tperdue
--


COPY "canned_responses"  FROM stdin;
\.
--
-- Data for TOC Entry ID 475 (OID 18793)
--
-- Name: db_images Type: TABLE DATA Owner: tperdue
--


COPY "db_images"  FROM stdin;
\.
--
-- Data for TOC Entry ID 476 (OID 18859)
--
-- Name: doc_data Type: TABLE DATA Owner: tperdue
--


COPY "doc_data"  FROM stdin;
\.
--
-- Data for TOC Entry ID 477 (OID 18924)
--
-- Name: doc_groups Type: TABLE DATA Owner: tperdue
--


COPY "doc_groups"  FROM stdin;
\.
--
-- Data for TOC Entry ID 478 (OID 18961)
--
-- Name: doc_states Type: TABLE DATA Owner: tperdue
--


COPY "doc_states"  FROM stdin;
1	active
2	deleted
3	pending
4	hidden
5	private
\.
--
-- Data for TOC Entry ID 479 (OID 18996)
--
-- Name: filemodule_monitor Type: TABLE DATA Owner: tperdue
--


COPY "filemodule_monitor"  FROM stdin;
\.
--
-- Data for TOC Entry ID 480 (OID 19033)
--
-- Name: forum Type: TABLE DATA Owner: tperdue
--


COPY "forum"  FROM stdin;
\.
--
-- Data for TOC Entry ID 481 (OID 19080)
--
-- Name: forum_agg_msg_count Type: TABLE DATA Owner: tperdue
--


COPY "forum_agg_msg_count"  FROM stdin;
\.
--
-- Data for TOC Entry ID 482 (OID 19115)
--
-- Name: forum_group_list Type: TABLE DATA Owner: tperdue
--


COPY "forum_group_list"  FROM stdin;
\.
--
-- Data for TOC Entry ID 483 (OID 19173)
--
-- Name: forum_monitored_forums Type: TABLE DATA Owner: tperdue
--


COPY "forum_monitored_forums"  FROM stdin;
\.
--
-- Data for TOC Entry ID 484 (OID 19210)
--
-- Name: forum_saved_place Type: TABLE DATA Owner: tperdue
--


COPY "forum_saved_place"  FROM stdin;
\.
--
-- Data for TOC Entry ID 485 (OID 19230)
--
-- Name: foundry_data Type: TABLE DATA Owner: tperdue
--


COPY "foundry_data"  FROM stdin;
\.
--
-- Data for TOC Entry ID 486 (OID 19287)
--
-- Name: foundry_news Type: TABLE DATA Owner: tperdue
--


COPY "foundry_news"  FROM stdin;
\.
--
-- Data for TOC Entry ID 487 (OID 19328)
--
-- Name: foundry_preferred_projects Type: TABLE DATA Owner: tperdue
--


COPY "foundry_preferred_projects"  FROM stdin;
\.
--
-- Data for TOC Entry ID 488 (OID 19367)
--
-- Name: foundry_projects Type: TABLE DATA Owner: tperdue
--


COPY "foundry_projects"  FROM stdin;
\.
--
-- Data for TOC Entry ID 489 (OID 19404)
--
-- Name: frs_file Type: TABLE DATA Owner: tperdue
--


COPY "frs_file"  FROM stdin;
\.
--
-- Data for TOC Entry ID 490 (OID 19465)
--
-- Name: frs_filetype Type: TABLE DATA Owner: tperdue
--


COPY "frs_filetype"  FROM stdin;
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
--
-- Data for TOC Entry ID 491 (OID 19514)
--
-- Name: frs_package Type: TABLE DATA Owner: tperdue
--


COPY "frs_package"  FROM stdin;
\.
--
-- Data for TOC Entry ID 492 (OID 19567)
--
-- Name: frs_processor Type: TABLE DATA Owner: tperdue
--


COPY "frs_processor"  FROM stdin;
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
--
-- Data for TOC Entry ID 493 (OID 19616)
--
-- Name: frs_release Type: TABLE DATA Owner: tperdue
--


COPY "frs_release"  FROM stdin;
\.
--
-- Data for TOC Entry ID 494 (OID 19677)
--
-- Name: frs_status Type: TABLE DATA Owner: tperdue
--


COPY "frs_status"  FROM stdin;
1	Active
3	Hidden
\.
--
-- Data for TOC Entry ID 495 (OID 19745)
--
-- Name: group_history Type: TABLE DATA Owner: tperdue
--


COPY "group_history"  FROM stdin;
\.
--
-- Data for TOC Entry ID 496 (OID 19802)
--
-- Name: group_type Type: TABLE DATA Owner: tperdue
--


COPY "group_type"  FROM stdin;
1	Project
2	Foundry
\.
--
-- Data for TOC Entry ID 497 (OID 19851)
--
-- Name: groups Type: TABLE DATA Owner: tperdue
--


COPY "groups"  FROM stdin;
1	Site Admin	\N	1	A	siteadmin	shell	\N	\N	cvs	\N	\N	\N	0	\N	1	1	1	1	1	1	1	1	0	1	1
2	Site News Admin	\N	1	A	newsadmin	shell	\N	\N	cvs	\N	\N	\N	0	\N	1	1	1	1	1	1	1	1	0	1	1
3	Stats Group	\N	0	A	stats	shell	\N	\N	cvs	\N	\N	\N	0	\N	1	1	1	1	1	1	1	1	0	1	1
4	Peer Ratings Group	\N	0	A	peerrating	shell	\N	\N	cvs	\N	\N	\N	0	\N	1	1	1	1	1	1	1	1	0	1	1
\.
--
-- Data for TOC Entry ID 499 (OID 19996)
--
-- Name: mail_group_list Type: TABLE DATA Owner: tperdue
--


COPY "mail_group_list"  FROM stdin;
\.
--
-- Data for TOC Entry ID 500 (OID 20055)
--
-- Name: news_bytes Type: TABLE DATA Owner: tperdue
--


COPY "news_bytes"  FROM stdin;
\.
--
-- Data for TOC Entry ID 501 (OID 20115)
--
-- Name: people_job Type: TABLE DATA Owner: tperdue
--


COPY "people_job"  FROM stdin;
\.
--
-- Data for TOC Entry ID 502 (OID 20175)
--
-- Name: people_job_category Type: TABLE DATA Owner: tperdue
--


COPY "people_job_category"  FROM stdin;
1	Developer	0
2	Project Manager	0
3	Unix Admin	0
4	Doc Writer	0
5	Tester	0
6	Support Manager	0
7	Graphic/Other Designer	0
\.
--
-- Data for TOC Entry ID 503 (OID 20226)
--
-- Name: people_job_inventory Type: TABLE DATA Owner: tperdue
--


COPY "people_job_inventory"  FROM stdin;
\.
--
-- Data for TOC Entry ID 504 (OID 20267)
--
-- Name: people_job_status Type: TABLE DATA Owner: tperdue
--


COPY "people_job_status"  FROM stdin;
1	Open
2	Filled
3	Deleted
\.
--
-- Data for TOC Entry ID 505 (OID 20316)
--
-- Name: people_skill Type: TABLE DATA Owner: tperdue
--


COPY "people_skill"  FROM stdin;
\.
--
-- Data for TOC Entry ID 506 (OID 20365)
--
-- Name: people_skill_inventory Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_inventory"  FROM stdin;
\.
--
-- Data for TOC Entry ID 507 (OID 20406)
--
-- Name: people_skill_level Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_level"  FROM stdin;
1	Want to Learn
2	Competent
3	Wizard
4	Wrote The Book
5	Wrote It
\.
--
-- Data for TOC Entry ID 508 (OID 20455)
--
-- Name: people_skill_year Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_year"  FROM stdin;
1	< 6 Months
2	6 Mo - 2 yr
3	2 yr - 5 yr
4	5 yr - 10 yr
5	> 10 years
\.
--
-- Data for TOC Entry ID 509 (OID 20504)
--
-- Name: project_assigned_to Type: TABLE DATA Owner: tperdue
--


COPY "project_assigned_to"  FROM stdin;
\.
--
-- Data for TOC Entry ID 510 (OID 20541)
--
-- Name: project_dependencies Type: TABLE DATA Owner: tperdue
--


COPY "project_dependencies"  FROM stdin;
\.
--
-- Data for TOC Entry ID 511 (OID 20578)
--
-- Name: project_group_list Type: TABLE DATA Owner: tperdue
--


COPY "project_group_list"  FROM stdin;
1	1		0	\N
\.
--
-- Data for TOC Entry ID 512 (OID 20633)
--
-- Name: project_history Type: TABLE DATA Owner: tperdue
--


COPY "project_history"  FROM stdin;
\.
--
-- Data for TOC Entry ID 513 (OID 20691)
--
-- Name: project_metric Type: TABLE DATA Owner: tperdue
--


COPY "project_metric"  FROM stdin;
\.
--
-- Data for TOC Entry ID 514 (OID 20727)
--
-- Name: project_metric_tmp1 Type: TABLE DATA Owner: tperdue
--


-- COPY "project_metric_tmp1"  FROM stdin;
-- \.
--
-- Data for TOC Entry ID 515 (OID 20782)
--
-- Name: project_status Type: TABLE DATA Owner: tperdue
--


COPY "project_status"  FROM stdin;
1	Open
2	Closed
100	None
3	Deleted
\.
--
-- Data for TOC Entry ID 516 (OID 20832)
--
-- Name: project_task Type: TABLE DATA Owner: tperdue
--


COPY "project_task"  FROM stdin;
1	1			0	0	0	0	0	100	100
\.
--
-- Data for TOC Entry ID 517 (OID 20900)
--
-- Name: project_weekly_metric Type: TABLE DATA Owner: tperdue
--


COPY "project_weekly_metric"  FROM stdin;
\.
--
-- Data for TOC Entry ID 518 (OID 20914)
--
-- Name: session Type: TABLE DATA Owner: tperdue
--


COPY "session"  FROM stdin;
\.
--
-- Data for TOC Entry ID 519 (OID 20953)
--
-- Name: snippet Type: TABLE DATA Owner: tperdue
--


COPY "snippet"  FROM stdin;
\.
--
-- Data for TOC Entry ID 520 (OID 21013)
--
-- Name: snippet_package Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package"  FROM stdin;
\.
--
-- Data for TOC Entry ID 521 (OID 21069)
--
-- Name: snippet_package_item Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package_item"  FROM stdin;
\.
--
-- Data for TOC Entry ID 522 (OID 21106)
--
-- Name: snippet_package_version Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package_version"  FROM stdin;
\.
--
-- Data for TOC Entry ID 523 (OID 21162)
--
-- Name: snippet_version Type: TABLE DATA Owner: tperdue
--


COPY "snippet_version"  FROM stdin;
\.
--
-- Data for TOC Entry ID 524 (OID 21200)
--
-- Name: stats_agg_logo_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_logo_by_day"  FROM stdin;
\.
--
-- Data for TOC Entry ID 525 (OID 21211)
--
-- Name: stats_agg_pages_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_pages_by_day"  FROM stdin;
\.
--
-- Data for TOC Entry ID 526 (OID 21224)
--
-- Name: stats_ftp_downloads Type: TABLE DATA Owner: tperdue
--


COPY "stats_ftp_downloads"  FROM stdin;
\.
--
-- Data for TOC Entry ID 527 (OID 21241)
--
-- Name: stats_http_downloads Type: TABLE DATA Owner: tperdue
--


COPY "stats_http_downloads"  FROM stdin;
\.
--
-- Data for TOC Entry ID 528 (OID 21277)
--
-- Name: supported_languages Type: TABLE DATA Owner: tperdue
--


COPY "supported_languages"  FROM stdin;
1	English	English.class	English	en
2	Japanese	Japanese.class	Japanese	ja
3	Hebrew	Hebrew.class	Hebrew	iw
4	Spanish	Spanish.class	Spanish	es
5	Thai	Thai.class	Thai	th
6	German	German.class	German	de
7	French	French.class	French	fr
8	Italian	Italian.class	Italian	it
9	Norwegian	Norwegian.class	Norwegian	no
10	Swedish	Swedish.class	Swedish	sv
11	Trad.Chinese	Chinese.class	Chinese	zh
12	Dutch	Dutch.class	Dutch	nl
13	Esperanto	Esperanto.class	Esperanto	eo
14	Catalan	Catalan.class	Catalan	ca
15	Polish	Polish.class	Polish	pl
16	Pt. Brazillian	PortugueseBrazillian.class	PortugueseBrazillian	pt
17	Russian	Russian.class	Russian	ru
18	Portuguese	Portuguese.class	Portuguese	pt
19	Greek	Greek.class	Greek	el
20	Bulgarian	Bulgarian.class	Bulgarian	bg
21	Indonesian	Indonesian.class	Indonesian	id
22	Korean	Korean.class	Korean	ko
23	Smpl.Chinese	SimplifiedChinese.class	SimplifiedChinese	zn
\.
--
-- Data for TOC Entry ID 529 (OID 21329)
--
-- Name: survey_question_types Type: TABLE DATA Owner: tperdue
--


COPY "survey_question_types"  FROM stdin;
1	Radio Buttons 1-5
2	Text Area
3	Radio Buttons Yes/No
4	Comment Only
5	Text Field
100	None
\.
--
-- Data for TOC Entry ID 530 (OID 21379)
--
-- Name: survey_questions Type: TABLE DATA Owner: tperdue
--


COPY "survey_questions"  FROM stdin;
\.
--
-- Data for TOC Entry ID 531 (OID 21414)
--
-- Name: survey_rating_aggregate Type: TABLE DATA Owner: tperdue
--


COPY "survey_rating_aggregate"  FROM stdin;
\.
--
-- Data for TOC Entry ID 532 (OID 21431)
--
-- Name: survey_rating_response Type: TABLE DATA Owner: tperdue
--


COPY "survey_rating_response"  FROM stdin;
\.
--
-- Data for TOC Entry ID 533 (OID 21450)
--
-- Name: survey_responses Type: TABLE DATA Owner: tperdue
--


COPY "survey_responses"  FROM stdin;
\.
--
-- Data for TOC Entry ID 534 (OID 21505)
--
-- Name: surveys Type: TABLE DATA Owner: tperdue
--


COPY "surveys"  FROM stdin;
\.
--
-- Data for TOC Entry ID 535 (OID 21637)
--
-- Name: theme_prefs Type: TABLE DATA Owner: tperdue
--


COPY "theme_prefs"  FROM stdin;
\.
--
-- Data for TOC Entry ID 536 (OID 21684)
--
-- Name: themes Type: TABLE DATA Owner: tperdue
--


COPY "themes"  FROM stdin;
1	forged	Forged Metal
2	classic	Classic Sourceforge
3	ultralite	Ultra Lite
4	debian	Debian
5	savannah	Savannah
\.
--
-- Data for TOC Entry ID 537 (OID 21700)
--
-- Name: top_group Type: TABLE DATA Owner: tperdue
--


COPY "top_group"  FROM stdin;
\.
--
-- Data for TOC Entry ID 538 (OID 21761)
--
-- Name: trove_cat Type: TABLE DATA Owner: tperdue
--


COPY "trove_cat"  FROM stdin;
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

-- ' <-- This quote to help Emacs not get lost for its syntax-highlighting


--
-- Data for TOC Entry ID 539 (OID 21825)
--
-- Name: trove_group_link Type: TABLE DATA Owner: tperdue
--


COPY "trove_group_link"  FROM stdin;
\.
--
-- Data for TOC Entry ID 540 (OID 21885)
--
-- Name: user_bookmarks Type: TABLE DATA Owner: tperdue
--


COPY "user_bookmarks"  FROM stdin;
\.
--
-- Data for TOC Entry ID 541 (OID 21937)
--
-- Name: user_diary Type: TABLE DATA Owner: tperdue
--


COPY "user_diary"  FROM stdin;
\.
--
-- Data for TOC Entry ID 542 (OID 21993)
--
-- Name: user_diary_monitor Type: TABLE DATA Owner: tperdue
--


COPY "user_diary_monitor"  FROM stdin;
\.
--
-- Data for TOC Entry ID 543 (OID 22030)
--
-- Name: user_group Type: TABLE DATA Owner: tperdue
--


COPY "user_group"  FROM stdin;
\.
--
-- Data for TOC Entry ID 544 (OID 22088)
--
-- Name: user_metric Type: TABLE DATA Owner: tperdue
--


COPY "user_metric"  FROM stdin;
\.
--
-- Data for TOC Entry ID 545 (OID 22135)
--
-- Name: user_metric0 Type: TABLE DATA Owner: tperdue
--


COPY "user_metric0"  FROM stdin;
\.
--
-- Data for TOC Entry ID 546 (OID 22163)
--
-- Name: user_preferences Type: TABLE DATA Owner: tperdue
--


COPY "user_preferences"  FROM stdin;
\.
--
-- Data for TOC Entry ID 547 (OID 22194)
--
-- Name: user_ratings Type: TABLE DATA Owner: tperdue
--


COPY "user_ratings"  FROM stdin;
\.
--
-- Data for TOC Entry ID 548 (OID 22230)
--
-- Name: users Type: TABLE DATA Owner: tperdue
--


COPY "users"  FROM stdin;
100	None	noreply@gforge.net	*********34343		A	/bin/bash		N	0	shell	0	\N	0	0	\N	\N	0		GMT	1	0
\.
--
-- Data for TOC Entry ID 549 (OID 22336)
--
-- Name: project_sums_agg Type: TABLE DATA Owner: tperdue
--


COPY "project_sums_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 550 (OID 22388)
--
-- Name: prdb_dbs Type: TABLE DATA Owner: tperdue
--


COPY "prdb_dbs"  FROM stdin;
\.
--
-- Data for TOC Entry ID 551 (OID 22425)
--
-- Name: prdb_states Type: TABLE DATA Owner: tperdue
--


COPY "prdb_states"  FROM stdin;
\.
--
-- Data for TOC Entry ID 552 (OID 22451)
--
-- Name: prdb_types Type: TABLE DATA Owner: tperdue
--


COPY "prdb_types"  FROM stdin;
\.
--
-- Data for TOC Entry ID 553 (OID 22500)
--
-- Name: prweb_vhost Type: TABLE DATA Owner: tperdue
--


COPY "prweb_vhost"  FROM stdin;
\.
--
-- Data for TOC Entry ID 554 (OID 22552)
--
-- Name: artifact_group_list Type: TABLE DATA Owner: tperdue
--


COPY "artifact_group_list"  FROM stdin;
100	1	Default	Default Data - Dont Edit	3	0	0		2592000	0	\N	\N	0	\N
\.
--
-- Data for TOC Entry ID 555 (OID 22619)
--
-- Name: artifact_resolution Type: TABLE DATA Owner: tperdue
--


COPY "artifact_resolution"  FROM stdin;
100	None
1	Accepted
2	Duplicate
3	Fixed
4	Invalid
5	Later
6	Out of Date
7	Postponed
8	Rejected
9	Remind
10	Wont Fix
11	Works For Me
\.
--
-- Data for TOC Entry ID 556 (OID 22668)
--
-- Name: artifact_perm Type: TABLE DATA Owner: tperdue
--


COPY "artifact_perm"  FROM stdin;
\.
--
-- Data for TOC Entry ID 557 (OID 22737)
--
-- Name: artifact_category Type: TABLE DATA Owner: tperdue
--


COPY "artifact_category"  FROM stdin;
100	100	None	100
\.
--
-- Data for TOC Entry ID 558 (OID 22789)
--
-- Name: artifact_group Type: TABLE DATA Owner: tperdue
--


COPY "artifact_group"  FROM stdin;
100	100	None
\.
--
-- Data for TOC Entry ID 559 (OID 22839)
--
-- Name: artifact_status Type: TABLE DATA Owner: tperdue
--


COPY "artifact_status"  FROM stdin;
1	Open
2	Closed
3	Deleted
\.
--
-- Data for TOC Entry ID 560 (OID 22888)
--
-- Name: artifact Type: TABLE DATA Owner: tperdue
--


COPY "artifact"  FROM stdin;
\.
--
-- Data for TOC Entry ID 561 (OID 22993)
--
-- Name: artifact_history Type: TABLE DATA Owner: tperdue
--


COPY "artifact_history"  FROM stdin;
\.
--
-- Data for TOC Entry ID 562 (OID 23067)
--
-- Name: artifact_file Type: TABLE DATA Owner: tperdue
--


COPY "artifact_file"  FROM stdin;
\.
--
-- Data for TOC Entry ID 563 (OID 23145)
--
-- Name: artifact_message Type: TABLE DATA Owner: tperdue
--


COPY "artifact_message"  FROM stdin;
\.
--
-- Data for TOC Entry ID 564 (OID 23218)
--
-- Name: artifact_monitor Type: TABLE DATA Owner: tperdue
--


COPY "artifact_monitor"  FROM stdin;
\.
--
-- Data for TOC Entry ID 565 (OID 23269)
--
-- Name: artifact_canned_responses Type: TABLE DATA Owner: tperdue
--


COPY "artifact_canned_responses"  FROM stdin;
\.
--
-- Data for TOC Entry ID 566 (OID 23301)
--
-- Name: artifact_counts_agg Type: TABLE DATA Owner: tperdue
--


COPY "artifact_counts_agg"  FROM stdin;
100	0	0
\.
--
-- Data for TOC Entry ID 567 (OID 23313)
--
-- Name: stats_site_pages_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_pages_by_day"  FROM stdin;
\.
--
-- Data for TOC Entry ID 568 (OID 23349)
--
-- Name: massmail_queue Type: TABLE DATA Owner: tperdue
--


COPY "massmail_queue"  FROM stdin;
\.
--
-- Data for TOC Entry ID 569 (OID 23388)
--
-- Name: frs_dlstats_file_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_file_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 570 (OID 23401)
--
-- Name: stats_agg_site_by_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_site_by_group"  FROM stdin;
\.
--
-- Data for TOC Entry ID 571 (OID 23414)
--
-- Name: stats_project_metric Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_metric"  FROM stdin;
\.
--
-- Data for TOC Entry ID 572 (OID 23433)
--
-- Name: stats_agg_logo_by_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_logo_by_group"  FROM stdin;
\.
--
-- Data for TOC Entry ID 573 (OID 23446)
--
-- Name: stats_subd_pages Type: TABLE DATA Owner: tperdue
--


COPY "stats_subd_pages"  FROM stdin;
\.
--
-- Data for TOC Entry ID 574 (OID 23463)
--
-- Name: stats_cvs_user Type: TABLE DATA Owner: tperdue
--


COPY "stats_cvs_user"  FROM stdin;
\.
--
-- Data for TOC Entry ID 575 (OID 23486)
--
-- Name: stats_cvs_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_cvs_group"  FROM stdin;
\.
--
-- Data for TOC Entry ID 576 (OID 23507)
--
-- Name: stats_project_developers Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_developers"  FROM stdin;
\.
--
-- Data for TOC Entry ID 577 (OID 23524)
--
-- Name: stats_project Type: TABLE DATA Owner: tperdue
--


COPY "stats_project"  FROM stdin;
\.
--
-- Data for TOC Entry ID 578 (OID 23567)
--
-- Name: stats_site Type: TABLE DATA Owner: tperdue
--


COPY "stats_site"  FROM stdin;
\.
--
-- Data for TOC Entry ID 579 (OID 23583)
--
-- Name: activity_log_old_old Type: TABLE DATA Owner: tperdue
--


COPY "activity_log_old_old"  FROM stdin;
\.
--
-- Data for TOC Entry ID 580 (OID 23624)
--
-- Name: activity_log_old Type: TABLE DATA Owner: tperdue
--


COPY "activity_log_old"  FROM stdin;
\.
--
-- Data for TOC Entry ID 581 (OID 23665)
--
-- Name: activity_log Type: TABLE DATA Owner: tperdue
--


COPY "activity_log"  FROM stdin;
\.
--
-- Data for TOC Entry ID 582 (OID 23706)
--
-- Name: cache_store Type: TABLE DATA Owner: tperdue
--


--
-- Data for TOC Entry ID 583 (OID 25693)
--
-- Name: user_metric_history Type: TABLE DATA Owner: tperdue
--


COPY "user_metric_history"  FROM stdin;
\.
--
-- Data for TOC Entry ID 584 (OID 25710)
--
-- Name: foundry_project_rankings_agg Type: TABLE DATA Owner: tperdue
--


COPY "foundry_project_rankings_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 585 (OID 25728)
--
-- Name: foundry_project_downloads_agg Type: TABLE DATA Owner: tperdue
--


COPY "foundry_project_downloads_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 586 (OID 25745)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_filetotal_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 587 (OID 25759)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_grouptotal_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 588 (OID 25773)
--
-- Name: frs_dlstats_group_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_group_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 589 (OID 25792)
--
-- Name: stats_project_months Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_months"  FROM stdin;
\.
--
-- Data for TOC Entry ID 590 (OID 25834)
--
-- Name: stats_project_all Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_all"  FROM stdin;
\.
--
-- Data for TOC Entry ID 591 (OID 25871)
--
-- Name: stats_project_developers_last30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_developers_last30"  FROM stdin;
\.
--
-- Data for TOC Entry ID 592 (OID 25884)
--
-- Name: stats_project_last_30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_last_30"  FROM stdin;
\.
--
-- Data for TOC Entry ID 593 (OID 25924)
--
-- Name: stats_site_pages_by_month Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_pages_by_month"  FROM stdin;
\.
--
-- Data for TOC Entry ID 594 (OID 25935)
--
-- Name: stats_site_last_30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_last_30"  FROM stdin;
\.
--
-- Data for TOC Entry ID 595 (OID 25967)
--
-- Name: stats_site_months Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_months"  FROM stdin;
\.
--
-- Data for TOC Entry ID 596 (OID 25998)
--
-- Name: stats_site_all Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_all"  FROM stdin;
\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.
--
-- Data for TOC Entry ID 597 (OID 26026)
--
-- Name: trove_agg Type: TABLE DATA Owner: tperdue
--


COPY "trove_agg"  FROM stdin;
\.
--
-- Data for TOC Entry ID 598 (OID 26069)
--
-- Name: trove_treesums Type: TABLE DATA Owner: tperdue
--


COPY "trove_treesums"  FROM stdin;
\.
--
-- TOC Entry ID 307 (OID 18793)
--
-- Name: "db_images_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "db_images_group" on "db_images" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 308 (OID 18859)
--
-- Name: "doc_group_doc_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "doc_group_doc_group" on "doc_data" using btree ( "doc_group" "int4_ops" );

--
-- TOC Entry ID 309 (OID 18924)
--
-- Name: "doc_groups_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "doc_groups_group" on "doc_groups" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 310 (OID 18996)
--
-- Name: "filemodule_monitor_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "filemodule_monitor_id" on "filemodule_monitor" using btree ( "filemodule_id" "int4_ops" );

--
-- TOC Entry ID 311 (OID 18996)
--
-- Name: "filemodulemonitor_userid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "filemodulemonitor_userid" on "filemodule_monitor" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 312 (OID 19033)
--
-- Name: "forum_forumid_msgid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_forumid_msgid" on "forum" using btree ( "group_forum_id" "int4_ops", "msg_id" "int4_ops" );

--
-- TOC Entry ID 313 (OID 19033)
--
-- Name: "forum_group_forum_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_group_forum_id" on "forum" using btree ( "group_forum_id" "int4_ops" );

--
-- TOC Entry ID 314 (OID 19033)
--
-- Name: "forum_forumid_isfollowupto" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_forumid_isfollowupto" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops" );

--
-- TOC Entry ID 315 (OID 19033)
--
-- Name: "forum_forumid_threadid_mostrece" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_forumid_threadid_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "thread_id" "int4_ops", "most_recent_date" "int4_ops" );

--
-- TOC Entry ID 316 (OID 19033)
--
-- Name: "forum_threadid_isfollowupto" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_threadid_isfollowupto" on "forum" using btree ( "thread_id" "int4_ops", "is_followup_to" "int4_ops" );

--
-- TOC Entry ID 317 (OID 19033)
--
-- Name: "forum_forumid_isfollto_mostrece" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_forumid_isfollto_mostrece" on "forum" using btree ( "group_forum_id" "int4_ops", "is_followup_to" "int4_ops", "most_recent_date" "int4_ops" );

--
-- TOC Entry ID 318 (OID 19115)
--
-- Name: "forum_group_list_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_group_list_group_id" on "forum_group_list" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 319 (OID 19173)
--
-- Name: "forummonitoredforums_user" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forummonitoredforums_user" on "forum_monitored_forums" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 320 (OID 19173)
--
-- Name: "forum_monitor_combo_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_monitor_combo_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops", "user_id" "int4_ops" );

--
-- TOC Entry ID 321 (OID 19173)
--
-- Name: "forum_monitor_thread_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_monitor_thread_id" on "forum_monitored_forums" using btree ( "forum_id" "int4_ops" );

--
-- TOC Entry ID 322 (OID 19287)
--
-- Name: "foundry_news_foundry_approved_d" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_news_foundry_approved_d" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops", "approve_date" "int4_ops" );

--
-- TOC Entry ID 323 (OID 19287)
--
-- Name: "foundry_news_foundry_approved" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_news_foundry_approved" on "foundry_news" using btree ( "foundry_id" "int4_ops", "is_approved" "int4_ops" );

--
-- TOC Entry ID 324 (OID 19287)
--
-- Name: "foundry_news_foundry" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_news_foundry" on "foundry_news" using btree ( "foundry_id" "int4_ops" );

--
-- TOC Entry ID 325 (OID 19287)
--
-- Name: "foundrynews_foundry_date_approv" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundrynews_foundry_date_approv" on "foundry_news" using btree ( "foundry_id" "int4_ops", "approve_date" "int4_ops", "is_approved" "int4_ops" );

--
-- TOC Entry ID 326 (OID 19328)
--
-- Name: "foundry_project_group_rank" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_project_group_rank" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops", "rank" "int4_ops" );

--
-- TOC Entry ID 327 (OID 19328)
--
-- Name: "foundry_project_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_project_group" on "foundry_preferred_projects" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 328 (OID 19367)
--
-- Name: "foundry_projects_foundry" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundry_projects_foundry" on "foundry_projects" using btree ( "foundry_id" "int4_ops" );

--
-- TOC Entry ID 329 (OID 19404)
--
-- Name: "frs_file_date" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frs_file_date" on "frs_file" using btree ( "post_date" "int4_ops" );

--
-- TOC Entry ID 330 (OID 19404)
--
-- Name: "frs_file_release_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frs_file_release_id" on "frs_file" using btree ( "release_id" "int4_ops" );

--
-- TOC Entry ID 331 (OID 19514)
--
-- Name: "package_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "package_group_id" on "frs_package" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 332 (OID 19616)
--
-- Name: "frs_release_package" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frs_release_package" on "frs_release" using btree ( "package_id" "int4_ops" );

--
-- TOC Entry ID 333 (OID 19745)
--
-- Name: "group_history_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "group_history_group_id" on "group_history" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 334 (OID 19851)
--
-- Name: "group_unix_uniq" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "group_unix_uniq" on "groups" using btree ( "unix_group_name" "varchar_ops" );

--
-- TOC Entry ID 335 (OID 19851)
--
-- Name: "groups_type" Type: INDEX Owner: tperdue
--

CREATE  INDEX "groups_type" on "groups" using btree ( "type" "int4_ops" );

--
-- TOC Entry ID 336 (OID 19851)
--
-- Name: "groups_public" Type: INDEX Owner: tperdue
--

CREATE  INDEX "groups_public" on "groups" using btree ( "is_public" "int4_ops" );

--
-- TOC Entry ID 337 (OID 19851)
--
-- Name: "groups_status" Type: INDEX Owner: tperdue
--

CREATE  INDEX "groups_status" on "groups" using btree ( "status" "bpchar_ops" );

--
-- TOC Entry ID 338 (OID 19996)
--
-- Name: "mail_group_list_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "mail_group_list_group" on "mail_group_list" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 339 (OID 20055)
--
-- Name: "news_bytes_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "news_bytes_group" on "news_bytes" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 340 (OID 20055)
--
-- Name: "news_bytes_approved" Type: INDEX Owner: tperdue
--

CREATE  INDEX "news_bytes_approved" on "news_bytes" using btree ( "is_approved" "int4_ops" );

--
-- TOC Entry ID 341 (OID 20055)
--
-- Name: "news_bytes_forum" Type: INDEX Owner: tperdue
--

CREATE  INDEX "news_bytes_forum" on "news_bytes" using btree ( "forum_id" "int4_ops" );

--
-- TOC Entry ID 342 (OID 20055)
--
-- Name: "news_group_date" Type: INDEX Owner: tperdue
--

CREATE  INDEX "news_group_date" on "news_bytes" using btree ( "group_id" "int4_ops", "date" "int4_ops" );

--
-- TOC Entry ID 343 (OID 20055)
--
-- Name: "news_approved_date" Type: INDEX Owner: tperdue
--

CREATE  INDEX "news_approved_date" on "news_bytes" using btree ( "is_approved" "int4_ops", "date" "int4_ops" );

--
-- TOC Entry ID 344 (OID 20115)
--
-- Name: "people_job_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "people_job_group_id" on "people_job" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 345 (OID 20504)
--
-- Name: "project_assigned_to_assigned_to" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_assigned_to_assigned_to" on "project_assigned_to" using btree ( "assigned_to_id" "int4_ops" );

--
-- TOC Entry ID 346 (OID 20504)
--
-- Name: "project_assigned_to_task_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_assigned_to_task_id" on "project_assigned_to" using btree ( "project_task_id" "int4_ops" );

--
-- TOC Entry ID 347 (OID 20541)
--
-- Name: "project_is_dependent_on_task_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_is_dependent_on_task_id" on "project_dependencies" using btree ( "is_dependent_on_task_id" "int4_ops" );

--
-- TOC Entry ID 348 (OID 20541)
--
-- Name: "project_dependencies_task_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_dependencies_task_id" on "project_dependencies" using btree ( "project_task_id" "int4_ops" );

--
-- TOC Entry ID 349 (OID 20578)
--
-- Name: "project_group_list_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_group_list_group_id" on "project_group_list" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 350 (OID 20633)
--
-- Name: "project_history_task_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_history_task_id" on "project_history" using btree ( "project_task_id" "int4_ops" );

--
-- TOC Entry ID 351 (OID 20691)
--
-- Name: "project_metric_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_metric_group" on "project_metric" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 352 (OID 20832)
--
-- Name: "projecttask_projid_status" Type: INDEX Owner: tperdue
--

CREATE  INDEX "projecttask_projid_status" on "project_task" using btree ( "group_project_id" "int4_ops", "status_id" "int4_ops" );

--
-- TOC Entry ID 353 (OID 20832)
--
-- Name: "project_task_group_project_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_task_group_project_id" on "project_task" using btree ( "group_project_id" "int4_ops" );

--
-- TOC Entry ID 354 (OID 20900)
--
-- Name: "projectweeklymetric_ranking" Type: INDEX Owner: tperdue
--

CREATE  INDEX "projectweeklymetric_ranking" on "project_weekly_metric" using btree ( "ranking" "int4_ops" );

--
-- TOC Entry ID 355 (OID 20900)
--
-- Name: "project_metric_weekly_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_metric_weekly_group" on "project_weekly_metric" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 356 (OID 20914)
--
-- Name: "session_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "session_user_id" on "session" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 357 (OID 20914)
--
-- Name: "session_time" Type: INDEX Owner: tperdue
--

CREATE  INDEX "session_time" on "session" using btree ( "time" "int4_ops" );

--
-- TOC Entry ID 358 (OID 20953)
--
-- Name: "snippet_language" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_language" on "snippet" using btree ( "language" "int4_ops" );

--
-- TOC Entry ID 359 (OID 20953)
--
-- Name: "snippet_category" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_category" on "snippet" using btree ( "category" "int4_ops" );

--
-- TOC Entry ID 360 (OID 21013)
--
-- Name: "snippet_package_language" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_package_language" on "snippet_package" using btree ( "language" "int4_ops" );

--
-- TOC Entry ID 361 (OID 21013)
--
-- Name: "snippet_package_category" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_package_category" on "snippet_package" using btree ( "category" "int4_ops" );

--
-- TOC Entry ID 362 (OID 21069)
--
-- Name: "snippet_package_item_pkg_ver" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_package_item_pkg_ver" on "snippet_package_item" using btree ( "snippet_package_version_id" "int4_ops" );

--
-- TOC Entry ID 363 (OID 21106)
--
-- Name: "snippet_package_version_pkg_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_package_version_pkg_id" on "snippet_package_version" using btree ( "snippet_package_id" "int4_ops" );

--
-- TOC Entry ID 364 (OID 21162)
--
-- Name: "snippet_version_snippet_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "snippet_version_snippet_id" on "snippet_version" using btree ( "snippet_id" "int4_ops" );

--
-- TOC Entry ID 365 (OID 21211)
--
-- Name: "pages_by_day_day" Type: INDEX Owner: tperdue
--

CREATE  INDEX "pages_by_day_day" on "stats_agg_pages_by_day" using btree ( "day" "int4_ops" );

--
-- TOC Entry ID 366 (OID 21224)
--
-- Name: "statsftpdl_day_fileid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statsftpdl_day_fileid" on "stats_ftp_downloads" using btree ( "day" "int4_ops", "filerelease_id" "int4_ops" );

--
-- TOC Entry ID 367 (OID 21241)
--
-- Name: "statshttpdl_day_fileid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statshttpdl_day_fileid" on "stats_http_downloads" using btree ( "day" "int4_ops", "filerelease_id" "int4_ops" );

--
-- TOC Entry ID 368 (OID 21277)
--
-- Name: "supported_languages_code" Type: INDEX Owner: tperdue
--

CREATE  INDEX "supported_languages_code" on "supported_languages" using btree ( "language_code" "bpchar_ops" );

--
-- TOC Entry ID 369 (OID 21379)
--
-- Name: "survey_questions_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_questions_group" on "survey_questions" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 370 (OID 21414)
--
-- Name: "survey_rating_aggregate_type_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_rating_aggregate_type_id" on "survey_rating_aggregate" using btree ( "type" "int4_ops", "id" "int4_ops" );

--
-- TOC Entry ID 371 (OID 21431)
--
-- Name: "survey_rating_responses_user_ty" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_rating_responses_user_ty" on "survey_rating_response" using btree ( "user_id" "int4_ops", "type" "int4_ops", "id" "int4_ops" );

--
-- TOC Entry ID 372 (OID 21431)
--
-- Name: "survey_rating_responses_type_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_rating_responses_type_id" on "survey_rating_response" using btree ( "type" "int4_ops", "id" "int4_ops" );

--
-- TOC Entry ID 373 (OID 21450)
--
-- Name: "survey_responses_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_responses_group_id" on "survey_responses" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 374 (OID 21450)
--
-- Name: "survey_responses_user_survey_qu" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_responses_user_survey_qu" on "survey_responses" using btree ( "user_id" "int4_ops", "survey_id" "int4_ops", "question_id" "int4_ops" );

--
-- TOC Entry ID 375 (OID 21450)
--
-- Name: "survey_responses_user_survey" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_responses_user_survey" on "survey_responses" using btree ( "user_id" "int4_ops", "survey_id" "int4_ops" );

--
-- TOC Entry ID 376 (OID 21450)
--
-- Name: "survey_responses_survey_questio" Type: INDEX Owner: tperdue
--

CREATE  INDEX "survey_responses_survey_questio" on "survey_responses" using btree ( "survey_id" "int4_ops", "question_id" "int4_ops" );

--
-- TOC Entry ID 377 (OID 21505)
--
-- Name: "surveys_group" Type: INDEX Owner: tperdue
--

CREATE  INDEX "surveys_group" on "surveys" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 378 (OID 21700)
--
-- Name: "rank_forumposts_week_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "rank_forumposts_week_idx" on "top_group" using btree ( "rank_forumposts_week" "int4_ops" );

--
-- TOC Entry ID 379 (OID 21700)
--
-- Name: "rank_downloads_week_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "rank_downloads_week_idx" on "top_group" using btree ( "rank_downloads_week" "int4_ops" );

--
-- TOC Entry ID 380 (OID 21700)
--
-- Name: "pageviews_proj_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "pageviews_proj_idx" on "top_group" using btree ( "pageviews_proj" "int4_ops" );

--
-- TOC Entry ID 381 (OID 21700)
--
-- Name: "rank_userrank_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "rank_userrank_idx" on "top_group" using btree ( "rank_userrank" "int4_ops" );

--
-- TOC Entry ID 382 (OID 21700)
--
-- Name: "rank_downloads_all_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "rank_downloads_all_idx" on "top_group" using btree ( "rank_downloads_all" "int4_ops" );

--
-- TOC Entry ID 383 (OID 21761)
--
-- Name: "parent_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "parent_idx" on "trove_cat" using btree ( "parent" "int4_ops" );

--
-- TOC Entry ID 384 (OID 21761)
--
-- Name: "root_parent_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "root_parent_idx" on "trove_cat" using btree ( "root_parent" "int4_ops" );

--
-- TOC Entry ID 385 (OID 21761)
--
-- Name: "version_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "version_idx" on "trove_cat" using btree ( "version" "int4_ops" );

--
-- TOC Entry ID 386 (OID 21825)
--
-- Name: "trove_group_link_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "trove_group_link_group_id" on "trove_group_link" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 387 (OID 21825)
--
-- Name: "trove_group_link_cat_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "trove_group_link_cat_id" on "trove_group_link" using btree ( "trove_cat_id" "int4_ops" );

--
-- TOC Entry ID 388 (OID 21885)
--
-- Name: "user_bookmark_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_bookmark_user_id" on "user_bookmarks" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 389 (OID 21937)
--
-- Name: "user_diary_user" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_diary_user" on "user_diary" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 390 (OID 21937)
--
-- Name: "user_diary_user_date" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_diary_user_date" on "user_diary" using btree ( "user_id" "int4_ops", "date_posted" "int4_ops" );

--
-- TOC Entry ID 391 (OID 21937)
--
-- Name: "user_diary_date" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_diary_date" on "user_diary" using btree ( "date_posted" "int4_ops" );

--
-- TOC Entry ID 392 (OID 21993)
--
-- Name: "user_diary_monitor_user" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_diary_monitor_user" on "user_diary_monitor" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 393 (OID 21993)
--
-- Name: "user_diary_monitor_monitored_us" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_diary_monitor_monitored_us" on "user_diary_monitor" using btree ( "monitored_user" "int4_ops" );

--
-- TOC Entry ID 394 (OID 22030)
--
-- Name: "user_group_group_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_group_group_id" on "user_group" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 395 (OID 22030)
--
-- Name: "bug_flags_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "bug_flags_idx" on "user_group" using btree ( "bug_flags" "int4_ops" );

--
-- TOC Entry ID 396 (OID 22030)
--
-- Name: "project_flags_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "project_flags_idx" on "user_group" using btree ( "project_flags" "int4_ops" );

--
-- TOC Entry ID 397 (OID 22030)
--
-- Name: "user_group_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_group_user_id" on "user_group" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 398 (OID 22030)
--
-- Name: "admin_flags_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "admin_flags_idx" on "user_group" using btree ( "admin_flags" "bpchar_ops" );

--
-- TOC Entry ID 399 (OID 22030)
--
-- Name: "forum_flags_idx" Type: INDEX Owner: tperdue
--

CREATE  INDEX "forum_flags_idx" on "user_group" using btree ( "forum_flags" "int4_ops" );

--
-- TOC Entry ID 400 (OID 22030)
--
-- Name: "usergroup_uniq_groupid_userid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "usergroup_uniq_groupid_userid" on "user_group" using btree ( "group_id" "int4_ops", "user_id" "int4_ops" );

--
-- TOC Entry ID 401 (OID 22135)
--
-- Name: "user_metric0_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_metric0_user_id" on "user_metric0" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 402 (OID 22163)
--
-- Name: "user_pref_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_pref_user_id" on "user_preferences" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 403 (OID 22194)
--
-- Name: "user_ratings_rated_by" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_ratings_rated_by" on "user_ratings" using btree ( "rated_by" "int4_ops" );

--
-- TOC Entry ID 404 (OID 22194)
--
-- Name: "user_ratings_user_id" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_ratings_user_id" on "user_ratings" using btree ( "user_id" "int4_ops" );

--
-- TOC Entry ID 405 (OID 22230)
--
-- Name: "users_namename_uniq" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "users_namename_uniq" on "users" using btree ( "user_name" "text_ops" );

--
-- TOC Entry ID 406 (OID 22230)
--
-- Name: "users_status" Type: INDEX Owner: tperdue
--

CREATE  INDEX "users_status" on "users" using btree ( "status" "bpchar_ops" );

--
-- TOC Entry ID 407 (OID 22230)
--
-- Name: "users_user_pw" Type: INDEX Owner: tperdue
--

CREATE  INDEX "users_user_pw" on "users" using btree ( "user_pw" "varchar_ops" );

--
-- TOC Entry ID 408 (OID 22336)
--
-- Name: "projectsumsagg_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "projectsumsagg_groupid" on "project_sums_agg" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 409 (OID 22388)
--
-- Name: "idx_prdb_dbname" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "idx_prdb_dbname" on "prdb_dbs" using btree ( "dbname" "text_ops" );

--
-- TOC Entry ID 410 (OID 22500)
--
-- Name: "idx_vhost_groups" Type: INDEX Owner: tperdue
--

CREATE  INDEX "idx_vhost_groups" on "prweb_vhost" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 411 (OID 22500)
--
-- Name: "idx_vhost_hostnames" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "idx_vhost_hostnames" on "prweb_vhost" using btree ( "vhost_name" "text_ops" );

--
-- TOC Entry ID 412 (OID 22552)
--
-- Name: "artgrouplist_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artgrouplist_groupid" on "artifact_group_list" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 413 (OID 22552)
--
-- Name: "artgrouplist_groupid_public" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artgrouplist_groupid_public" on "artifact_group_list" using btree ( "group_id" "int4_ops", "is_public" "int4_ops" );

--
-- TOC Entry ID 414 (OID 22668)
--
-- Name: "artperm_groupartifactid_userid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "artperm_groupartifactid_userid" on "artifact_perm" using btree ( "group_artifact_id" "int4_ops", "user_id" "int4_ops" );

--
-- TOC Entry ID 415 (OID 22668)
--
-- Name: "artperm_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artperm_groupartifactid" on "artifact_perm" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 416 (OID 22737)
--
-- Name: "artcategory_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artcategory_groupartifactid" on "artifact_category" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 417 (OID 22789)
--
-- Name: "artgroup_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artgroup_groupartifactid" on "artifact_group" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 418 (OID 22888)
--
-- Name: "art_groupartid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_groupartid" on "artifact" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 419 (OID 22888)
--
-- Name: "art_groupartid_statusid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_groupartid_statusid" on "artifact" using btree ( "group_artifact_id" "int4_ops", "status_id" "int4_ops" );

--
-- TOC Entry ID 420 (OID 22888)
--
-- Name: "art_groupartid_assign" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_groupartid_assign" on "artifact" using btree ( "group_artifact_id" "int4_ops", "assigned_to" "int4_ops" );

--
-- TOC Entry ID 421 (OID 22888)
--
-- Name: "art_groupartid_submit" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_groupartid_submit" on "artifact" using btree ( "group_artifact_id" "int4_ops", "submitted_by" "int4_ops" );

--
-- TOC Entry ID 422 (OID 22888)
--
-- Name: "art_submit_status" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_submit_status" on "artifact" using btree ( "submitted_by" "int4_ops", "status_id" "int4_ops" );

--
-- TOC Entry ID 423 (OID 22888)
--
-- Name: "art_assign_status" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_assign_status" on "artifact" using btree ( "assigned_to" "int4_ops", "status_id" "int4_ops" );

--
-- TOC Entry ID 424 (OID 22888)
--
-- Name: "art_groupartid_artifactid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "art_groupartid_artifactid" on "artifact" using btree ( "group_artifact_id" "int4_ops", "artifact_id" "int4_ops" );

--
-- TOC Entry ID 425 (OID 22993)
--
-- Name: "arthistory_artid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "arthistory_artid" on "artifact_history" using btree ( "artifact_id" "int4_ops" );

--
-- TOC Entry ID 426 (OID 22993)
--
-- Name: "arthistory_artid_entrydate" Type: INDEX Owner: tperdue
--

CREATE  INDEX "arthistory_artid_entrydate" on "artifact_history" using btree ( "artifact_id" "int4_ops", "entrydate" "int4_ops" );

--
-- TOC Entry ID 427 (OID 23067)
--
-- Name: "artfile_artid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artfile_artid" on "artifact_file" using btree ( "artifact_id" "int4_ops" );

--
-- TOC Entry ID 428 (OID 23067)
--
-- Name: "artfile_artid_adddate" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artfile_artid_adddate" on "artifact_file" using btree ( "artifact_id" "int4_ops", "adddate" "int4_ops" );

--
-- TOC Entry ID 429 (OID 23145)
--
-- Name: "artmessage_artid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artmessage_artid" on "artifact_message" using btree ( "artifact_id" "int4_ops" );

--
-- TOC Entry ID 430 (OID 23145)
--
-- Name: "artmessage_artid_adddate" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artmessage_artid_adddate" on "artifact_message" using btree ( "artifact_id" "int4_ops", "adddate" "int4_ops" );

--
-- TOC Entry ID 431 (OID 23218)
--
-- Name: "artmonitor_artifactid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artmonitor_artifactid" on "artifact_monitor" using btree ( "artifact_id" "int4_ops" );

--
-- TOC Entry ID 432 (OID 23269)
--
-- Name: "artifactcannedresponses_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artifactcannedresponses_groupid" on "artifact_canned_responses" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 433 (OID 23301)
--
-- Name: "artifactcountsagg_groupartid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "artifactcountsagg_groupartid" on "artifact_counts_agg" using btree ( "group_artifact_id" "int4_ops" );

--
-- TOC Entry ID 434 (OID 23313)
--
-- Name: "statssitepgsbyday_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssitepgsbyday_oid" on stats_site_pages_by_day(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 435 (OID 23313)
--
-- Name: "statssitepagesbyday_month_day" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statssitepagesbyday_month_day" on "stats_site_pages_by_day" using btree ( "month" "int4_ops", "day" "int4_ops" );

--
-- TOC Entry ID 436 (OID 23388)
--
-- Name: "frsdlfileagg_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "frsdlfileagg_oid" on frs_dlstats_file_agg(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 437 (OID 23388)
--
-- Name: "frsdlfileagg_month_day_file" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "frsdlfileagg_month_day_file" on "frs_dlstats_file_agg" using btree ( "month" "int4_ops", "day" "int4_ops", "file_id" "int4_ops" );

--
-- TOC Entry ID 438 (OID 23401)
--
-- Name: "statsaggsitebygrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsaggsitebygrp_oid" on stats_agg_site_by_group(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 439 (OID 23401)
--
-- Name: "statssitebygroup_month_day_grou" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssitebygroup_month_day_grou" on "stats_agg_site_by_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 440 (OID 23414)
--
-- Name: "statsprojectmetric_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsprojectmetric_oid" on stats_project_metric(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 441 (OID 23414)
--
-- Name: "statsprojectmetric_month_day_gr" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsprojectmetric_month_day_gr" on "stats_project_metric" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 442 (OID 23433)
--
-- Name: "statsagglogobygrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsagglogobygrp_oid" on stats_agg_logo_by_group(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 443 (OID 23433)
--
-- Name: "statslogobygroup_month_day_grou" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statslogobygroup_month_day_grou" on "stats_agg_logo_by_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 444 (OID 23446)
--
-- Name: "statssubdpages_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssubdpages_oid" on stats_subd_pages(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 445 (OID 23446)
--
-- Name: "statssubdpages_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssubdpages_month_day_group" on "stats_subd_pages" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 446 (OID 23486)
--
-- Name: "statscvsgrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statscvsgrp_oid" on stats_cvs_group(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 447 (OID 23486)
--
-- Name: "statscvsgroup_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statscvsgroup_month_day_group" on "stats_cvs_group" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 448 (OID 23507)
--
-- Name: "statsprojectdevelop_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsprojectdevelop_oid" on stats_project_developers(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 449 (OID 23507)
--
-- Name: "statsprojectdev_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsprojectdev_month_day_group" on "stats_project_developers" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 450 (OID 23524)
--
-- Name: "statsproject_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsproject_oid" on stats_project(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 451 (OID 23524)
--
-- Name: "statsproject_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statsproject_month_day_group" on "stats_project" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 452 (OID 23567)
--
-- Name: "statssite_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssite_oid" on stats_site(month,day) ;
-- using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 453 (OID 23567)
--
-- Name: "statssite_month_day" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "statssite_month_day" on "stats_site" using btree ( "month" "int4_ops", "day" "int4_ops" );

--
-- TOC Entry ID 454 (OID 25693)
--
-- Name: "user_metric_history_date_userid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "user_metric_history_date_userid" on "user_metric_history" using btree ( "month" "int4_ops", "day" "int4_ops", "user_id" "int4_ops" );

--
-- TOC Entry ID 455 (OID 25710)
--
-- Name: "foundryprojectrankingsagg_found" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundryprojectrankingsagg_found" on "foundry_project_rankings_agg" using btree ( "foundry_id" "int4_ops", "ranking" "int4_ops" );

--
-- TOC Entry ID 456 (OID 25728)
--
-- Name: "foundryprojdlsagg_foundryid_dls" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundryprojdlsagg_foundryid_dls" on "foundry_project_downloads_agg" using btree ( "foundry_id" "int4_ops", "downloads" "int4_ops" );

--
-- TOC Entry ID 457 (OID 25745)
--
-- Name: "frsdlfiletotal_fileid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlfiletotal_fileid" on "frs_dlstats_filetotal_agg" using btree ( "file_id" "int4_ops" );

--
-- TOC Entry ID 458 (OID 25759)
--
-- Name: "frsdlgrouptotal_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlgrouptotal_groupid" on "frs_dlstats_grouptotal_agg" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 459 (OID 25773)
--
-- Name: "frsdlgroup_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlgroup_groupid" on "frs_dlstats_group_agg" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 460 (OID 25773)
--
-- Name: "frsdlgroup_month_day_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlgroup_month_day_groupid" on "frs_dlstats_group_agg" using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- TOC Entry ID 461 (OID 25792)
--
-- Name: "statsprojectmonths_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statsprojectmonths_groupid" on "stats_project_months" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 462 (OID 25792)
--
-- Name: "statsprojectmonths_groupid_mont" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statsprojectmonths_groupid_mont" on "stats_project_months" using btree ( "group_id" "int4_ops", "month" "int4_ops" );

--
-- TOC Entry ID 463 (OID 25834)
--
-- Name: "statsprojectall_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statsprojectall_groupid" on "stats_project_all" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 464 (OID 25884)
--
-- Name: "statsproject30_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statsproject30_groupid" on "stats_project_last_30" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 465 (OID 25935)
--
-- Name: "statssitelast30_month_day" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statssitelast30_month_day" on "stats_site_last_30" using btree ( "month" "int4_ops", "day" "int4_ops" );

--
-- TOC Entry ID 466 (OID 25967)
--
-- Name: "statssitemonths_month" Type: INDEX Owner: tperdue
--

CREATE  INDEX "statssitemonths_month" on "stats_site_months" using btree ( "month" "int4_ops" );

--
-- TOC Entry ID 467 (OID 26026)
--
-- Name: "troveagg_trovecatid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "troveagg_trovecatid" on "trove_agg" using btree ( "trove_cat_id" "int4_ops" );

--
-- TOC Entry ID 468 (OID 26026)
--
-- Name: "troveagg_trovecatid_ranking" Type: INDEX Owner: tperdue
--

CREATE  INDEX "troveagg_trovecatid_ranking" on "trove_agg" using btree ( "trove_cat_id" "int4_ops", "ranking" "int4_ops" );

ALTER TABLE user_group ADD CONSTRAINT user_group_user_id_fk
	FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL ;

ALTER TABLE user_group ADD CONSTRAINT user_group_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

ALTER TABLE forum ADD CONSTRAINT forum_group_forum_id_fk
        FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) MATCH FULL ;

ALTER TABLE forum_group_list ADD CONSTRAINT forum_group_list_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

ALTER TABLE project_group_list ADD CONSTRAINT project_group_list_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

--
-- TOC Entry ID 644 (OID 24227)
--
-- Name: "RI_ConstraintTrigger_24226" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 640 (OID 24229)
--
-- Name: "RI_ConstraintTrigger_24228" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER DELETE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 641 (OID 24231)
--
-- Name: "RI_ConstraintTrigger_24230" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER UPDATE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 645 (OID 24233)
--
-- Name: "RI_ConstraintTrigger_24232" Type: TRIGGER Owner: tperdue
--

ALTER TABLE project_task ADD CONSTRAINT project_task_created_by_fk
	FOREIGN KEY (created_by) REFERENCES users(user_id) MATCH FULL ;

--
-- TOC Entry ID 646 (OID 24239)
--
-- Name: "RI_ConstraintTrigger_24238" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 642 (OID 24241)
--
-- Name: "RI_ConstraintTrigger_24240" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER DELETE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 643 (OID 24243)
--
-- Name: "RI_ConstraintTrigger_24242" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER UPDATE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 659 (OID 24245)
--
-- Name: "RI_ConstraintTrigger_24244" Type: TRIGGER Owner: tperdue
--

ALTER TABLE users ADD CONSTRAINT users_languageid_fk
	FOREIGN KEY (language) REFERENCES supported_languages(language_id) MATCH FULL ;

ALTER TABLE artifact_group_list ADD CONSTRAINT artifactgroup_groupid_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_userid_fk
        FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_autoassignto_fk
        FOREIGN KEY (auto_assign_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact_group ADD CONSTRAINT artifactgroup_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact ADD CONSTRAINT artifact_artifactgroupid_fk
        FOREIGN KEY (artifact_group_id) REFERENCES artifact_group(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_assignedto_fk
        FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_categoryid_fk
        FOREIGN KEY (category_id) REFERENCES artifact_category(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_resolutionid_fk
        FOREIGN KEY (resolution_id) REFERENCES artifact_resolution(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_statusid_fk
        FOREIGN KEY (status_id) REFERENCES artifact_status(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_modby_fk
        FOREIGN KEY (mod_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_monitor ADD CONSTRAINT artifactmonitor_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;

--
-- TOC Entry ID 685 (OID 24376)
--
-- Name: artifactgrouplist_insert_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "artifactgrouplist_insert_trig" AFTER INSERT ON "artifact_group_list"  FOR EACH ROW EXECUTE PROCEDURE "artifactgrouplist_insert_agg" ();

--
-- TOC Entry ID 716 (OID 24377)
--
-- Name: artifactgroup_update_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "artifactgroup_update_trig" AFTER UPDATE ON "artifact"  FOR EACH ROW EXECUTE PROCEDURE "artifactgroup_update_agg" ();

--
-- TOC Entry ID 608 (OID 24378)
--
-- Name: forumgrouplist_insert_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "forumgrouplist_insert_trig" AFTER INSERT ON "forum_group_list"  FOR EACH ROW EXECUTE PROCEDURE "forumgrouplist_insert_agg" ();

ALTER TABLE frs_file ADD CONSTRAINT frsfile_processorid_fk
	FOREIGN KEY (processor_id) REFERENCES frs_processor(processor_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_releaseid_fk
	FOREIGN KEY (release_id) REFERENCES frs_release(release_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_typeid_fk
	FOREIGN KEY (type_id) REFERENCES frs_filetype(type_id) MATCH FULL;

ALTER TABLE frs_package ADD CONSTRAINT frspackage_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;
ALTER TABLE frs_package ADD CONSTRAINT frspackage_groupid_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

ALTER TABLE frs_release ADD CONSTRAINT frsrelease_packageid_fk
	FOREIGN KEY (package_id) REFERENCES frs_package(package_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_releasedby_fk
	FOREIGN KEY (released_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;

--
-- TOC Entry ID 725 (OID 24427)
--
-- Name: forum_insert_agg Type: RULE Owner: tperdue
--

CREATE RULE forum_insert_agg AS ON INSERT TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count + 1) WHERE (forum_agg_msg_count.group_forum_id = new.group_forum_id);
--
-- TOC Entry ID 726 (OID 24428)
--
-- Name: forum_delete_agg Type: RULE Owner: tperdue
--

CREATE RULE forum_delete_agg AS ON DELETE TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count - 1) WHERE (forum_agg_msg_count.group_forum_id = old.group_forum_id);
--
-- TOC Entry ID 727 (OID 24429)
--
-- Name: artifact_insert_agg Type: RULE Owner: tperdue
--

CREATE RULE artifact_insert_agg AS ON INSERT TO artifact DO UPDATE artifact_counts_agg SET count = (artifact_counts_agg.count + 1), open_count = (artifact_counts_agg.open_count + 1) WHERE (artifact_counts_agg.group_artifact_id = new.group_artifact_id);
--
-- TOC Entry ID 3 (OID 18724)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"canned_responses_pk_seq"', 1, 'f');

--
-- TOC Entry ID 5 (OID 18774)
--
-- Name: db_images_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"db_images_pk_seq"', 1, 'f');

--
-- TOC Entry ID 7 (OID 18840)
--
-- Name: doc_data_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_data_pk_seq"', 1, 'f');

--
-- TOC Entry ID 9 (OID 18905)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_groups_pk_seq"', 1, 'f');

--
-- TOC Entry ID 11 (OID 18942)
--
-- Name: doc_states_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_states_pk_seq"', 1, 'f');

--
-- TOC Entry ID 13 (OID 18977)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"filemodule_monitor_pk_seq"', 1, 'f');

--
-- TOC Entry ID 15 (OID 19014)
--
-- Name: forum_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_pk_seq"', 1, 'f');

--
-- TOC Entry ID 17 (OID 19096)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_group_list_pk_seq"', 1, 'f');

--
-- TOC Entry ID 19 (OID 19154)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_monitored_forums_pk_seq"', 1, 'f');

--
-- TOC Entry ID 21 (OID 19191)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_saved_place_pk_seq"', 1, 'f');

--
-- TOC Entry ID 23 (OID 19268)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_news_pk_seq"', 1, 'f');

--
-- TOC Entry ID 25 (OID 19309)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_preferred_projec_pk_seq"', 1, 'f');

--
-- TOC Entry ID 27 (OID 19348)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_projects_pk_seq"', 1, 'f');

--
-- TOC Entry ID 29 (OID 19385)
--
-- Name: frs_file_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_file_pk_seq"', 1, 'f');

--
-- TOC Entry ID 31 (OID 19446)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_filetype_pk_seq"', 9999, 't');

--
-- TOC Entry ID 33 (OID 19495)
--
-- Name: frs_package_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_package_pk_seq"', 1, 'f');

--
-- TOC Entry ID 35 (OID 19548)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_processor_pk_seq"', 9999, 't');

--
-- TOC Entry ID 37 (OID 19597)
--
-- Name: frs_release_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_release_pk_seq"', 1, 'f');

--
-- TOC Entry ID 39 (OID 19658)
--
-- Name: frs_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_status_pk_seq"', 3, 't');

--
-- TOC Entry ID 41 (OID 19707)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_cvs_history_pk_seq"', 1, 'f');

--
-- TOC Entry ID 43 (OID 19726)
--
-- Name: group_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_history_pk_seq"', 1, 'f');

--
-- TOC Entry ID 45 (OID 19783)
--
-- Name: group_type_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_type_pk_seq"', 1, 'f');

--
-- TOC Entry ID 47 (OID 19832)
--
-- Name: groups_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"groups_pk_seq"', 4, 't');

--
-- TOC Entry ID 49 (OID 19977)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"mail_group_list_pk_seq"', 1, 'f');

--
-- TOC Entry ID 51 (OID 20036)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"news_bytes_pk_seq"', 1, 'f');

--
-- TOC Entry ID 53 (OID 20096)
--
-- Name: people_job_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_pk_seq"', 1, 'f');

--
-- TOC Entry ID 55 (OID 20156)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_category_pk_seq"', 7, 't');

--
-- TOC Entry ID 57 (OID 20207)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_inventory_pk_seq"', 1, 'f');

--
-- TOC Entry ID 59 (OID 20248)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_status_pk_seq"', 1, 'f');

--
-- TOC Entry ID 61 (OID 20297)
--
-- Name: people_skill_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_pk_seq"', 1, 'f');

--
-- TOC Entry ID 63 (OID 20346)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_inventory_pk_seq"', 1, 'f');

--
-- TOC Entry ID 65 (OID 20387)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_level_pk_seq"', 5, 't');

--
-- TOC Entry ID 67 (OID 20436)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_year_pk_seq"', 5, 't');

--
-- TOC Entry ID 69 (OID 20485)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_assigned_to_pk_seq"', 1, 'f');

--
-- TOC Entry ID 71 (OID 20522)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_dependencies_pk_seq"', 1, 'f');

--
-- TOC Entry ID 73 (OID 20559)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_group_list_pk_seq"', 1, 't');

--
-- TOC Entry ID 75 (OID 20614)
--
-- Name: project_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_history_pk_seq"', 1, 'f');

--
-- TOC Entry ID 77 (OID 20672)
--
-- Name: project_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_pk_seq"', 1, 'f');

--
-- TOC Entry ID 79 (OID 20708)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE SET Owner:
--

-- SELECT setval ('"project_metric_tmp1_pk_seq"', 1, 'f');

--
-- TOC Entry ID 81 (OID 20744)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_weekly_tm_pk_seq"', 1, 'f');

--
-- TOC Entry ID 83 (OID 20763)
--
-- Name: project_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_status_pk_seq"', 1, 'f');

--
-- TOC Entry ID 85 (OID 20813)
--
-- Name: project_task_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_task_pk_seq"', 100, 't');

--
-- TOC Entry ID 87 (OID 20881)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_weekly_metric_pk_seq"', 1, 'f');

--
-- TOC Entry ID 89 (OID 20934)
--
-- Name: snippet_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_pk_seq"', 1, 'f');

--
-- TOC Entry ID 91 (OID 20994)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_pk_seq"', 1, 'f');

--
-- TOC Entry ID 93 (OID 21050)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_item_pk_seq"', 1, 'f');

--
-- TOC Entry ID 95 (OID 21087)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_version_pk_seq"', 1, 'f');

--
-- TOC Entry ID 97 (OID 21143)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_version_pk_seq"', 1, 'f');

--
-- TOC Entry ID 99 (OID 21258)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"supported_languages_pk_seq"', 23, 't');

--
-- TOC Entry ID 101 (OID 21310)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"survey_question_types_pk_seq"', 1, 'f');

--
-- TOC Entry ID 103 (OID 21360)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"survey_questions_pk_seq"', 1, 'f');

--
-- TOC Entry ID 105 (OID 21486)
--
-- Name: surveys_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"surveys_pk_seq"', 1, 'f');

--
-- TOC Entry ID 107 (OID 21542)
--
-- Name: system_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_history_pk_seq"', 1, 'f');

--
-- TOC Entry ID 109 (OID 21561)
--
-- Name: system_machines_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_machines_pk_seq"', 1, 'f');

--
-- TOC Entry ID 111 (OID 21580)
--
-- Name: system_news_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_news_pk_seq"', 1, 'f');

--
-- TOC Entry ID 113 (OID 21599)
--
-- Name: system_services_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_services_pk_seq"', 1, 'f');

--
-- TOC Entry ID 115 (OID 21618)
--
-- Name: system_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_status_pk_seq"', 1, 'f');

--
-- TOC Entry ID 117 (OID 21665)
--
-- Name: themes_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"themes_pk_seq"', 1, 't');

--
-- TOC Entry ID 119 (OID 21742)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_cat_pk_seq"', 305, 't');

--
-- TOC Entry ID 121 (OID 21806)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_group_link_pk_seq"', 1, 'f');

--
-- TOC Entry ID 123 (OID 21847)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_treesums_pk_seq"', 1, 'f');

--
-- TOC Entry ID 125 (OID 21866)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_bookmarks_pk_seq"', 1, 'f');

--
-- TOC Entry ID 127 (OID 21918)
--
-- Name: user_diary_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_diary_pk_seq"', 1, 'f');

--
-- TOC Entry ID 129 (OID 21974)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_diary_monitor_pk_seq"', 1, 'f');

--
-- TOC Entry ID 131 (OID 22011)
--
-- Name: user_group_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_group_pk_seq"', 1, 'f');

--
-- TOC Entry ID 133 (OID 22069)
--
-- Name: user_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_metric_pk_seq"', 1, 'f');

--
-- TOC Entry ID 135 (OID 22116)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_metric0_pk_seq"', 1, 'f');

--
-- TOC Entry ID 137 (OID 22211)
--
-- Name: users_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"users_pk_seq"', 1, 'f');

--
-- TOC Entry ID 139 (OID 22298)
--
-- Name: unix_uid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"unix_uid_seq"', 1, 'f');

--
-- TOC Entry ID 141 (OID 22317)
--
-- Name: forum_thread_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_thread_seq"', 1, 'f');

--
-- TOC Entry ID 143 (OID 22350)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE SET Owner:
--

-- SELECT setval ('"project_metric_wee_ranking1_seq"', 1, 'f');

--
-- TOC Entry ID 145 (OID 22369)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"prdb_dbs_dbid_seq"', 1, 'f');

--
-- TOC Entry ID 147 (OID 22481)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"prweb_vhost_vhostid_seq"', 1, 'f');

--
-- TOC Entry ID 149 (OID 22533)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_grou_group_artifac_seq"', 100, 't');

--
-- TOC Entry ID 151 (OID 22600)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_resolution_id_seq"', 1, 'f');

--
-- TOC Entry ID 153 (OID 22649)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_perm_id_seq"', 1, 'f');

--
-- TOC Entry ID 155 (OID 22718)
--
-- Name: artifact_category_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_category_id_seq"', 100, 't');

--
-- TOC Entry ID 157 (OID 22770)
--
-- Name: artifact_group_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_group_id_seq"', 1, 'f');

--
-- TOC Entry ID 159 (OID 22820)
--
-- Name: artifact_status_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_status_id_seq"', 3, 't');

--
-- TOC Entry ID 161 (OID 22869)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_artifact_id_seq"', 1, 'f');

--
-- TOC Entry ID 163 (OID 22974)
--
-- Name: artifact_history_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_history_id_seq"', 1, 'f');

--
-- TOC Entry ID 165 (OID 23048)
--
-- Name: artifact_file_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_file_id_seq"', 1, 'f');

--
-- TOC Entry ID 167 (OID 23126)
--
-- Name: artifact_message_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_message_id_seq"', 1, 'f');

--
-- TOC Entry ID 169 (OID 23199)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_monitor_id_seq"', 1, 'f');

--
-- TOC Entry ID 171 (OID 23250)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_canned_response_id_seq"', 1, 'f');

--
-- TOC Entry ID 173 (OID 23330)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"massmail_queue_id_seq"', 1, 'f');

--
-- TOC Entry ID 175 (OID 26050)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE SET Owner:
--

-- SELECT setval ('"trove_treesum_trove_treesum_seq"', 1, 'f');

---- From now on, everything comes from Debian-SF

-- System projects were *not* created in 1970
update groups set register_time = int4(abstime(now())) ;


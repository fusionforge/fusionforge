--
-- Selected TOC Entries:
--
--
-- TOC Entry ID 442 (OID 45490493)
--
-- Name: "plpgsql_call_handler" () Type: FUNCTION Owner: tperdue
--

CREATE FUNCTION "plpgsql_call_handler" () RETURNS opaque AS '$libdir/plpgsql', 'plpgsql_call_handler' LANGUAGE 'C';

--
-- TOC Entry ID 443 (OID 45490494)
--
-- Name: plpgsql Type: PROCEDURAL LANGUAGE Owner: 
--

CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER "plpgsql_call_handler" LANCOMPILER '';

--
-- TOC Entry ID 2 (OID 45490495)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 176 (OID 45490497)
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
-- TOC Entry ID 4 (OID 45490503)
--
-- Name: db_images_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "db_images_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 177 (OID 45490505)
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
	"upload_date" integer,
	"version" integer,
	Constraint "db_images_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 6 (OID 45490511)
--
-- Name: doc_data_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_data_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 178 (OID 45490513)
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
-- TOC Entry ID 8 (OID 45490519)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 179 (OID 45490521)
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
-- TOC Entry ID 10 (OID 45490524)
--
-- Name: doc_states_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 180 (OID 45490526)
--
-- Name: doc_states Type: TABLE Owner: tperdue
--

CREATE TABLE "doc_states" (
	"stateid" integer DEFAULT nextval('doc_states_pk_seq'::text) NOT NULL,
	"name" character varying(255) DEFAULT '' NOT NULL,
	Constraint "doc_states_pkey" Primary Key ("stateid")
);

--
-- TOC Entry ID 12 (OID 45490529)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 181 (OID 45490531)
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
-- TOC Entry ID 14 (OID 45490534)
--
-- Name: forum_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 182 (OID 45490536)
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
-- TOC Entry ID 183 (OID 45490542)
--
-- Name: forum_agg_msg_count Type: TABLE Owner: tperdue
--

CREATE TABLE "forum_agg_msg_count" (
	"group_forum_id" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL,
	Constraint "forum_agg_msg_count_pkey" Primary Key ("group_forum_id")
);

--
-- TOC Entry ID 16 (OID 45490545)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 184 (OID 45490547)
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
-- TOC Entry ID 18 (OID 45490553)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 185 (OID 45490555)
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
-- TOC Entry ID 20 (OID 45490558)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 186 (OID 45490560)
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
-- TOC Entry ID 22 (OID 45490563)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 24 (OID 45490565)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_preferred_projec_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 26 (OID 45490567)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "foundry_projects_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 28 (OID 45490569)
--
-- Name: frs_file_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_file_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 187 (OID 45490571)
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
-- TOC Entry ID 30 (OID 45490577)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 188 (OID 45490579)
--
-- Name: frs_filetype Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_filetype" (
	"type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_filetype_pkey" Primary Key ("type_id")
);

--
-- TOC Entry ID 32 (OID 45490585)
--
-- Name: frs_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 189 (OID 45490587)
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
-- TOC Entry ID 34 (OID 45490593)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 190 (OID 45490595)
--
-- Name: frs_processor Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_processor" (
	"processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_processor_pkey" Primary Key ("processor_id")
);

--
-- TOC Entry ID 36 (OID 45490601)
--
-- Name: frs_release_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_release_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 191 (OID 45490603)
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
-- TOC Entry ID 38 (OID 45490609)
--
-- Name: frs_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 192 (OID 45490611)
--
-- Name: frs_status Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_status" (
	"status_id" integer DEFAULT nextval('frs_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "frs_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 40 (OID 45490617)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_cvs_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 42 (OID 45490619)
--
-- Name: group_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 193 (OID 45490621)
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
-- TOC Entry ID 44 (OID 45490627)
--
-- Name: group_type_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "group_type_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 194 (OID 45490629)
--
-- Name: group_type Type: TABLE Owner: tperdue
--

CREATE TABLE "group_type" (
	"type_id" integer DEFAULT nextval('group_type_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "group_type_pkey" Primary Key ("type_id")
);

--
-- TOC Entry ID 46 (OID 45490635)
--
-- Name: groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 195 (OID 45490637)
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

--
-- TOC Entry ID 48 (OID 45490649)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "mail_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 196 (OID 45490651)
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
-- TOC Entry ID 50 (OID 45490657)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "news_bytes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 197 (OID 45490659)
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
-- TOC Entry ID 52 (OID 45490665)
--
-- Name: people_job_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 198 (OID 45490667)
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
-- TOC Entry ID 54 (OID 45490673)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 199 (OID 45490675)
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
-- TOC Entry ID 56 (OID 45490681)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 200 (OID 45490683)
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
-- TOC Entry ID 58 (OID 45490686)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 201 (OID 45490688)
--
-- Name: people_job_status Type: TABLE Owner: tperdue
--

CREATE TABLE "people_job_status" (
	"status_id" integer DEFAULT nextval('people_job_status_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_job_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 60 (OID 45490694)
--
-- Name: people_skill_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 202 (OID 45490696)
--
-- Name: people_skill Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill" (
	"skill_id" integer DEFAULT nextval('people_skill_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_pkey" Primary Key ("skill_id")
);

--
-- TOC Entry ID 62 (OID 45490702)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 203 (OID 45490704)
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
-- TOC Entry ID 64 (OID 45490707)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 204 (OID 45490709)
--
-- Name: people_skill_level Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill_level" (
	"skill_level_id" integer DEFAULT nextval('people_skill_level_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_level_pkey" Primary Key ("skill_level_id")
);

--
-- TOC Entry ID 66 (OID 45490715)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 205 (OID 45490717)
--
-- Name: people_skill_year Type: TABLE Owner: tperdue
--

CREATE TABLE "people_skill_year" (
	"skill_year_id" integer DEFAULT nextval('people_skill_year_pk_seq'::text) NOT NULL,
	"name" text,
	Constraint "people_skill_year_pkey" Primary Key ("skill_year_id")
);

--
-- TOC Entry ID 68 (OID 45490723)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 206 (OID 45490725)
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
-- TOC Entry ID 70 (OID 45490728)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 207 (OID 45490730)
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
-- TOC Entry ID 72 (OID 45490733)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 208 (OID 45490735)
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
-- TOC Entry ID 74 (OID 45490741)
--
-- Name: project_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 209 (OID 45490743)
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
-- TOC Entry ID 76 (OID 45490749)
--
-- Name: project_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 210 (OID 45490751)
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
-- TOC Entry ID 78 (OID 45490754)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 211 (OID 45490756)
--
-- Name: project_metric_tmp1 Type: TABLE Owner: tperdue
--

CREATE TABLE "project_metric_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	Constraint "project_metric_tmp1_pkey" Primary Key ("ranking")
);

--
-- TOC Entry ID 80 (OID 45490759)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_weekly_tm_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 82 (OID 45490761)
--
-- Name: project_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 212 (OID 45490763)
--
-- Name: project_status Type: TABLE Owner: tperdue
--

CREATE TABLE "project_status" (
	"status_id" integer DEFAULT nextval('project_status_pk_seq'::text) NOT NULL,
	"status_name" text DEFAULT '' NOT NULL,
	Constraint "project_status_pkey" Primary Key ("status_id")
);

--
-- TOC Entry ID 84 (OID 45490769)
--
-- Name: project_task_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_task_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 213 (OID 45490771)
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
-- TOC Entry ID 86 (OID 45490777)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 214 (OID 45490779)
--
-- Name: project_weekly_metric Type: TABLE Owner: tperdue
--

CREATE TABLE "project_weekly_metric" (
	"ranking" integer DEFAULT nextval('project_weekly_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 215 (OID 45490781)
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
-- TOC Entry ID 88 (OID 45490784)
--
-- Name: snippet_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 216 (OID 45490786)
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
-- TOC Entry ID 90 (OID 45490792)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 217 (OID 45490794)
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
-- TOC Entry ID 92 (OID 45490800)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 218 (OID 45490802)
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
-- TOC Entry ID 94 (OID 45490805)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 219 (OID 45490807)
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
-- TOC Entry ID 96 (OID 45490813)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "snippet_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 220 (OID 45490815)
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
-- TOC Entry ID 221 (OID 45490821)
--
-- Name: stats_agg_logo_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_logo_by_day" (
	"day" integer,
	"count" integer
);

--
-- TOC Entry ID 222 (OID 45490823)
--
-- Name: stats_agg_pages_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_agg_pages_by_day" (
	"day" integer DEFAULT '0' NOT NULL,
	"count" integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 98 (OID 45490829)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "supported_languages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 223 (OID 45490831)
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
-- TOC Entry ID 100 (OID 45490837)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 224 (OID 45490839)
--
-- Name: survey_question_types Type: TABLE Owner: tperdue
--

CREATE TABLE "survey_question_types" (
	"id" integer DEFAULT nextval('survey_question_types_pk_seq'::text) NOT NULL,
	"type" text DEFAULT '' NOT NULL,
	Constraint "survey_question_types_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 102 (OID 45490845)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 225 (OID 45490847)
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
-- TOC Entry ID 226 (OID 45490853)
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
-- TOC Entry ID 227 (OID 45490855)
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
-- TOC Entry ID 228 (OID 45490857)
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
-- TOC Entry ID 104 (OID 45490862)
--
-- Name: surveys_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "surveys_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 229 (OID 45490864)
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
-- TOC Entry ID 106 (OID 45490870)
--
-- Name: system_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 108 (OID 45490872)
--
-- Name: system_machines_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_machines_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 110 (OID 45490874)
--
-- Name: system_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 112 (OID 45490876)
--
-- Name: system_services_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_services_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 114 (OID 45490878)
--
-- Name: system_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "system_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 116 (OID 45490880)
--
-- Name: themes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 118 (OID 45490884)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_cat_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 230 (OID 45490886)
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
-- TOC Entry ID 120 (OID 45490892)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 231 (OID 45490894)
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
-- TOC Entry ID 122 (OID 45490897)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 124 (OID 45490899)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 232 (OID 45490901)
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
-- TOC Entry ID 126 (OID 45490907)
--
-- Name: user_diary_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 233 (OID 45490909)
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
-- TOC Entry ID 128 (OID 45490915)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 234 (OID 45490917)
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
-- TOC Entry ID 130 (OID 45490920)
--
-- Name: user_group_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 235 (OID 45490922)
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
	"artifact_flags" integer,
	Constraint "user_group_pkey" Primary Key ("user_group_id")
);

--
-- TOC Entry ID 132 (OID 45490925)
--
-- Name: user_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 236 (OID 45490927)
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
-- TOC Entry ID 134 (OID 45490930)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 237 (OID 45490932)
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
-- TOC Entry ID 238 (OID 45490935)
--
-- Name: user_preferences Type: TABLE Owner: tperdue
--

CREATE TABLE "user_preferences" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"preference_name" character varying(20),
	"dead1" character varying(20),
	"set_date" integer DEFAULT '0' NOT NULL,
	"preference_value" text
);

--
-- TOC Entry ID 239 (OID 45490940)
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
-- TOC Entry ID 136 (OID 45490942)
--
-- Name: users_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "users_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 240 (OID 45490944)
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

--
-- TOC Entry ID 138 (OID 45490950)
--
-- Name: unix_uid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 140 (OID 45490952)
--
-- Name: forum_thread_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 241 (OID 45490954)
--
-- Name: project_sums_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "project_sums_agg" (
	"group_id" integer DEFAULT 0 NOT NULL,
	"type" character(4),
	"count" integer DEFAULT 0 NOT NULL
);

--
-- TOC Entry ID 142 (OID 45490956)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "project_metric_wee_ranking1_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 144 (OID 45490958)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 242 (OID 45490960)
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
-- TOC Entry ID 243 (OID 45490966)
--
-- Name: prdb_states Type: TABLE Owner: tperdue
--

CREATE TABLE "prdb_states" (
	"stateid" integer NOT NULL,
	"statename" text
);

--
-- TOC Entry ID 244 (OID 45490971)
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
-- TOC Entry ID 146 (OID 45490977)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 245 (OID 45490979)
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
-- TOC Entry ID 148 (OID 45490985)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 246 (OID 45490987)
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
-- TOC Entry ID 150 (OID 45490993)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 247 (OID 45490995)
--
-- Name: artifact_resolution Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_resolution" (
	"id" integer DEFAULT nextval('"artifact_resolution_id_seq"'::text) NOT NULL,
	"resolution_name" text,
	Constraint "artifact_resolution_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 152 (OID 45491001)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 248 (OID 45491003)
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
-- TOC Entry ID 249 (OID 45491008)
--
-- Name: artifactperm_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifactperm_user_vw" as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id);

--
-- TOC Entry ID 250 (OID 45491011)
--
-- Name: artifactperm_artgrouplist_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifactperm_artgrouplist_vw" as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id);

--
-- TOC Entry ID 154 (OID 45491012)
--
-- Name: artifact_category_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 251 (OID 45491014)
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
-- TOC Entry ID 156 (OID 45491020)
--
-- Name: artifact_group_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 252 (OID 45491022)
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
-- TOC Entry ID 158 (OID 45491028)
--
-- Name: artifact_status_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 253 (OID 45491030)
--
-- Name: artifact_status Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_status" (
	"id" integer DEFAULT nextval('"artifact_status_id_seq"'::text) NOT NULL,
	"status_name" text NOT NULL,
	Constraint "artifact_status_pkey" Primary Key ("id")
);

--
-- TOC Entry ID 160 (OID 45491036)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 254 (OID 45491038)
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
-- TOC Entry ID 255 (OID 45491046)
--
-- Name: artifact_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_vw" as SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.category_id, artifact.artifact_group_id, artifact.resolution_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact_category.category_name, artifact_group.group_name, artifact_resolution.resolution_name FROM users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution WHERE ((((((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id)) AND (artifact.category_id = artifact_category.id)) AND (artifact.artifact_group_id = artifact_group.id)) AND (artifact.resolution_id = artifact_resolution.id));

--
-- TOC Entry ID 162 (OID 45491047)
--
-- Name: artifact_history_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 256 (OID 45491049)
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
-- TOC Entry ID 257 (OID 45491057)
--
-- Name: artifact_history_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_history_user_vw" as SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name FROM artifact_history ah, users WHERE (ah.mod_by = users.user_id);

--
-- TOC Entry ID 164 (OID 45491058)
--
-- Name: artifact_file_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 258 (OID 45491060)
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
-- TOC Entry ID 259 (OID 45491068)
--
-- Name: artifact_file_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_file_user_vw" as SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname FROM artifact_file af, users WHERE (af.submitted_by = users.user_id);

--
-- TOC Entry ID 166 (OID 45491069)
--
-- Name: artifact_message_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 260 (OID 45491071)
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
-- TOC Entry ID 261 (OID 45491079)
--
-- Name: artifact_message_user_vw Type: VIEW Owner: tperdue
--

CREATE VIEW "artifact_message_user_vw" as SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname FROM artifact_message am, users WHERE (am.submitted_by = users.user_id);

--
-- TOC Entry ID 168 (OID 45491080)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 262 (OID 45491082)
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
-- TOC Entry ID 170 (OID 45491088)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 263 (OID 45491090)
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
-- TOC Entry ID 264 (OID 45491096)
--
-- Name: artifact_counts_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_counts_agg" (
	"group_artifact_id" integer NOT NULL,
	"count" integer NOT NULL,
	"open_count" integer
);

--
-- TOC Entry ID 265 (OID 45491098)
--
-- Name: stats_site_pages_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);

--
-- TOC Entry ID 444 (OID 45491100)
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
-- TOC Entry ID 445 (OID 45491101)
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
-- TOC Entry ID 446 (OID 45491102)
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
-- TOC Entry ID 172 (OID 45491103)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 266 (OID 45491105)
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
-- TOC Entry ID 267 (OID 45491111)
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
-- TOC Entry ID 268 (OID 45491113)
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
-- TOC Entry ID 269 (OID 45491115)
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
-- TOC Entry ID 270 (OID 45491117)
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
-- TOC Entry ID 271 (OID 45491119)
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
-- TOC Entry ID 272 (OID 45491121)
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
-- TOC Entry ID 273 (OID 45491123)
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
-- TOC Entry ID 274 (OID 45491125)
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
-- TOC Entry ID 275 (OID 45491127)
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
-- TOC Entry ID 276 (OID 45491129)
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
-- TOC Entry ID 277 (OID 45491131)
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
-- TOC Entry ID 278 (OID 45491136)
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
-- TOC Entry ID 279 (OID 45491141)
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
-- TOC Entry ID 280 (OID 45491146)
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
-- TOC Entry ID 281 (OID 45491152)
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
-- TOC Entry ID 282 (OID 45491154)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_filetotal_agg" (
	"file_id" integer,
	"downloads" integer
);

--
-- TOC Entry ID 283 (OID 45491156)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_grouptotal_agg" (
	"group_id" integer,
	"downloads" integer
);

--
-- TOC Entry ID 284 (OID 45491158)
--
-- Name: frs_dlstats_group_agg Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_group_agg" (
	"group_id" integer,
	"month" integer,
	"day" integer,
	"downloads" integer
);

--
-- TOC Entry ID 285 (OID 45491160)
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
-- TOC Entry ID 286 (OID 45491162)
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
-- TOC Entry ID 287 (OID 45491164)
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
-- TOC Entry ID 288 (OID 45491166)
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
-- TOC Entry ID 289 (OID 45491168)
--
-- Name: stats_site_pages_by_month Type: TABLE Owner: tperdue
--

CREATE TABLE "stats_site_pages_by_month" (
	"month" integer,
	"site_page_views" integer
);

--
-- TOC Entry ID 290 (OID 45491170)
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
-- TOC Entry ID 291 (OID 45491172)
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
-- TOC Entry ID 292 (OID 45491174)
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
-- TOC Entry ID 293 (OID 45491176)
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
-- TOC Entry ID 174 (OID 45491178)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE "trove_treesum_trove_treesum_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;

--
-- TOC Entry ID 294 (OID 45491180)
--
-- Name: trove_treesums Type: TABLE Owner: tperdue
--

CREATE TABLE "trove_treesums" (
	"trove_treesums_id" integer DEFAULT nextval('"trove_treesum_trove_treesum_seq"'::text) NOT NULL,
	"trove_cat_id" integer DEFAULT '0' NOT NULL,
	"limit_1" integer DEFAULT '0' NOT NULL,
	"subprojects" integer DEFAULT '0' NOT NULL,
	Constraint "trove_treesums_pkey" Primary Key ("trove_treesums_id")
);

--
-- TOC Entry ID 295 (OID 45491183)
--
-- Name: frs_dlstats_file Type: TABLE Owner: tperdue
--

CREATE TABLE "frs_dlstats_file" (
	"ip_address" text,
	"file_id" integer,
	"month" integer,
	"day" integer
);

--
-- Data for TOC Entry ID 447 (OID 45490497)
--
-- Name: canned_responses Type: TABLE DATA Owner: tperdue
--


COPY "canned_responses" FROM stdin;
\.
--
-- Data for TOC Entry ID 448 (OID 45490505)
--
-- Name: db_images Type: TABLE DATA Owner: tperdue
--


COPY "db_images" FROM stdin;
\.
--
-- Data for TOC Entry ID 449 (OID 45490513)
--
-- Name: doc_data Type: TABLE DATA Owner: tperdue
--


COPY "doc_data" FROM stdin;
\.
--
-- Data for TOC Entry ID 450 (OID 45490521)
--
-- Name: doc_groups Type: TABLE DATA Owner: tperdue
--


COPY "doc_groups" FROM stdin;
\.
--
-- Data for TOC Entry ID 451 (OID 45490526)
--
-- Name: doc_states Type: TABLE DATA Owner: tperdue
--


COPY "doc_states" FROM stdin;
1	active
2	deleted
3	pending
4	hidden
5	private
\.
--
-- Data for TOC Entry ID 452 (OID 45490531)
--
-- Name: filemodule_monitor Type: TABLE DATA Owner: tperdue
--


COPY "filemodule_monitor" FROM stdin;
\.
--
-- Data for TOC Entry ID 453 (OID 45490536)
--
-- Name: forum Type: TABLE DATA Owner: tperdue
--


COPY "forum" FROM stdin;
\.
--
-- Data for TOC Entry ID 454 (OID 45490542)
--
-- Name: forum_agg_msg_count Type: TABLE DATA Owner: tperdue
--


COPY "forum_agg_msg_count" FROM stdin;
\.
--
-- Data for TOC Entry ID 455 (OID 45490547)
--
-- Name: forum_group_list Type: TABLE DATA Owner: tperdue
--


COPY "forum_group_list" FROM stdin;
\.
--
-- Data for TOC Entry ID 456 (OID 45490555)
--
-- Name: forum_monitored_forums Type: TABLE DATA Owner: tperdue
--


COPY "forum_monitored_forums" FROM stdin;
\.
--
-- Data for TOC Entry ID 457 (OID 45490560)
--
-- Name: forum_saved_place Type: TABLE DATA Owner: tperdue
--


COPY "forum_saved_place" FROM stdin;
\.
--
-- Data for TOC Entry ID 458 (OID 45490571)
--
-- Name: frs_file Type: TABLE DATA Owner: tperdue
--


COPY "frs_file" FROM stdin;
\.
--
-- Data for TOC Entry ID 459 (OID 45490579)
--
-- Name: frs_filetype Type: TABLE DATA Owner: tperdue
--


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
--
-- Data for TOC Entry ID 460 (OID 45490587)
--
-- Name: frs_package Type: TABLE DATA Owner: tperdue
--


COPY "frs_package" FROM stdin;
\.
--
-- Data for TOC Entry ID 461 (OID 45490595)
--
-- Name: frs_processor Type: TABLE DATA Owner: tperdue
--


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
--
-- Data for TOC Entry ID 462 (OID 45490603)
--
-- Name: frs_release Type: TABLE DATA Owner: tperdue
--


COPY "frs_release" FROM stdin;
\.
--
-- Data for TOC Entry ID 463 (OID 45490611)
--
-- Name: frs_status Type: TABLE DATA Owner: tperdue
--


COPY "frs_status" FROM stdin;
1	Active
3	Hidden
\.
--
-- Data for TOC Entry ID 464 (OID 45490621)
--
-- Name: group_history Type: TABLE DATA Owner: tperdue
--


COPY "group_history" FROM stdin;
\.
--
-- Data for TOC Entry ID 465 (OID 45490629)
--
-- Name: group_type Type: TABLE DATA Owner: tperdue
--


COPY "group_type" FROM stdin;
1	Project
2	Foundry
\.
--
-- Data for TOC Entry ID 466 (OID 45490637)
--
-- Name: groups Type: TABLE DATA Owner: tperdue
--


COPY "groups" FROM stdin;
1	Master Group	\N	0	A	sourceforge	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N
2	Stats Group	\N	0	A	stats	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N
3	News Group	\N	0	A	news	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N
4	Peer Ratings Group	\N	0	A	peerrating	shell1	\N	\N	cvs1	\N	\N	\N	0	1	\N	1	1	1	1	1	1	1	1				1	1	0	0	0		0	1	1	\N	\N	\N
\.
--
-- Data for TOC Entry ID 467 (OID 45490651)
--
-- Name: mail_group_list Type: TABLE DATA Owner: tperdue
--


COPY "mail_group_list" FROM stdin;
\.
--
-- Data for TOC Entry ID 468 (OID 45490659)
--
-- Name: news_bytes Type: TABLE DATA Owner: tperdue
--


COPY "news_bytes" FROM stdin;
\.
--
-- Data for TOC Entry ID 469 (OID 45490667)
--
-- Name: people_job Type: TABLE DATA Owner: tperdue
--


COPY "people_job" FROM stdin;
\.
--
-- Data for TOC Entry ID 470 (OID 45490675)
--
-- Name: people_job_category Type: TABLE DATA Owner: tperdue
--


COPY "people_job_category" FROM stdin;
1	Developer	0
2	Project Manager	0
3	Unix Admin	0
4	Doc Writer	0
5	Tester	0
6	Support Manager	0
7	Graphic/Other Designer	0
\.
--
-- Data for TOC Entry ID 471 (OID 45490683)
--
-- Name: people_job_inventory Type: TABLE DATA Owner: tperdue
--


COPY "people_job_inventory" FROM stdin;
\.
--
-- Data for TOC Entry ID 472 (OID 45490688)
--
-- Name: people_job_status Type: TABLE DATA Owner: tperdue
--


COPY "people_job_status" FROM stdin;
1	Open
2	Filled
3	Deleted
\.
--
-- Data for TOC Entry ID 473 (OID 45490696)
--
-- Name: people_skill Type: TABLE DATA Owner: tperdue
--


COPY "people_skill" FROM stdin;
\.
--
-- Data for TOC Entry ID 474 (OID 45490704)
--
-- Name: people_skill_inventory Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_inventory" FROM stdin;
\.
--
-- Data for TOC Entry ID 475 (OID 45490709)
--
-- Name: people_skill_level Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_level" FROM stdin;
1	Want to Learn
2	Competent
3	Wizard
4	Wrote The Book
5	Wrote It
\.
--
-- Data for TOC Entry ID 476 (OID 45490717)
--
-- Name: people_skill_year Type: TABLE DATA Owner: tperdue
--


COPY "people_skill_year" FROM stdin;
1	< 6 Months
2	6 Mo - 2 yr
3	2 yr - 5 yr
4	5 yr - 10 yr
5	> 10 years
\.
--
-- Data for TOC Entry ID 477 (OID 45490725)
--
-- Name: project_assigned_to Type: TABLE DATA Owner: tperdue
--


COPY "project_assigned_to" FROM stdin;
\.
--
-- Data for TOC Entry ID 478 (OID 45490730)
--
-- Name: project_dependencies Type: TABLE DATA Owner: tperdue
--


COPY "project_dependencies" FROM stdin;
\.
--
-- Data for TOC Entry ID 479 (OID 45490735)
--
-- Name: project_group_list Type: TABLE DATA Owner: tperdue
--


COPY "project_group_list" FROM stdin;
1	1		0	\N
\.
--
-- Data for TOC Entry ID 480 (OID 45490743)
--
-- Name: project_history Type: TABLE DATA Owner: tperdue
--


COPY "project_history" FROM stdin;
\.
--
-- Data for TOC Entry ID 481 (OID 45490751)
--
-- Name: project_metric Type: TABLE DATA Owner: tperdue
--


COPY "project_metric" FROM stdin;
\.
--
-- Data for TOC Entry ID 482 (OID 45490756)
--
-- Name: project_metric_tmp1 Type: TABLE DATA Owner: tperdue
--


COPY "project_metric_tmp1" FROM stdin;
\.
--
-- Data for TOC Entry ID 483 (OID 45490763)
--
-- Name: project_status Type: TABLE DATA Owner: tperdue
--


COPY "project_status" FROM stdin;
1	Open
2	Closed
100	None
3	Deleted
\.
--
-- Data for TOC Entry ID 484 (OID 45490771)
--
-- Name: project_task Type: TABLE DATA Owner: tperdue
--


COPY "project_task" FROM stdin;
1	1			0	0	0	0	0	100	100
\.
--
-- Data for TOC Entry ID 485 (OID 45490779)
--
-- Name: project_weekly_metric Type: TABLE DATA Owner: tperdue
--


COPY "project_weekly_metric" FROM stdin;
\.
--
-- Data for TOC Entry ID 486 (OID 45490781)
--
-- Name: session Type: TABLE DATA Owner: tperdue
--


COPY "session" FROM stdin;
\.
--
-- Data for TOC Entry ID 487 (OID 45490786)
--
-- Name: snippet Type: TABLE DATA Owner: tperdue
--


COPY "snippet" FROM stdin;
\.
--
-- Data for TOC Entry ID 488 (OID 45490794)
--
-- Name: snippet_package Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package" FROM stdin;
\.
--
-- Data for TOC Entry ID 489 (OID 45490802)
--
-- Name: snippet_package_item Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package_item" FROM stdin;
\.
--
-- Data for TOC Entry ID 490 (OID 45490807)
--
-- Name: snippet_package_version Type: TABLE DATA Owner: tperdue
--


COPY "snippet_package_version" FROM stdin;
\.
--
-- Data for TOC Entry ID 491 (OID 45490815)
--
-- Name: snippet_version Type: TABLE DATA Owner: tperdue
--


COPY "snippet_version" FROM stdin;
\.
--
-- Data for TOC Entry ID 492 (OID 45490821)
--
-- Name: stats_agg_logo_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_logo_by_day" FROM stdin;
\.
--
-- Data for TOC Entry ID 493 (OID 45490823)
--
-- Name: stats_agg_pages_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_pages_by_day" FROM stdin;
\.
--
-- Data for TOC Entry ID 494 (OID 45490831)
--
-- Name: supported_languages Type: TABLE DATA Owner: tperdue
--


COPY "supported_languages" FROM stdin;
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
12	Dutch	Dutch.class	Dutch	nl
13	Esperanto	Esperanto.class	Esperanto	eo
14	Catalan	Catalan.class	Catalan	ca
15	Polish	Polish.class	Polish	pl
11	Trad.Chinese	Chinese.class	Chinese	zh
17	Russian	Russian.class	Russian	ru
18	Portuguese	Portuguese.class	Portuguese	pt
19	Greek	Greek.class	Greek	el
20	Bulgarian	Bulgarian.class	Bulgarian	bg
21	Indonesian	Indonesian.class	Indonesian	id
16	Pt. Brazillian	PortugueseBrazillian.class	PortugueseBrazillian	pt
22	Korean	Korean.class	Korean	ko
23	Smpl.Chinese	SimplifiedChinese.class	SimplifiedChinese	zn
\.
--
-- Data for TOC Entry ID 495 (OID 45490839)
--
-- Name: survey_question_types Type: TABLE DATA Owner: tperdue
--


COPY "survey_question_types" FROM stdin;
1	Radio Buttons 1-5
2	Text Area
3	Radio Buttons Yes/No
4	Comment Only
5	Text Field
100	None
\.
--
-- Data for TOC Entry ID 496 (OID 45490847)
--
-- Name: survey_questions Type: TABLE DATA Owner: tperdue
--


COPY "survey_questions" FROM stdin;
\.
--
-- Data for TOC Entry ID 497 (OID 45490853)
--
-- Name: survey_rating_aggregate Type: TABLE DATA Owner: tperdue
--


COPY "survey_rating_aggregate" FROM stdin;
\.
--
-- Data for TOC Entry ID 498 (OID 45490855)
--
-- Name: survey_rating_response Type: TABLE DATA Owner: tperdue
--


COPY "survey_rating_response" FROM stdin;
\.
--
-- Data for TOC Entry ID 499 (OID 45490857)
--
-- Name: survey_responses Type: TABLE DATA Owner: tperdue
--


COPY "survey_responses" FROM stdin;
\.
--
-- Data for TOC Entry ID 500 (OID 45490864)
--
-- Name: surveys Type: TABLE DATA Owner: tperdue
--


COPY "surveys" FROM stdin;
\.
--
-- Data for TOC Entry ID 501 (OID 45490886)
--
-- Name: trove_cat Type: TABLE DATA Owner: tperdue
--


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
--
-- Data for TOC Entry ID 502 (OID 45490894)
--
-- Name: trove_group_link Type: TABLE DATA Owner: tperdue
--


COPY "trove_group_link" FROM stdin;
\.
--
-- Data for TOC Entry ID 503 (OID 45490901)
--
-- Name: user_bookmarks Type: TABLE DATA Owner: tperdue
--


COPY "user_bookmarks" FROM stdin;
\.
--
-- Data for TOC Entry ID 504 (OID 45490909)
--
-- Name: user_diary Type: TABLE DATA Owner: tperdue
--


COPY "user_diary" FROM stdin;
\.
--
-- Data for TOC Entry ID 505 (OID 45490917)
--
-- Name: user_diary_monitor Type: TABLE DATA Owner: tperdue
--


COPY "user_diary_monitor" FROM stdin;
\.
--
-- Data for TOC Entry ID 506 (OID 45490922)
--
-- Name: user_group Type: TABLE DATA Owner: tperdue
--


COPY "user_group" FROM stdin;
\.
--
-- Data for TOC Entry ID 507 (OID 45490927)
--
-- Name: user_metric Type: TABLE DATA Owner: tperdue
--


COPY "user_metric" FROM stdin;
\.
--
-- Data for TOC Entry ID 508 (OID 45490932)
--
-- Name: user_metric0 Type: TABLE DATA Owner: tperdue
--


COPY "user_metric0" FROM stdin;
\.
--
-- Data for TOC Entry ID 509 (OID 45490935)
--
-- Name: user_preferences Type: TABLE DATA Owner: tperdue
--


COPY "user_preferences" FROM stdin;
\.
--
-- Data for TOC Entry ID 510 (OID 45490940)
--
-- Name: user_ratings Type: TABLE DATA Owner: tperdue
--


COPY "user_ratings" FROM stdin;
\.
--
-- Data for TOC Entry ID 511 (OID 45490944)
--
-- Name: users Type: TABLE DATA Owner: tperdue
--


COPY "users" FROM stdin;
100	None	noreply@sourceforge.net	*********34343		A	/bin/bash		N	0	shell1	0	\N	0	0	\N	\N	0		GMT	1	0	\N	\N
2	noreply				D	/bin/bash		N	0	shell1	0	\N	0	0	\N	\N	0		GMT	1	0	\N	\N
\.
--
-- Data for TOC Entry ID 512 (OID 45490954)
--
-- Name: project_sums_agg Type: TABLE DATA Owner: tperdue
--


COPY "project_sums_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 513 (OID 45490960)
--
-- Name: prdb_dbs Type: TABLE DATA Owner: tperdue
--


COPY "prdb_dbs" FROM stdin;
\.
--
-- Data for TOC Entry ID 514 (OID 45490966)
--
-- Name: prdb_states Type: TABLE DATA Owner: tperdue
--


COPY "prdb_states" FROM stdin;
\.
--
-- Data for TOC Entry ID 515 (OID 45490971)
--
-- Name: prdb_types Type: TABLE DATA Owner: tperdue
--


COPY "prdb_types" FROM stdin;
\.
--
-- Data for TOC Entry ID 516 (OID 45490979)
--
-- Name: prweb_vhost Type: TABLE DATA Owner: tperdue
--


COPY "prweb_vhost" FROM stdin;
\.
--
-- Data for TOC Entry ID 517 (OID 45490987)
--
-- Name: artifact_group_list Type: TABLE DATA Owner: tperdue
--


COPY "artifact_group_list" FROM stdin;
100	1	Default	Default Data - Dont Edit	3	0	0		2592000	0	\N	\N	0	\N
\.
--
-- Data for TOC Entry ID 518 (OID 45490995)
--
-- Name: artifact_resolution Type: TABLE DATA Owner: tperdue
--


COPY "artifact_resolution" FROM stdin;
100	None
\.
--
-- Data for TOC Entry ID 519 (OID 45491003)
--
-- Name: artifact_perm Type: TABLE DATA Owner: tperdue
--


COPY "artifact_perm" FROM stdin;
\.
--
-- Data for TOC Entry ID 520 (OID 45491014)
--
-- Name: artifact_category Type: TABLE DATA Owner: tperdue
--


COPY "artifact_category" FROM stdin;
100	100	None	100
\.
--
-- Data for TOC Entry ID 521 (OID 45491022)
--
-- Name: artifact_group Type: TABLE DATA Owner: tperdue
--


COPY "artifact_group" FROM stdin;
100	100	None
\.
--
-- Data for TOC Entry ID 522 (OID 45491030)
--
-- Name: artifact_status Type: TABLE DATA Owner: tperdue
--


COPY "artifact_status" FROM stdin;
1	Open
2	Closed
3	Deleted
\.
--
-- Data for TOC Entry ID 523 (OID 45491038)
--
-- Name: artifact Type: TABLE DATA Owner: tperdue
--


COPY "artifact" FROM stdin;
\.
--
-- Data for TOC Entry ID 524 (OID 45491049)
--
-- Name: artifact_history Type: TABLE DATA Owner: tperdue
--


COPY "artifact_history" FROM stdin;
\.
--
-- Data for TOC Entry ID 525 (OID 45491060)
--
-- Name: artifact_file Type: TABLE DATA Owner: tperdue
--


COPY "artifact_file" FROM stdin;
\.
--
-- Data for TOC Entry ID 526 (OID 45491071)
--
-- Name: artifact_message Type: TABLE DATA Owner: tperdue
--


COPY "artifact_message" FROM stdin;
\.
--
-- Data for TOC Entry ID 527 (OID 45491082)
--
-- Name: artifact_monitor Type: TABLE DATA Owner: tperdue
--


COPY "artifact_monitor" FROM stdin;
\.
--
-- Data for TOC Entry ID 528 (OID 45491090)
--
-- Name: artifact_canned_responses Type: TABLE DATA Owner: tperdue
--


COPY "artifact_canned_responses" FROM stdin;
\.
--
-- Data for TOC Entry ID 529 (OID 45491096)
--
-- Name: artifact_counts_agg Type: TABLE DATA Owner: tperdue
--


COPY "artifact_counts_agg" FROM stdin;
100	0	0
\.
--
-- Data for TOC Entry ID 530 (OID 45491098)
--
-- Name: stats_site_pages_by_day Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_pages_by_day" FROM stdin;
\.
--
-- Data for TOC Entry ID 531 (OID 45491105)
--
-- Name: massmail_queue Type: TABLE DATA Owner: tperdue
--


COPY "massmail_queue" FROM stdin;
\.
--
-- Data for TOC Entry ID 532 (OID 45491111)
--
-- Name: frs_dlstats_file_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_file_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 533 (OID 45491113)
--
-- Name: stats_agg_site_by_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_site_by_group" FROM stdin;
\.
--
-- Data for TOC Entry ID 534 (OID 45491115)
--
-- Name: stats_project_metric Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_metric" FROM stdin;
\.
--
-- Data for TOC Entry ID 535 (OID 45491117)
--
-- Name: stats_agg_logo_by_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_agg_logo_by_group" FROM stdin;
\.
--
-- Data for TOC Entry ID 536 (OID 45491119)
--
-- Name: stats_subd_pages Type: TABLE DATA Owner: tperdue
--


COPY "stats_subd_pages" FROM stdin;
\.
--
-- Data for TOC Entry ID 537 (OID 45491121)
--
-- Name: stats_cvs_user Type: TABLE DATA Owner: tperdue
--


COPY "stats_cvs_user" FROM stdin;
\.
--
-- Data for TOC Entry ID 538 (OID 45491123)
--
-- Name: stats_cvs_group Type: TABLE DATA Owner: tperdue
--


COPY "stats_cvs_group" FROM stdin;
\.
--
-- Data for TOC Entry ID 539 (OID 45491125)
--
-- Name: stats_project_developers Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_developers" FROM stdin;
\.
--
-- Data for TOC Entry ID 540 (OID 45491127)
--
-- Name: stats_project Type: TABLE DATA Owner: tperdue
--


COPY "stats_project" FROM stdin;
\.
--
-- Data for TOC Entry ID 541 (OID 45491129)
--
-- Name: stats_site Type: TABLE DATA Owner: tperdue
--


COPY "stats_site" FROM stdin;
\.
--
-- Data for TOC Entry ID 542 (OID 45491131)
--
-- Name: activity_log_old_old Type: TABLE DATA Owner: tperdue
--


COPY "activity_log_old_old" FROM stdin;
\.
--
-- Data for TOC Entry ID 543 (OID 45491136)
--
-- Name: activity_log_old Type: TABLE DATA Owner: tperdue
--


COPY "activity_log_old" FROM stdin;
\.
--
-- Data for TOC Entry ID 544 (OID 45491141)
--
-- Name: activity_log Type: TABLE DATA Owner: tperdue
--


COPY "activity_log" FROM stdin;
20010829	20	0	IE	5.5	Win	999136009	/index.php	0
20010829	20	0	IE	5.5	Win	999136070	/index.php	0
20010829	20	0	IE	5.5	Win	999136085	/account/register.php	0
20020115	18	0	OTHER	0	Other	1011142480	/index.php	0
20020115	18	0	OTHER	0	Other	1011142501	/index.php	0
20020115	19	0	MOZILLA	4.79	Win	1011143173	/index.php	0
20020115	19	0	MOZILLA	4.79	Win	1011143195	/account/login.php	0
20020115	19	0	MOZILLA	4.79	Win	1011143198	/softwaremap/trove_list.php	0
20020115	19	0	MOZILLA	4.79	Win	1011143202	/softwaremap/trove_list.php	0
20020115	19	0	MOZILLA	4.79	Win	1011143203	/softwaremap/trove_list.php	0
20020203	21	0	MOZILLA	4.79	Win	1012793437	/index.php	0
20020605	7	0	IE	6	Win	1023281865	/index.php	0
20020605	7	0	IE	6	Win	1023281873	/account/login.php	0
20020605	7	0	IE	6	Win	1023281884	/account/login.php	0
20020605	7	0	IE	6	Win	1023281891	/account/login.php	0
20020605	7	0	IE	6	Win	1023281997	/account/register.php	0
20020605	8	0	IE	6	Win	1023282078	/account/register.php	0
20020605	8	0	IE	6	Win	1023282109	/account/register.php	0
20020605	8	0	IE	6	Win	1023282187	/account/register.php	0
20020605	8	0	IE	6	Win	1023282216	/account/register.php	0
\.
--
-- Data for TOC Entry ID 545 (OID 45491146)
--
-- Name: cache_store Type: TABLE DATA Owner: tperdue
--


COPY "cache_store" FROM stdin;
_softwaremap_trove_list_php:a4f6c517dc969a7829873128473f53c2	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: Software Map</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: x-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.linux.com/'style='text-decoration:none'><font color='#ffffff'>Linux.Com</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.thinkgeek.com/'style='text-decoration:none'><font color='#ffffff'>Thinkgeek</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.questionexchange.com/'style='text-decoration:none'><font color='#ffffff'>Question Exchange</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<h2>Trove</h2>\n\n\t<HR NoShade><P><TABLE width=100% border="0" cellspacing="0" cellpadding="0">\n<TR valign="top"><TD><FONT face="arial, helvetica" size="3"><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>Topic</B><BR>\n &nbsp;  &nbsp; <a href="trove_list.php?form_cat=20"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Communications</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=66"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Database</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=55"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Desktop Environment</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=71"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Education</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=80"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Games/Entertainment</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=87"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Internet</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=99"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Multimedia</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=129"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Office/Business</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=234"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Other/Nonlisted Topic</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=154"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Printing</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=132"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Religion</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=97"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Scientific/Engineering</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=43"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Security</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=282"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Sociology</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=45"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Software Development</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=136"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; System</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=156"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Terminals</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp; <a href="trove_list.php?form_cat=63"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Text Editors</a> <I>(0 projects)</I><BR></TD><TD><FONT face="arial, helvetica" size="3">Browse by:<BR><A href="/softwaremap/trove_list.php?form_cat=6"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Development Status\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=225"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Environment\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=1"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Intended Audience\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=13"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; License\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=274"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Natural Language\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=199"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Operating System\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=160"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Programming Language\n</A><BR><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>Topic</B>\n</TD></TR></TABLE><HR noshade>\n<SPAN><CENTER><FONT size="-1"><B>0</B> projects in result set.</FONT></CENTER></SPAN><HR>\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/softwaremap/trove_list.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1011143199
_softwaremap_trove_list_php:3d7cf3cf81761a45156519636ba0de2b	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: Software Map</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: x-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.slashdot.com/'style='text-decoration:none'><font color='#ffffff'>Slashdot.Org</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.freshmeat.net/'style='text-decoration:none'><font color='#ffffff'>Freshmeat</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.openmagazine.net/'style='text-decoration:none'><font color='#ffffff'>Open Magazine</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<h2>Trove</h2>\n\n\t<HR NoShade><P><TABLE width=100% border="0" cellspacing="0" cellpadding="0">\n<TR valign="top"><TD><FONT face="arial, helvetica" size="3"><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <A href="/softwaremap/trove_list.php?form_cat=18">Topic</A><BR>\n &nbsp;  &nbsp; <IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>Communications</B><BR>\n &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=21"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; BBS</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=22"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Chat</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=27"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Conferencing</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=28"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Email</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=37"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; FIDO</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=36"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Fax</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=251"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; File Sharing</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=38"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Ham Radio</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=40"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Internet Phone</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=247"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Telephony</a> <I>(0 projects)</I><BR> &nbsp;  &nbsp;  &nbsp;  &nbsp; <a href="trove_list.php?form_cat=39"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Usenet News</a> <I>(0 projects)</I><BR></TD><TD><FONT face="arial, helvetica" size="3">Browse by:<BR><A href="/softwaremap/trove_list.php?form_cat=6"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Development Status\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=225"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Environment\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=1"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Intended Audience\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=13"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; License\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=274"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Natural Language\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=199"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Operating System\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=160"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Programming Language\n</A><BR><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>Topic</B>\n</TD></TR></TABLE><HR noshade>\n<SPAN><CENTER><FONT size="-1"><B>0</B> projects in result set.</FONT></CENTER></SPAN><HR>\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/softwaremap/trove_list.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1011143202
_softwaremap_trove_list_php:b4b640ad41c9e4b734f51c9a89c924bf	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: Software Map</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: x-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: x-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.openmagazine.net/'style='text-decoration:none'><font color='#ffffff'>Open Magazine</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.questionexchange.com/'style='text-decoration:none'><font color='#ffffff'>Question Exchange</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.themes.org/'style='text-decoration:none'><font color='#ffffff'>Themes.Org</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<h2>Trove</h2>\n\n\t<HR NoShade><P><TABLE width=100% border="0" cellspacing="0" cellpadding="0">\n<TR valign="top"><TD><FONT face="arial, helvetica" size="3"><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <A href="/softwaremap/trove_list.php?form_cat=18">Topic</A><BR>\n &nbsp;  &nbsp; <IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <A href="/softwaremap/trove_list.php?form_cat=20">Communications</A><BR>\n &nbsp;  &nbsp;  &nbsp;  &nbsp; <IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>BBS</B><BR>\n</TD><TD><FONT face="arial, helvetica" size="3">Browse by:<BR><A href="/softwaremap/trove_list.php?form_cat=6"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Development Status\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=225"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Environment\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=1"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Intended Audience\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=13"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; License\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=274"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Natural Language\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=199"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Operating System\n</A><BR><A href="/softwaremap/trove_list.php?form_cat=160"><IMG src="//images/ic/cfolder15.png" border=0 width=15 height=13>&nbsp; Programming Language\n</A><BR><IMG src="//images/ic/ofolder15.png" border=0 width=15 height=13>&nbsp; <B>Topic</B>\n</TD></TR></TABLE><HR noshade>\n<SPAN><CENTER><FONT size="-1"><B>0</B> projects in result set.</FONT></CENTER></SPAN><HR>\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/softwaremap/trove_list.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1011143203
_index_php:60ca8086ca5d7c334ebbf81dd64bdd99	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: Welcome</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:x-small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: xx-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: x-small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.thinkgeek.com/'style='text-decoration:none'><font color='#ffffff'>Thinkgeek</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.themes.org/'style='text-decoration:none'><font color='#ffffff'>Themes.Org</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.newsforge.com/'style='text-decoration:none'><font color='#ffffff'>NewsForge</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<!-- whole page table -->\n<TABLE width=100% cellpadding=5 cellspacing=0 border=0>\n<TR><TD width="65%" VALIGN="TOP">\n\n\t<hr width="100%" size="1" noshade>\n\t<span class="slogan">\n\t<div align="center">\n\tBreaking Down the Barriers to Open Source Development\t</div>\n\t</span>\n        <hr width="100%" size="1" noshade>\n\t&nbsp;<br>\n<P>\nSourceForge is a <B>free service to <A href="http://www.opensource.org">Open Source</A> developers</B> offering easy access to the best in CVS, mailing lists, bug tracking, message boards/forums, task management, site hosting, permanent file archival, full backups, and total web-based administration.  <A href="/docman/display_doc.php?docid=753&group_id=1"><font size="-1">[ more ]</font></A><A href="/docman/display_doc.php?docid=756&group_id=1"><font size="-1">[ FAQ ]</font></A><BR>&nbsp;<P><B>Site Feedback and Participation</B><P>In order to get the most out of SourceForge, you'll need to <A href="/account/register.php">register as a site user</A>. This will allow you to participate fully in all we have to offer. You may of course browse the site without registering, but will not have access to participate fully. <P>&nbsp;<BR><B>Set Up Your Own Project</B><P><A href="/account/register.php">Register as a site user</A>, then <A HREF="/account/login.php">Login</A> and finally, <A HREF="/register/">Register Your Project.</A> Thanks... and enjoy the site.<P>\n<br><b>SourceForge Development Foundries</b><br><br>\n<table bgcolor="White" border="0" cellpadding="0" cellspacing="0" valign="top" width="100%">\n<tr>\n\t<td>Essentials:</td>\n</tr>\n<tr>\n\t<td><font size="-1"><a href="/foundry/linuxkernel/">Linux Kernel</a>, <a href="/foundry/linuxdrivers/"><b>Linux Drivers</b></a></font></td>\n</tr>\n<tr>\n\t<td>Hardware:</td>\n\t<td>Programming:</td>\n</tr>\n<tr>\n\t<td><font size="-1"><a href="/foundry/printing/">Printing</a>, <a href="/foundry/storage/">Storage</a></font></td>\n\t<td><font size="-1"><a href="/foundry/java/">Java</a>, <a href="/foundry/perl-foundry/">Perl</a>, <a href="/foundry/php-foundry/">PHP</a>, <a href="/foundry/python-foundry/">Python</a>, <a href="/foundry/tcl-foundry/">Tcl/Tk</a>, <a href="/foundry/gnome-foundry/">GNOME</a></font></td>\n</tr>\n<tr>\n\t<td>International:</td>\n\t<td>Services:</td>\n</tr>\n<tr>\n\t<td><font size="-1"><a href="/foundry/french/">French</a>, <a href="/foundry/spanish/">Espanol</a>, <a href="/foundry/japanese/">Japanese</a></font></td>\n\t<td><font size="-1"><a href="/foundry/databases/">Database</a>, <a href="/foundry/web/">Web</a></font></td>\n</tr>\n<tr>\n\t<td>Graphics:</td>\n\t<td>Fun:</td>\n</tr>\n<tr>\n\t<td><font size="-1"><a href="/foundry/vectorgraphics/">Vector Graphics</a>, <a href="/foundry/3d/">3D</a></font></td>\n\t<td><font size="-1"><a href="/foundry/games/">Games</a></font></td>\n</tr>\n<tr>\n\t\t<td>&nbsp;</td><td align="right"><font size="-1"><a href="about_foundries.php">[ More ]</a></font></td>\n</tr>\n</table>\n<br>\n\n<TABLE cellspacing="1" cellpadding="5" width="100%" border="0" bgcolor="#EAECEF">\n\t\t\t<TR BGCOLOR="#D1D5D7" BACKGROUND="//themes/forged/images/steel3.jpg" align="center">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>Latest News</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2><H3>No News Items Found</H3></ul><HR width="100%" size="1" noshade>\n<div align="center"><a href="http://server1/news/">[News archive]</a></div>\n\t\t</TD>\n\t\t\t</TR>\n\t</TABLE>\n\n</TD>\n\n<TD width="35%" VALIGN="TOP"><TABLE cellspacing="1" cellpadding="5" width="100%" border="0" bgcolor="#EAECEF">\n\t\t\t<TR BGCOLOR="#D1D5D7" BACKGROUND="//themes/forged/images/steel3.jpg" align="center">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>SourceForge Statistics</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2>Hosted Projects: <B>4</B><BR>Registered Users: <B>1</B>\n\t\t\t\t</TD>\n\t\t\t</TR>\n\t\t\t<TR BGCOLOR="#EAECEF" align="center" background="//themes/forged/images/steel3.jpg">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>SourceForge OnSite</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2>Bring SourceForge to your company - support 100 to 10,000+ developers with <a href='http://www.valinux.com/services/sfos.html'>SourceForge OnSite</a>.<br>\n\t\t\t\t</TD>\n\t\t\t</TR>\n\t\t\t<TR BGCOLOR="#EAECEF" align="center" background="//themes/forged/images/steel3.jpg">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>Top Project Downloads</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2><CENTER><A href="/top/">[ More ]</A></CENTER>\n\t\t\t\t</TD>\n\t\t\t</TR>\n\t\t\t<TR BGCOLOR="#EAECEF" align="center" background="//themes/forged/images/steel3.jpg">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>Highest Ranked Users</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2>None Found. \n\t\t\t\t</TD>\n\t\t\t</TR>\n\t\t\t<TR BGCOLOR="#EAECEF" align="center" background="//themes/forged/images/steel3.jpg">\n\t\t\t\t<TD colspan=2><SPAN class=titlebar>Most Active This Week</SPAN></TD>\n\t\t\t</TR><TR align=left bgcolor="#EAECEF">\n\t\t\t\t<TD colspan=2>\n\t\t</TD>\n\t\t\t</TR>\n\t</TABLE>\n\n</TD></TR></TABLE>\n\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/index.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1023281867
_account_register_php:a0a479179537fccd04a443730556126b	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: SourceForge: Register</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:x-small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: xx-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: x-small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.newsforge.com/'style='text-decoration:none'><font color='#ffffff'>NewsForge</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.questionexchange.com/'style='text-decoration:none'><font color='#ffffff'>Question Exchange</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.linux.com/'style='text-decoration:none'><font color='#ffffff'>Linux.Com</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<h2>SourceForge New Account Registration</h2>\n\n\n<form action="https://server1/account/register.php" method="post">\n<p>\nLogin Name (do not use uppercase letters) *:<br>\n<input type="text" name="unix_name" value="">\n<p>\nPassword (min. 6 chars) *:<br>\n<input type="password" name="password1">\n<p>\nPassword (repeat) *:<br>\n<input type="password" name="password2">\n<P>\nFull/Real Name *:<BR>\n<INPUT size=30 type="text" name="realname" value="">\n<P>\nLanguage Choice:<BR>\n\n\t\t<SELECT NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT><P>\nTimezone:<BR>\n\n\t\t<SELECT NAME="timezone">\n\t\t\t\t<OPTION VALUE="US/Alaska">US/Alaska</OPTION>\n\t\t\t\t<OPTION VALUE="US/Aleutian">US/Aleutian</OPTION>\n\t\t\t\t<OPTION VALUE="US/Arizona">US/Arizona</OPTION>\n\t\t\t\t<OPTION VALUE="US/Central">US/Central</OPTION>\n\t\t\t\t<OPTION VALUE="US/Eastern">US/Eastern</OPTION>\n\t\t\t\t<OPTION VALUE="US/East-Indiana">US/East-Indiana</OPTION>\n\t\t\t\t<OPTION VALUE="US/Hawaii">US/Hawaii</OPTION>\n\t\t\t\t<OPTION VALUE="US/Indiana-Starke">US/Indiana-Starke</OPTION>\n\t\t\t\t<OPTION VALUE="US/Michigan">US/Michigan</OPTION>\n\t\t\t\t<OPTION VALUE="US/Mountain">US/Mountain</OPTION>\n\t\t\t\t<OPTION VALUE="US/Pacific">US/Pacific</OPTION>\n\t\t\t\t<OPTION VALUE="US/Samoa">US/Samoa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Abidjan">Africa/Abidjan</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Accra">Africa/Accra</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Addis_Ababa">Africa/Addis_Ababa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Algiers">Africa/Algiers</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Asmera">Africa/Asmera</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bamako">Africa/Bamako</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bangui">Africa/Bangui</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Banjul">Africa/Banjul</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bissau">Africa/Bissau</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Blantyre">Africa/Blantyre</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Brazzaville">Africa/Brazzaville</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bujumbura">Africa/Bujumbura</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Cairo">Africa/Cairo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Casablanca">Africa/Casablanca</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ceuta">Africa/Ceuta</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Conakry">Africa/Conakry</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Dakar">Africa/Dakar</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Dar_es_Salaam">Africa/Dar_es_Salaam</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Djibouti">Africa/Djibouti</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Douala">Africa/Douala</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/El_Aaiun">Africa/El_Aaiun</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Freetown">Africa/Freetown</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Gaborone">Africa/Gaborone</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Harare">Africa/Harare</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Johannesburg">Africa/Johannesburg</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kampala">Africa/Kampala</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Khartoum">Africa/Khartoum</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kigali">Africa/Kigali</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kinshasa">Africa/Kinshasa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lagos">Africa/Lagos</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Libreville">Africa/Libreville</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lome">Africa/Lome</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Luanda">Africa/Luanda</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lubumbashi">Africa/Lubumbashi</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lusaka">Africa/Lusaka</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Malabo">Africa/Malabo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Maputo">Africa/Maputo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Maseru">Africa/Maseru</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Mbabane">Africa/Mbabane</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Mogadishu">Africa/Mogadishu</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Monrovia">Africa/Monrovia</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Nairobi">Africa/Nairobi</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ndjamena">Africa/Ndjamena</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Niamey">Africa/Niamey</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Nouakchott">Africa/Nouakchott</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ouagadougou">Africa/Ouagadougou</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Porto-Novo">Africa/Porto-Novo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Sao_Tome">Africa/Sao_Tome</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Timbuktu">Africa/Timbuktu</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Tripoli">Africa/Tripoli</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Tunis">Africa/Tunis</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Windhoek">Africa/Windhoek</OPTION>\n\t\t\t\t<OPTION VALUE="America/Adak">America/Adak</OPTION>\n\t\t\t\t<OPTION VALUE="America/Anchorage">America/Anchorage</OPTION>\n\t\t\t\t<OPTION VALUE="America/Anguilla">America/Anguilla</OPTION>\n\t\t\t\t<OPTION VALUE="America/Antigua">America/Antigua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Araguaina">America/Araguaina</OPTION>\n\t\t\t\t<OPTION VALUE="America/Aruba">America/Aruba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Asuncion">America/Asuncion</OPTION>\n\t\t\t\t<OPTION VALUE="America/Atka">America/Atka</OPTION>\n\t\t\t\t<OPTION VALUE="America/Barbados">America/Barbados</OPTION>\n\t\t\t\t<OPTION VALUE="America/Belem">America/Belem</OPTION>\n\t\t\t\t<OPTION VALUE="America/Belize">America/Belize</OPTION>\n\t\t\t\t<OPTION VALUE="America/Boa_Vista">America/Boa_Vista</OPTION>\n\t\t\t\t<OPTION VALUE="America/Bogota">America/Bogota</OPTION>\n\t\t\t\t<OPTION VALUE="America/Boise">America/Boise</OPTION>\n\t\t\t\t<OPTION VALUE="America/Buenos_Aires">America/Buenos_Aires</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cambridge_Bay">America/Cambridge_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cancun">America/Cancun</OPTION>\n\t\t\t\t<OPTION VALUE="America/Caracas">America/Caracas</OPTION>\n\t\t\t\t<OPTION VALUE="America/Catamarca">America/Catamarca</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cayenne">America/Cayenne</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cayman">America/Cayman</OPTION>\n\t\t\t\t<OPTION VALUE="America/Chicago">America/Chicago</OPTION>\n\t\t\t\t<OPTION VALUE="America/Chihuahua">America/Chihuahua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cordoba">America/Cordoba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Costa_Rica">America/Costa_Rica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cuiaba">America/Cuiaba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Curacao">America/Curacao</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dawson">America/Dawson</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dawson_Creek">America/Dawson_Creek</OPTION>\n\t\t\t\t<OPTION VALUE="America/Denver">America/Denver</OPTION>\n\t\t\t\t<OPTION VALUE="America/Detroit">America/Detroit</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dominica">America/Dominica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Edmonton">America/Edmonton</OPTION>\n\t\t\t\t<OPTION VALUE="America/El_Salvador">America/El_Salvador</OPTION>\n\t\t\t\t<OPTION VALUE="America/Ensenada">America/Ensenada</OPTION>\n\t\t\t\t<OPTION VALUE="America/Fortaleza">America/Fortaleza</OPTION>\n\t\t\t\t<OPTION VALUE="America/Fort_Wayne">America/Fort_Wayne</OPTION>\n\t\t\t\t<OPTION VALUE="America/Glace_Bay">America/Glace_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Godthab">America/Godthab</OPTION>\n\t\t\t\t<OPTION VALUE="America/Goose_Bay">America/Goose_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Grand_Turk">America/Grand_Turk</OPTION>\n\t\t\t\t<OPTION VALUE="America/Grenada">America/Grenada</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guadeloupe">America/Guadeloupe</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guatemala">America/Guatemala</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guayaquil">America/Guayaquil</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guyana">America/Guyana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Halifax">America/Halifax</OPTION>\n\t\t\t\t<OPTION VALUE="America/Havana">America/Havana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Hermosillo">America/Hermosillo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Indianapolis">America/Indiana/Indianapolis</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Knox">America/Indiana/Knox</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Marengo">America/Indiana/Marengo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Vevay">America/Indiana/Vevay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indianapolis">America/Indianapolis</OPTION>\n\t\t\t\t<OPTION VALUE="America/Inuvik">America/Inuvik</OPTION>\n\t\t\t\t<OPTION VALUE="America/Iqaluit">America/Iqaluit</OPTION>\n\t\t\t\t<OPTION VALUE="America/Jamaica">America/Jamaica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Jujuy">America/Jujuy</OPTION>\n\t\t\t\t<OPTION VALUE="America/Juneau">America/Juneau</OPTION>\n\t\t\t\t<OPTION VALUE="America/Knox_IN">America/Knox_IN</OPTION>\n\t\t\t\t<OPTION VALUE="America/La_Paz">America/La_Paz</OPTION>\n\t\t\t\t<OPTION VALUE="America/Lima">America/Lima</OPTION>\n\t\t\t\t<OPTION VALUE="America/Los_Angeles">America/Los_Angeles</OPTION>\n\t\t\t\t<OPTION VALUE="America/Louisville">America/Louisville</OPTION>\n\t\t\t\t<OPTION VALUE="America/Maceio">America/Maceio</OPTION>\n\t\t\t\t<OPTION VALUE="America/Managua">America/Managua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Manaus">America/Manaus</OPTION>\n\t\t\t\t<OPTION VALUE="America/Martinique">America/Martinique</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mazatlan">America/Mazatlan</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mendoza">America/Mendoza</OPTION>\n\t\t\t\t<OPTION VALUE="America/Menominee">America/Menominee</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mexico_City">America/Mexico_City</OPTION>\n\t\t\t\t<OPTION VALUE="America/Miquelon">America/Miquelon</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montevideo">America/Montevideo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montreal">America/Montreal</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montserrat">America/Montserrat</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nassau">America/Nassau</OPTION>\n\t\t\t\t<OPTION VALUE="America/New_York">America/New_York</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nipigon">America/Nipigon</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nome">America/Nome</OPTION>\n\t\t\t\t<OPTION VALUE="America/Noronha">America/Noronha</OPTION>\n\t\t\t\t<OPTION VALUE="America/Panama">America/Panama</OPTION>\n\t\t\t\t<OPTION VALUE="America/Pangnirtung">America/Pangnirtung</OPTION>\n\t\t\t\t<OPTION VALUE="America/Paramaribo">America/Paramaribo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Phoenix">America/Phoenix</OPTION>\n\t\t\t\t<OPTION VALUE="America/Port-au-Prince">America/Port-au-Prince</OPTION>\n\t\t\t\t<OPTION VALUE="America/Porto_Acre">America/Porto_Acre</OPTION>\n\t\t\t\t<OPTION VALUE="America/Port_of_Spain">America/Port_of_Spain</OPTION>\n\t\t\t\t<OPTION VALUE="America/Porto_Velho">America/Porto_Velho</OPTION>\n\t\t\t\t<OPTION VALUE="America/Puerto_Rico">America/Puerto_Rico</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rainy_River">America/Rainy_River</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rankin_Inlet">America/Rankin_Inlet</OPTION>\n\t\t\t\t<OPTION VALUE="America/Regina">America/Regina</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rosario">America/Rosario</OPTION>\n\t\t\t\t<OPTION VALUE="America/Santiago">America/Santiago</OPTION>\n\t\t\t\t<OPTION VALUE="America/Santo_Domingo">America/Santo_Domingo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Sao_Paulo">America/Sao_Paulo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Scoresbysund">America/Scoresbysund</OPTION>\n\t\t\t\t<OPTION VALUE="America/Shiprock">America/Shiprock</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Johns">America/St_Johns</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Kitts">America/St_Kitts</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Lucia">America/St_Lucia</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Thomas">America/St_Thomas</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Vincent">America/St_Vincent</OPTION>\n\t\t\t\t<OPTION VALUE="America/Swift_Current">America/Swift_Current</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tegucigalpa">America/Tegucigalpa</OPTION>\n\t\t\t\t<OPTION VALUE="America/Thule">America/Thule</OPTION>\n\t\t\t\t<OPTION VALUE="America/Thunder_Bay">America/Thunder_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tijuana">America/Tijuana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tortola">America/Tortola</OPTION>\n\t\t\t\t<OPTION VALUE="America/Vancouver">America/Vancouver</OPTION>\n\t\t\t\t<OPTION VALUE="America/Virgin">America/Virgin</OPTION>\n\t\t\t\t<OPTION VALUE="America/Whitehorse">America/Whitehorse</OPTION>\n\t\t\t\t<OPTION VALUE="America/Winnipeg">America/Winnipeg</OPTION>\n\t\t\t\t<OPTION VALUE="America/Yakutat">America/Yakutat</OPTION>\n\t\t\t\t<OPTION VALUE="America/Yellowknife">America/Yellowknife</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Casey">Antarctica/Casey</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Davis">Antarctica/Davis</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/DumontDUrville">Antarctica/DumontDUrville</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Mawson">Antarctica/Mawson</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/McMurdo">Antarctica/McMurdo</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Palmer">Antarctica/Palmer</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/South_Pole">Antarctica/South_Pole</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Syowa">Antarctica/Syowa</OPTION>\n\t\t\t\t<OPTION VALUE="Arctic/Longyearbyen">Arctic/Longyearbyen</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aden">Asia/Aden</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Almaty">Asia/Almaty</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Amman">Asia/Amman</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Anadyr">Asia/Anadyr</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aqtau">Asia/Aqtau</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aqtobe">Asia/Aqtobe</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ashkhabad">Asia/Ashkhabad</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Baghdad">Asia/Baghdad</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bahrain">Asia/Bahrain</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Baku">Asia/Baku</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bangkok">Asia/Bangkok</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Beirut">Asia/Beirut</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bishkek">Asia/Bishkek</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Brunei">Asia/Brunei</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Calcutta">Asia/Calcutta</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Chungking">Asia/Chungking</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Colombo">Asia/Colombo</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dacca">Asia/Dacca</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Damascus">Asia/Damascus</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dili">Asia/Dili</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dubai">Asia/Dubai</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dushanbe">Asia/Dushanbe</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Gaza">Asia/Gaza</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Harbin">Asia/Harbin</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Hong_Kong">Asia/Hong_Kong</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Hovd">Asia/Hovd</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Irkutsk">Asia/Irkutsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Istanbul">Asia/Istanbul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jakarta">Asia/Jakarta</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jayapura">Asia/Jayapura</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jerusalem">Asia/Jerusalem</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kabul">Asia/Kabul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kamchatka">Asia/Kamchatka</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Karachi">Asia/Karachi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kashgar">Asia/Kashgar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Katmandu">Asia/Katmandu</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Krasnoyarsk">Asia/Krasnoyarsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuala_Lumpur">Asia/Kuala_Lumpur</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuching">Asia/Kuching</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuwait">Asia/Kuwait</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Macao">Asia/Macao</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Magadan">Asia/Magadan</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Manila">Asia/Manila</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Muscat">Asia/Muscat</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Nicosia">Asia/Nicosia</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Novosibirsk">Asia/Novosibirsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Omsk">Asia/Omsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Phnom_Penh">Asia/Phnom_Penh</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Pyongyang">Asia/Pyongyang</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Qatar">Asia/Qatar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Rangoon">Asia/Rangoon</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh">Asia/Riyadh</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh87">Asia/Riyadh87</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh88">Asia/Riyadh88</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh89">Asia/Riyadh89</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Saigon">Asia/Saigon</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Samarkand">Asia/Samarkand</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Seoul">Asia/Seoul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Shanghai">Asia/Shanghai</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Singapore">Asia/Singapore</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Taipei">Asia/Taipei</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tashkent">Asia/Tashkent</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tbilisi">Asia/Tbilisi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tehran">Asia/Tehran</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tel_Aviv">Asia/Tel_Aviv</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Thimbu">Asia/Thimbu</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tokyo">Asia/Tokyo</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ujung_Pandang">Asia/Ujung_Pandang</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ulaanbaatar">Asia/Ulaanbaatar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ulan_Bator">Asia/Ulan_Bator</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Urumqi">Asia/Urumqi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Vientiane">Asia/Vientiane</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Vladivostok">Asia/Vladivostok</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yakutsk">Asia/Yakutsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yekaterinburg">Asia/Yekaterinburg</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yerevan">Asia/Yerevan</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Azores">Atlantic/Azores</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Bermuda">Atlantic/Bermuda</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Canary">Atlantic/Canary</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Cape_Verde">Atlantic/Cape_Verde</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Faeroe">Atlantic/Faeroe</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Jan_Mayen">Atlantic/Jan_Mayen</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Madeira">Atlantic/Madeira</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Reykjavik">Atlantic/Reykjavik</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/South_Georgia">Atlantic/South_Georgia</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Stanley">Atlantic/Stanley</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/St_Helena">Atlantic/St_Helena</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/ACT">Australia/ACT</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Adelaide">Australia/Adelaide</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Brisbane">Australia/Brisbane</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Broken_Hill">Australia/Broken_Hill</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Canberra">Australia/Canberra</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Darwin">Australia/Darwin</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Hobart">Australia/Hobart</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/LHI">Australia/LHI</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Lindeman">Australia/Lindeman</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Lord_Howe">Australia/Lord_Howe</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Melbourne">Australia/Melbourne</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/North">Australia/North</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/NSW">Australia/NSW</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Perth">Australia/Perth</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Queensland">Australia/Queensland</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/South">Australia/South</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Sydney">Australia/Sydney</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Tasmania">Australia/Tasmania</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Victoria">Australia/Victoria</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/West">Australia/West</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Yancowinna">Australia/Yancowinna</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/Acre">Brazil/Acre</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/DeNoronha">Brazil/DeNoronha</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/East">Brazil/East</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/West">Brazil/West</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Atlantic">Canada/Atlantic</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Central">Canada/Central</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Eastern">Canada/Eastern</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/East-Saskatchewan">Canada/East-Saskatchewan</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Mountain">Canada/Mountain</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Newfoundland">Canada/Newfoundland</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Pacific">Canada/Pacific</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Saskatchewan">Canada/Saskatchewan</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Yukon">Canada/Yukon</OPTION>\n\t\t\t\t<OPTION VALUE="CET">CET</OPTION>\n\t\t\t\t<OPTION VALUE="Chile/Continental">Chile/Continental</OPTION>\n\t\t\t\t<OPTION VALUE="Chile/EasterIsland">Chile/EasterIsland</OPTION>\n\t\t\t\t<OPTION VALUE="China/Beijing">China/Beijing</OPTION>\n\t\t\t\t<OPTION VALUE="China/Shanghai">China/Shanghai</OPTION>\n\t\t\t\t<OPTION VALUE="CST6CDT">CST6CDT</OPTION>\n\t\t\t\t<OPTION VALUE="Cuba">Cuba</OPTION>\n\t\t\t\t<OPTION VALUE="EET">EET</OPTION>\n\t\t\t\t<OPTION VALUE="Egypt">Egypt</OPTION>\n\t\t\t\t<OPTION VALUE="Eire">Eire</OPTION>\n\t\t\t\t<OPTION VALUE="EST">EST</OPTION>\n\t\t\t\t<OPTION VALUE="EST5EDT">EST5EDT</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Amsterdam">Europe/Amsterdam</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Andorra">Europe/Andorra</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Athens">Europe/Athens</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Belfast">Europe/Belfast</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Belgrade">Europe/Belgrade</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Berlin">Europe/Berlin</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Bratislava">Europe/Bratislava</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Brussels">Europe/Brussels</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Bucharest">Europe/Bucharest</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Budapest">Europe/Budapest</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Chisinau">Europe/Chisinau</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Copenhagen">Europe/Copenhagen</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Dublin">Europe/Dublin</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Gibraltar">Europe/Gibraltar</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Helsinki">Europe/Helsinki</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Istanbul">Europe/Istanbul</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Kaliningrad">Europe/Kaliningrad</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Kiev">Europe/Kiev</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Lisbon">Europe/Lisbon</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Ljubljana">Europe/Ljubljana</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/London">Europe/London</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Luxembourg">Europe/Luxembourg</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Madrid">Europe/Madrid</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Malta">Europe/Malta</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Minsk">Europe/Minsk</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Monaco">Europe/Monaco</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Moscow">Europe/Moscow</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Oslo">Europe/Oslo</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Paris">Europe/Paris</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Prague">Europe/Prague</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Riga">Europe/Riga</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Rome">Europe/Rome</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Samara">Europe/Samara</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/San_Marino">Europe/San_Marino</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Sarajevo">Europe/Sarajevo</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Simferopol">Europe/Simferopol</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Skopje">Europe/Skopje</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Sofia">Europe/Sofia</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Stockholm">Europe/Stockholm</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tallinn">Europe/Tallinn</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tirane">Europe/Tirane</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tiraspol">Europe/Tiraspol</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Uzhgorod">Europe/Uzhgorod</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vaduz">Europe/Vaduz</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vatican">Europe/Vatican</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vienna">Europe/Vienna</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vilnius">Europe/Vilnius</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Warsaw">Europe/Warsaw</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zagreb">Europe/Zagreb</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zaporozhye">Europe/Zaporozhye</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zurich">Europe/Zurich</OPTION>\n\t\t\t\t<OPTION VALUE="Factory">Factory</OPTION>\n\t\t\t\t<OPTION VALUE="GB">GB</OPTION>\n\t\t\t\t<OPTION VALUE="GB-Eire">GB-Eire</OPTION>\n\t\t\t\t<OPTION VALUE="GMT" SELECTED>GMT</OPTION>\n\t\t\t\t<OPTION VALUE="GMT0">GMT0</OPTION>\n\t\t\t\t<OPTION VALUE="GMT-0">GMT-0</OPTION>\n\t\t\t\t<OPTION VALUE="GMT+0">GMT+0</OPTION>\n\t\t\t\t<OPTION VALUE="Greenwich">Greenwich</OPTION>\n\t\t\t\t<OPTION VALUE="Hongkong">Hongkong</OPTION>\n\t\t\t\t<OPTION VALUE="HST">HST</OPTION>\n\t\t\t\t<OPTION VALUE="Iceland">Iceland</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Antananarivo">Indian/Antananarivo</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Chagos">Indian/Chagos</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Christmas">Indian/Christmas</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Cocos">Indian/Cocos</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Comoro">Indian/Comoro</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Kerguelen">Indian/Kerguelen</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mahe">Indian/Mahe</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Maldives">Indian/Maldives</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mauritius">Indian/Mauritius</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mayotte">Indian/Mayotte</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Reunion">Indian/Reunion</OPTION>\n\t\t\t\t<OPTION VALUE="Iran">Iran</OPTION>\n\t\t\t\t<OPTION VALUE="Israel">Israel</OPTION>\n\t\t\t\t<OPTION VALUE="Jamaica">Jamaica</OPTION>\n\t\t\t\t<OPTION VALUE="Japan">Japan</OPTION>\n\t\t\t\t<OPTION VALUE="Kwajalein">Kwajalein</OPTION>\n\t\t\t\t<OPTION VALUE="Libya">Libya</OPTION>\n\t\t\t\t<OPTION VALUE="MET">MET</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/BajaNorte">Mexico/BajaNorte</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/BajaSur">Mexico/BajaSur</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/General">Mexico/General</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh87">Mideast/Riyadh87</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh88">Mideast/Riyadh88</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh89">Mideast/Riyadh89</OPTION>\n\t\t\t\t<OPTION VALUE="MST">MST</OPTION>\n\t\t\t\t<OPTION VALUE="MST7MDT">MST7MDT</OPTION>\n\t\t\t\t<OPTION VALUE="Navajo">Navajo</OPTION>\n\t\t\t\t<OPTION VALUE="NZ">NZ</OPTION>\n\t\t\t\t<OPTION VALUE="NZ-CHAT">NZ-CHAT</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Apia">Pacific/Apia</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Auckland">Pacific/Auckland</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Chatham">Pacific/Chatham</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Easter">Pacific/Easter</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Efate">Pacific/Efate</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Enderbury">Pacific/Enderbury</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Fakaofo">Pacific/Fakaofo</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Fiji">Pacific/Fiji</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Funafuti">Pacific/Funafuti</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Galapagos">Pacific/Galapagos</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Gambier">Pacific/Gambier</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Guadalcanal">Pacific/Guadalcanal</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Guam">Pacific/Guam</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Honolulu">Pacific/Honolulu</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Johnston">Pacific/Johnston</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kiritimati">Pacific/Kiritimati</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kosrae">Pacific/Kosrae</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kwajalein">Pacific/Kwajalein</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Majuro">Pacific/Majuro</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Marquesas">Pacific/Marquesas</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Midway">Pacific/Midway</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Nauru">Pacific/Nauru</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Niue">Pacific/Niue</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Norfolk">Pacific/Norfolk</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Noumea">Pacific/Noumea</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Pago_Pago">Pacific/Pago_Pago</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Palau">Pacific/Palau</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Pitcairn">Pacific/Pitcairn</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Ponape">Pacific/Ponape</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Port_Moresby">Pacific/Port_Moresby</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Rarotonga">Pacific/Rarotonga</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Saipan">Pacific/Saipan</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Samoa">Pacific/Samoa</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tahiti">Pacific/Tahiti</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tarawa">Pacific/Tarawa</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tongatapu">Pacific/Tongatapu</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Truk">Pacific/Truk</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Wake">Pacific/Wake</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Wallis">Pacific/Wallis</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Yap">Pacific/Yap</OPTION>\n\t\t\t\t<OPTION VALUE="Poland">Poland</OPTION>\n\t\t\t\t<OPTION VALUE="Portugal">Portugal</OPTION>\n\t\t\t\t<OPTION VALUE="PRC">PRC</OPTION>\n\t\t\t\t<OPTION VALUE="PST8PDT">PST8PDT</OPTION>\n\t\t\t\t<OPTION VALUE="ROC">ROC</OPTION>\n\t\t\t\t<OPTION VALUE="ROK">ROK</OPTION>\n\t\t\t\t<OPTION VALUE="Singapore">Singapore</OPTION>\n\t\t\t\t<OPTION VALUE="Turkey">Turkey</OPTION>\n\t\t\t\t<OPTION VALUE="UCT">UCT</OPTION>\n\t\t\t\t<OPTION VALUE="Universal">Universal</OPTION>\n\t\t\t\t<OPTION VALUE="UTC">UTC</OPTION>\n\t\t\t\t<OPTION VALUE="WET">WET</OPTION>\n\t\t\t\t<OPTION VALUE="W-SU">W-SU</OPTION>\n\t\t\t\t<OPTION VALUE="Zulu">Zulu</OPTION>\n\t\t</SELECT><P>\nEmail Address *:\n<BR><I>This email address will be verified before account activation.\nIt will not be displayed on the site. You will receive a mail forward\naccount at &lt;loginname@users.company.com&gt; that will forward to\nthis address.</I>\n<BR><INPUT size=30 type="text" name="email" value="">\n<P>\n<INPUT type="checkbox" name="mail_site" value="1" checked>\nReceive Email about Site Updates <I>(Very low traffic and includes\nsecurity notices. Highly Recommended.)</I>\n<P>\n<INPUT type="checkbox" name="mail_va" value="1">\nReceive additional community mailings. <I>(Low traffic.)</I>\n<p>\nFields marked with * are mandatory.\n</p>\n<p>\n<input type="submit" name="submit" value="Register">\n</form>\n\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/account/register.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1023281997
_account_register_php:a483c7d11fad8ec91a3a82926c2a1533	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"\n\t"http://www.w3.org/TR/REC-html40/loose.dtd">\n\n<!-- Server: server1 -->\n<html lang="en">\n  <head>\n\t<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">\n    <TITLE>SourceForge: SourceForge: Register</TITLE>\n\t<SCRIPT language="JavaScript">\n\t<!--\n\tfunction help_window(helpurl) {\n\t\tHelpWin = window.open( 'http://server1' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');\n\t}\n\t// -->\n\t</SCRIPT>\n\n\t\t<link rel="SHORTCUT ICON" href="/images/favicon.ico">\n\t\t<style type="text/css">\n\t\t\t<!--\n\tOL,UL,P,BODY,TD,TR,TH,FORM { font-family: verdana,arial,helvetica,sans-serif; font-size:x-small; color: #333333; }\n\n\tH1 { font-size: x-large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH2 { font-size: large; font-family: verdana,arial,helvetica,sans-serif; }\n\tH3 { font-size: medium; font-family: verdana,arial,helvetica,sans-serif; }\n\tH4 { font-size: small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH5 { font-size: x-small; font-family: verdana,arial,helvetica,sans-serif; }\n\tH6 { font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif; }\n\n\tPRE,TT { font-family: courier,sans-serif }\n\n\tSPAN.center { text-align: center }\n\tSPAN.boxspace { font-size: 2pt; }\n\tSPAN.osdn {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.search {font-size: xx-small; font-family:  verdana,arial,helvetica,sans-serif;}\n\tSPAN.slogan {font-size: large; font-weight: bold; font-family: verdana,arial,helvetica,sans-serif;}\n\tSPAN.footer {font-size: xx-small; font-family: verdana,arial,helvetica,sans-serif;}\n\n\tA.maintitlebar { color: #FFFFFF }\n\tA.maintitlebar:visited { color: #FFFFFF }\n\n\tA.sortbutton { color: #FFFFFF; text-decoration: underline; }\n\tA.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }\n\n\t.menus { color: #6666aa; text-decoration: none; }\n\t.menus:visited { color: #6666aa; text-decoration: none; }\n\n\tA:link { text-decoration:none }\n\tA:visited { text-decoration:none }\n\tA:active { text-decoration:none }\n\tA:hover { text-decoration:underline; color:#FF0000 }\n\n\t.tabs { color: #000000; }\n\t.tabs:visited { color: #000000; }\n\t.tabs:hover { color:#FF0000; }\n\t.tabselect { color: #000000; font-weight: bold; }\n\t.tabselect:visited { font-weight: bold;}\n\t.tabselect:hover { color:#FF0000; font-weight: bold; }\n\n\t.titlebar { text-decoration:none; color:#000000; font-family: Helvetica,verdana,arial,helvetica,sans-serif; font-size: x-small; font-weight: bold; }\n\t.develtitle { color:#000000; font-weight: bold; }\n\t.legallink { color:#000000; font-weight: bold; }\n\t\t\t-->\n\t\t</style>\n\n \t   </HEAD>\n<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="#6C7198" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">\n\n<!-- \n\nOSDN navbar \n\n-->\n<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">\n\t<tr> \n\t\t<td valign="middle" align="left" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.openmagazine.net/'style='text-decoration:none'><font color='#ffffff'>Open Magazine</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.geocrawler.com/'style='text-decoration:none'><font color='#ffffff'>Geocrawler</font></a>\n\t\t&nbsp;&middot;&nbsp;<a href='http://www.slashdot.com/'style='text-decoration:none'><font color='#ffffff'>Slashdot.Org</font></a>\n&nbsp;&middot;&nbsp;\n\t\t</SPAN>\n\t\t</td>\n\t\t<td valign="middle" align="right" bgcolor="#6C7198">\n\t\t<SPAN class="osdn">\n\t\t\t<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;\n\n\t\t<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; \n\t\t<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>\n\t\t</SPAN>\n\t\t</td>\n\t</tr>\n</table>\n\n<table width="100%" cellpadding="0" cellspacing="0" border="0">\n\t<tr> \n\t\t<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><img src="/images/blank.gif" width="100" height="1" alt=""></TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">\n    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>\n\n    <NOLAYER>\n      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no"><A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click"><IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>\n      </IFRAME>\n    </NOLAYER></td>\n\t\t<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com"><IMG src="/images/OSDN-lc.gif" hspace="10" border="0" alt=" OSDN - Open Source Development Network " border=0 width=100 height=40></a>\n\t</td>\n\t</tr>\n</table>\n<!-- \n\n\nEnd OSDN NavBar \n\n\n--><img src="/images/blank.gif" width="100" height="5" alt=""><br>\n<!-- start page body -->\n<div align="center">\n<table cellpadding="0" cellspacing="0" border="0" width="99%">\n\t<tr>\n\t\t<td background="//themes/forged/images/tbar1.png" width="1%" height="17"><IMG src="//themes/forged/images/tleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/tbar1.png" align="center" colspan="3" width="99%"><IMG src="//themes/forged/images/tbar1.png" border=0 width=1 height=17></td>\n\t\t<td><IMG src="//themes/forged/images/tright1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td width="17" background="//themes/forged/images/leftbar1.png" align="left" valign="bottom"><IMG src="//themes/forged/images/leftbar1.png" border=0 width=17 height=25></td>\n\t\t<td colspan="3" bgcolor="#ffffff">\n<!-- start main body cell -->\n\n\t<table cellpadding="0" cellspacing="0" border="0" width="100%">\n\t\t<tr>\n\t\t\t<td width="141" background="//themes/forged/images/steel3.jpg" bgcolor="#cfd1d4" align="left" valign="top">\n\n\t<!-- VA Linux Stats Counter -->\n\t<IMG src="http://www2.valinux.com/clear.gif?id=105" width=140 height=1 alt="Counter"><BR>\n\t<CENTER>\n\t<a href="/"><IMG src="//themes/forged/images/sflogo-hammer1.jpg" border=0 width=136 height=79></A>\n\t</CENTER>\n\t<P>\n\t<!-- menus -->\n\t<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Status:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>\t<A class="menus" href="/account/login.php">Login via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/account/register.php">New User via SSL</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Search</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n<CENTER>\n\t\t<FONT SIZE="1">\n\t\t<FORM action="/search/" method="POST" target="_blank">\n\n\t\t<SELECT name="type_of_search">\n\t\t<OPTION value="soft">Software/Group</OPTION>\n\t\t<OPTION value="people">People</OPTION>\n\t\t<OPTION value="freshmeat">Freshmeat.net</OPTION>\n\t\t</SELECT><BR>\n\t\t<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1" CHECKED> Require All Words<BR>\n\t\t<INPUT TYPE="text" SIZE="12" NAME="words" VALUE=""><BR><INPUT TYPE="submit" NAME="Search" VALUE="Search"></FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Software</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/softwaremap/">Software Map</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/new/">New Releases</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/mirrors/">Other Site Mirrors</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/snippet/">Code Snippet Library</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/docman/?group_id=1"><b>Site Docs</b></A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/forum/?group_id=1">Discussion Forums</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/people/">Project Help Wanted</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/top/">Top Projects</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/docman/display_doc.php?docid=2352&group_id=1">Site Status</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="http://jobs.osdn.com">jobs.osdn.com</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/compilefarm/">Compile Farm</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/contact.php">Contact SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/about.php">About SourceForge</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>SourceForge Foundries</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\t<A class="menus" href="/about_foundries.php">About Foundries</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br><P>\t<A class="menus" href="/foundry/linuxkernel/">Linux Kernel</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/linuxdrivers/">Linux Drivers</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/3d/">3D</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/games/">Games</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/java/">Java</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/printing/">Printing</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\t<A class="menus" href="/foundry/storage/">Storage</A> &nbsp;<IMG src="//themes/forged/images/point1.png" border=0 width=7 height=7><br>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n<table cellpadding="0" cellspacing="0" border="0" width="140">\n\t<tr>\n\t\t<td align="left" valign="middle"><b>Language:</b><br></td>\n\t</tr>\n\t<tr>\n\t\t<td align="right" valign="middle">\n\n\t<!--\t\n\n\t\tthis document.write is necessary\n\t\tto prevent the ads from screwing up\n\t\tthe rest of the site in netscape...\n\n\t\tThanks, netscape, for your cheesy browser\n\n\t-->\n\t<FONT SIZE="1">\n\t<FORM ACTION="/account/setlang.php" METHOD="POST">\n\t\n\t\t<select onchange="submit()" NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT>\n\t<BR>\n\t<NOSCRIPT>\n\t<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">\n\t</NOSCRIPT>\n\t</FORM></FONT>\n\t\t\t<BR>\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<P>\n\t</TD>\n\n\t<td width="20" background="//themes/forged/images/fade1.png" nowrap>&nbsp;</td>\n\t<td valign="top" bgcolor="#FFFFFF" width="99%">\n\t<BR>\n\n\t<h2>SourceForge New Account Registration</h2>\n\n\n<form action="http://server1/account/register.php" method="post">\n<p>\nLogin Name (do not use uppercase letters) *:<br>\n<input type="text" name="unix_name" value="">\n<p>\nPassword (min. 6 chars) *:<br>\n<input type="password" name="password1">\n<p>\nPassword (repeat) *:<br>\n<input type="password" name="password2">\n<P>\nFull/Real Name *:<BR>\n<INPUT size=30 type="text" name="realname" value="">\n<P>\nLanguage Choice:<BR>\n\n\t\t<SELECT NAME="language_id">\n\t\t\t\t<OPTION VALUE="20">Bulgarian</OPTION>\n\t\t\t\t<OPTION VALUE="14">Catalan</OPTION>\n\t\t\t\t<OPTION VALUE="12">Dutch</OPTION>\n\t\t\t\t<OPTION VALUE="1" SELECTED>English</OPTION>\n\t\t\t\t<OPTION VALUE="13">Esperanto</OPTION>\n\t\t\t\t<OPTION VALUE="7">French</OPTION>\n\t\t\t\t<OPTION VALUE="6">German</OPTION>\n\t\t\t\t<OPTION VALUE="19">Greek</OPTION>\n\t\t\t\t<OPTION VALUE="3">Hebrew</OPTION>\n\t\t\t\t<OPTION VALUE="21">Indonesian</OPTION>\n\t\t\t\t<OPTION VALUE="8">Italian</OPTION>\n\t\t\t\t<OPTION VALUE="2">Japanese</OPTION>\n\t\t\t\t<OPTION VALUE="22">Korean</OPTION>\n\t\t\t\t<OPTION VALUE="9">Norwegian</OPTION>\n\t\t\t\t<OPTION VALUE="15">Polish</OPTION>\n\t\t\t\t<OPTION VALUE="18">Portuguese</OPTION>\n\t\t\t\t<OPTION VALUE="16">Pt. Brazillian</OPTION>\n\t\t\t\t<OPTION VALUE="17">Russian</OPTION>\n\t\t\t\t<OPTION VALUE="23">Smpl.Chinese</OPTION>\n\t\t\t\t<OPTION VALUE="4">Spanish</OPTION>\n\t\t\t\t<OPTION VALUE="10">Swedish</OPTION>\n\t\t\t\t<OPTION VALUE="5">Thai</OPTION>\n\t\t\t\t<OPTION VALUE="11">Trad.Chinese</OPTION>\n\t\t</SELECT><P>\nTimezone:<BR>\n\n\t\t<SELECT NAME="timezone">\n\t\t\t\t<OPTION VALUE="US/Alaska">US/Alaska</OPTION>\n\t\t\t\t<OPTION VALUE="US/Aleutian">US/Aleutian</OPTION>\n\t\t\t\t<OPTION VALUE="US/Arizona">US/Arizona</OPTION>\n\t\t\t\t<OPTION VALUE="US/Central">US/Central</OPTION>\n\t\t\t\t<OPTION VALUE="US/Eastern">US/Eastern</OPTION>\n\t\t\t\t<OPTION VALUE="US/East-Indiana">US/East-Indiana</OPTION>\n\t\t\t\t<OPTION VALUE="US/Hawaii">US/Hawaii</OPTION>\n\t\t\t\t<OPTION VALUE="US/Indiana-Starke">US/Indiana-Starke</OPTION>\n\t\t\t\t<OPTION VALUE="US/Michigan">US/Michigan</OPTION>\n\t\t\t\t<OPTION VALUE="US/Mountain">US/Mountain</OPTION>\n\t\t\t\t<OPTION VALUE="US/Pacific">US/Pacific</OPTION>\n\t\t\t\t<OPTION VALUE="US/Samoa">US/Samoa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Abidjan">Africa/Abidjan</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Accra">Africa/Accra</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Addis_Ababa">Africa/Addis_Ababa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Algiers">Africa/Algiers</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Asmera">Africa/Asmera</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bamako">Africa/Bamako</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bangui">Africa/Bangui</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Banjul">Africa/Banjul</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bissau">Africa/Bissau</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Blantyre">Africa/Blantyre</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Brazzaville">Africa/Brazzaville</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Bujumbura">Africa/Bujumbura</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Cairo">Africa/Cairo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Casablanca">Africa/Casablanca</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ceuta">Africa/Ceuta</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Conakry">Africa/Conakry</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Dakar">Africa/Dakar</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Dar_es_Salaam">Africa/Dar_es_Salaam</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Djibouti">Africa/Djibouti</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Douala">Africa/Douala</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/El_Aaiun">Africa/El_Aaiun</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Freetown">Africa/Freetown</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Gaborone">Africa/Gaborone</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Harare">Africa/Harare</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Johannesburg">Africa/Johannesburg</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kampala">Africa/Kampala</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Khartoum">Africa/Khartoum</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kigali">Africa/Kigali</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Kinshasa">Africa/Kinshasa</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lagos">Africa/Lagos</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Libreville">Africa/Libreville</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lome">Africa/Lome</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Luanda">Africa/Luanda</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lubumbashi">Africa/Lubumbashi</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Lusaka">Africa/Lusaka</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Malabo">Africa/Malabo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Maputo">Africa/Maputo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Maseru">Africa/Maseru</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Mbabane">Africa/Mbabane</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Mogadishu">Africa/Mogadishu</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Monrovia">Africa/Monrovia</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Nairobi">Africa/Nairobi</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ndjamena">Africa/Ndjamena</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Niamey">Africa/Niamey</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Nouakchott">Africa/Nouakchott</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Ouagadougou">Africa/Ouagadougou</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Porto-Novo">Africa/Porto-Novo</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Sao_Tome">Africa/Sao_Tome</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Timbuktu">Africa/Timbuktu</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Tripoli">Africa/Tripoli</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Tunis">Africa/Tunis</OPTION>\n\t\t\t\t<OPTION VALUE="Africa/Windhoek">Africa/Windhoek</OPTION>\n\t\t\t\t<OPTION VALUE="America/Adak">America/Adak</OPTION>\n\t\t\t\t<OPTION VALUE="America/Anchorage">America/Anchorage</OPTION>\n\t\t\t\t<OPTION VALUE="America/Anguilla">America/Anguilla</OPTION>\n\t\t\t\t<OPTION VALUE="America/Antigua">America/Antigua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Araguaina">America/Araguaina</OPTION>\n\t\t\t\t<OPTION VALUE="America/Aruba">America/Aruba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Asuncion">America/Asuncion</OPTION>\n\t\t\t\t<OPTION VALUE="America/Atka">America/Atka</OPTION>\n\t\t\t\t<OPTION VALUE="America/Barbados">America/Barbados</OPTION>\n\t\t\t\t<OPTION VALUE="America/Belem">America/Belem</OPTION>\n\t\t\t\t<OPTION VALUE="America/Belize">America/Belize</OPTION>\n\t\t\t\t<OPTION VALUE="America/Boa_Vista">America/Boa_Vista</OPTION>\n\t\t\t\t<OPTION VALUE="America/Bogota">America/Bogota</OPTION>\n\t\t\t\t<OPTION VALUE="America/Boise">America/Boise</OPTION>\n\t\t\t\t<OPTION VALUE="America/Buenos_Aires">America/Buenos_Aires</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cambridge_Bay">America/Cambridge_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cancun">America/Cancun</OPTION>\n\t\t\t\t<OPTION VALUE="America/Caracas">America/Caracas</OPTION>\n\t\t\t\t<OPTION VALUE="America/Catamarca">America/Catamarca</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cayenne">America/Cayenne</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cayman">America/Cayman</OPTION>\n\t\t\t\t<OPTION VALUE="America/Chicago">America/Chicago</OPTION>\n\t\t\t\t<OPTION VALUE="America/Chihuahua">America/Chihuahua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cordoba">America/Cordoba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Costa_Rica">America/Costa_Rica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Cuiaba">America/Cuiaba</OPTION>\n\t\t\t\t<OPTION VALUE="America/Curacao">America/Curacao</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dawson">America/Dawson</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dawson_Creek">America/Dawson_Creek</OPTION>\n\t\t\t\t<OPTION VALUE="America/Denver">America/Denver</OPTION>\n\t\t\t\t<OPTION VALUE="America/Detroit">America/Detroit</OPTION>\n\t\t\t\t<OPTION VALUE="America/Dominica">America/Dominica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Edmonton">America/Edmonton</OPTION>\n\t\t\t\t<OPTION VALUE="America/El_Salvador">America/El_Salvador</OPTION>\n\t\t\t\t<OPTION VALUE="America/Ensenada">America/Ensenada</OPTION>\n\t\t\t\t<OPTION VALUE="America/Fortaleza">America/Fortaleza</OPTION>\n\t\t\t\t<OPTION VALUE="America/Fort_Wayne">America/Fort_Wayne</OPTION>\n\t\t\t\t<OPTION VALUE="America/Glace_Bay">America/Glace_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Godthab">America/Godthab</OPTION>\n\t\t\t\t<OPTION VALUE="America/Goose_Bay">America/Goose_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Grand_Turk">America/Grand_Turk</OPTION>\n\t\t\t\t<OPTION VALUE="America/Grenada">America/Grenada</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guadeloupe">America/Guadeloupe</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guatemala">America/Guatemala</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guayaquil">America/Guayaquil</OPTION>\n\t\t\t\t<OPTION VALUE="America/Guyana">America/Guyana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Halifax">America/Halifax</OPTION>\n\t\t\t\t<OPTION VALUE="America/Havana">America/Havana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Hermosillo">America/Hermosillo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Indianapolis">America/Indiana/Indianapolis</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Knox">America/Indiana/Knox</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Marengo">America/Indiana/Marengo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indiana/Vevay">America/Indiana/Vevay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Indianapolis">America/Indianapolis</OPTION>\n\t\t\t\t<OPTION VALUE="America/Inuvik">America/Inuvik</OPTION>\n\t\t\t\t<OPTION VALUE="America/Iqaluit">America/Iqaluit</OPTION>\n\t\t\t\t<OPTION VALUE="America/Jamaica">America/Jamaica</OPTION>\n\t\t\t\t<OPTION VALUE="America/Jujuy">America/Jujuy</OPTION>\n\t\t\t\t<OPTION VALUE="America/Juneau">America/Juneau</OPTION>\n\t\t\t\t<OPTION VALUE="America/Knox_IN">America/Knox_IN</OPTION>\n\t\t\t\t<OPTION VALUE="America/La_Paz">America/La_Paz</OPTION>\n\t\t\t\t<OPTION VALUE="America/Lima">America/Lima</OPTION>\n\t\t\t\t<OPTION VALUE="America/Los_Angeles">America/Los_Angeles</OPTION>\n\t\t\t\t<OPTION VALUE="America/Louisville">America/Louisville</OPTION>\n\t\t\t\t<OPTION VALUE="America/Maceio">America/Maceio</OPTION>\n\t\t\t\t<OPTION VALUE="America/Managua">America/Managua</OPTION>\n\t\t\t\t<OPTION VALUE="America/Manaus">America/Manaus</OPTION>\n\t\t\t\t<OPTION VALUE="America/Martinique">America/Martinique</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mazatlan">America/Mazatlan</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mendoza">America/Mendoza</OPTION>\n\t\t\t\t<OPTION VALUE="America/Menominee">America/Menominee</OPTION>\n\t\t\t\t<OPTION VALUE="America/Mexico_City">America/Mexico_City</OPTION>\n\t\t\t\t<OPTION VALUE="America/Miquelon">America/Miquelon</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montevideo">America/Montevideo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montreal">America/Montreal</OPTION>\n\t\t\t\t<OPTION VALUE="America/Montserrat">America/Montserrat</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nassau">America/Nassau</OPTION>\n\t\t\t\t<OPTION VALUE="America/New_York">America/New_York</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nipigon">America/Nipigon</OPTION>\n\t\t\t\t<OPTION VALUE="America/Nome">America/Nome</OPTION>\n\t\t\t\t<OPTION VALUE="America/Noronha">America/Noronha</OPTION>\n\t\t\t\t<OPTION VALUE="America/Panama">America/Panama</OPTION>\n\t\t\t\t<OPTION VALUE="America/Pangnirtung">America/Pangnirtung</OPTION>\n\t\t\t\t<OPTION VALUE="America/Paramaribo">America/Paramaribo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Phoenix">America/Phoenix</OPTION>\n\t\t\t\t<OPTION VALUE="America/Port-au-Prince">America/Port-au-Prince</OPTION>\n\t\t\t\t<OPTION VALUE="America/Porto_Acre">America/Porto_Acre</OPTION>\n\t\t\t\t<OPTION VALUE="America/Port_of_Spain">America/Port_of_Spain</OPTION>\n\t\t\t\t<OPTION VALUE="America/Porto_Velho">America/Porto_Velho</OPTION>\n\t\t\t\t<OPTION VALUE="America/Puerto_Rico">America/Puerto_Rico</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rainy_River">America/Rainy_River</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rankin_Inlet">America/Rankin_Inlet</OPTION>\n\t\t\t\t<OPTION VALUE="America/Regina">America/Regina</OPTION>\n\t\t\t\t<OPTION VALUE="America/Rosario">America/Rosario</OPTION>\n\t\t\t\t<OPTION VALUE="America/Santiago">America/Santiago</OPTION>\n\t\t\t\t<OPTION VALUE="America/Santo_Domingo">America/Santo_Domingo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Sao_Paulo">America/Sao_Paulo</OPTION>\n\t\t\t\t<OPTION VALUE="America/Scoresbysund">America/Scoresbysund</OPTION>\n\t\t\t\t<OPTION VALUE="America/Shiprock">America/Shiprock</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Johns">America/St_Johns</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Kitts">America/St_Kitts</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Lucia">America/St_Lucia</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Thomas">America/St_Thomas</OPTION>\n\t\t\t\t<OPTION VALUE="America/St_Vincent">America/St_Vincent</OPTION>\n\t\t\t\t<OPTION VALUE="America/Swift_Current">America/Swift_Current</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tegucigalpa">America/Tegucigalpa</OPTION>\n\t\t\t\t<OPTION VALUE="America/Thule">America/Thule</OPTION>\n\t\t\t\t<OPTION VALUE="America/Thunder_Bay">America/Thunder_Bay</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tijuana">America/Tijuana</OPTION>\n\t\t\t\t<OPTION VALUE="America/Tortola">America/Tortola</OPTION>\n\t\t\t\t<OPTION VALUE="America/Vancouver">America/Vancouver</OPTION>\n\t\t\t\t<OPTION VALUE="America/Virgin">America/Virgin</OPTION>\n\t\t\t\t<OPTION VALUE="America/Whitehorse">America/Whitehorse</OPTION>\n\t\t\t\t<OPTION VALUE="America/Winnipeg">America/Winnipeg</OPTION>\n\t\t\t\t<OPTION VALUE="America/Yakutat">America/Yakutat</OPTION>\n\t\t\t\t<OPTION VALUE="America/Yellowknife">America/Yellowknife</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Casey">Antarctica/Casey</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Davis">Antarctica/Davis</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/DumontDUrville">Antarctica/DumontDUrville</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Mawson">Antarctica/Mawson</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/McMurdo">Antarctica/McMurdo</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Palmer">Antarctica/Palmer</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/South_Pole">Antarctica/South_Pole</OPTION>\n\t\t\t\t<OPTION VALUE="Antarctica/Syowa">Antarctica/Syowa</OPTION>\n\t\t\t\t<OPTION VALUE="Arctic/Longyearbyen">Arctic/Longyearbyen</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aden">Asia/Aden</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Almaty">Asia/Almaty</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Amman">Asia/Amman</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Anadyr">Asia/Anadyr</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aqtau">Asia/Aqtau</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Aqtobe">Asia/Aqtobe</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ashkhabad">Asia/Ashkhabad</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Baghdad">Asia/Baghdad</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bahrain">Asia/Bahrain</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Baku">Asia/Baku</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bangkok">Asia/Bangkok</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Beirut">Asia/Beirut</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Bishkek">Asia/Bishkek</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Brunei">Asia/Brunei</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Calcutta">Asia/Calcutta</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Chungking">Asia/Chungking</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Colombo">Asia/Colombo</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dacca">Asia/Dacca</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Damascus">Asia/Damascus</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dili">Asia/Dili</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dubai">Asia/Dubai</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Dushanbe">Asia/Dushanbe</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Gaza">Asia/Gaza</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Harbin">Asia/Harbin</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Hong_Kong">Asia/Hong_Kong</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Hovd">Asia/Hovd</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Irkutsk">Asia/Irkutsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Istanbul">Asia/Istanbul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jakarta">Asia/Jakarta</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jayapura">Asia/Jayapura</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Jerusalem">Asia/Jerusalem</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kabul">Asia/Kabul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kamchatka">Asia/Kamchatka</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Karachi">Asia/Karachi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kashgar">Asia/Kashgar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Katmandu">Asia/Katmandu</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Krasnoyarsk">Asia/Krasnoyarsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuala_Lumpur">Asia/Kuala_Lumpur</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuching">Asia/Kuching</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Kuwait">Asia/Kuwait</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Macao">Asia/Macao</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Magadan">Asia/Magadan</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Manila">Asia/Manila</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Muscat">Asia/Muscat</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Nicosia">Asia/Nicosia</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Novosibirsk">Asia/Novosibirsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Omsk">Asia/Omsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Phnom_Penh">Asia/Phnom_Penh</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Pyongyang">Asia/Pyongyang</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Qatar">Asia/Qatar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Rangoon">Asia/Rangoon</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh">Asia/Riyadh</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh87">Asia/Riyadh87</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh88">Asia/Riyadh88</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Riyadh89">Asia/Riyadh89</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Saigon">Asia/Saigon</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Samarkand">Asia/Samarkand</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Seoul">Asia/Seoul</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Shanghai">Asia/Shanghai</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Singapore">Asia/Singapore</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Taipei">Asia/Taipei</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tashkent">Asia/Tashkent</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tbilisi">Asia/Tbilisi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tehran">Asia/Tehran</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tel_Aviv">Asia/Tel_Aviv</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Thimbu">Asia/Thimbu</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Tokyo">Asia/Tokyo</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ujung_Pandang">Asia/Ujung_Pandang</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ulaanbaatar">Asia/Ulaanbaatar</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Ulan_Bator">Asia/Ulan_Bator</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Urumqi">Asia/Urumqi</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Vientiane">Asia/Vientiane</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Vladivostok">Asia/Vladivostok</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yakutsk">Asia/Yakutsk</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yekaterinburg">Asia/Yekaterinburg</OPTION>\n\t\t\t\t<OPTION VALUE="Asia/Yerevan">Asia/Yerevan</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Azores">Atlantic/Azores</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Bermuda">Atlantic/Bermuda</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Canary">Atlantic/Canary</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Cape_Verde">Atlantic/Cape_Verde</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Faeroe">Atlantic/Faeroe</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Jan_Mayen">Atlantic/Jan_Mayen</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Madeira">Atlantic/Madeira</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Reykjavik">Atlantic/Reykjavik</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/South_Georgia">Atlantic/South_Georgia</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/Stanley">Atlantic/Stanley</OPTION>\n\t\t\t\t<OPTION VALUE="Atlantic/St_Helena">Atlantic/St_Helena</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/ACT">Australia/ACT</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Adelaide">Australia/Adelaide</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Brisbane">Australia/Brisbane</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Broken_Hill">Australia/Broken_Hill</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Canberra">Australia/Canberra</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Darwin">Australia/Darwin</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Hobart">Australia/Hobart</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/LHI">Australia/LHI</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Lindeman">Australia/Lindeman</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Lord_Howe">Australia/Lord_Howe</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Melbourne">Australia/Melbourne</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/North">Australia/North</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/NSW">Australia/NSW</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Perth">Australia/Perth</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Queensland">Australia/Queensland</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/South">Australia/South</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Sydney">Australia/Sydney</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Tasmania">Australia/Tasmania</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Victoria">Australia/Victoria</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/West">Australia/West</OPTION>\n\t\t\t\t<OPTION VALUE="Australia/Yancowinna">Australia/Yancowinna</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/Acre">Brazil/Acre</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/DeNoronha">Brazil/DeNoronha</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/East">Brazil/East</OPTION>\n\t\t\t\t<OPTION VALUE="Brazil/West">Brazil/West</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Atlantic">Canada/Atlantic</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Central">Canada/Central</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Eastern">Canada/Eastern</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/East-Saskatchewan">Canada/East-Saskatchewan</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Mountain">Canada/Mountain</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Newfoundland">Canada/Newfoundland</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Pacific">Canada/Pacific</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Saskatchewan">Canada/Saskatchewan</OPTION>\n\t\t\t\t<OPTION VALUE="Canada/Yukon">Canada/Yukon</OPTION>\n\t\t\t\t<OPTION VALUE="CET">CET</OPTION>\n\t\t\t\t<OPTION VALUE="Chile/Continental">Chile/Continental</OPTION>\n\t\t\t\t<OPTION VALUE="Chile/EasterIsland">Chile/EasterIsland</OPTION>\n\t\t\t\t<OPTION VALUE="China/Beijing">China/Beijing</OPTION>\n\t\t\t\t<OPTION VALUE="China/Shanghai">China/Shanghai</OPTION>\n\t\t\t\t<OPTION VALUE="CST6CDT">CST6CDT</OPTION>\n\t\t\t\t<OPTION VALUE="Cuba">Cuba</OPTION>\n\t\t\t\t<OPTION VALUE="EET">EET</OPTION>\n\t\t\t\t<OPTION VALUE="Egypt">Egypt</OPTION>\n\t\t\t\t<OPTION VALUE="Eire">Eire</OPTION>\n\t\t\t\t<OPTION VALUE="EST">EST</OPTION>\n\t\t\t\t<OPTION VALUE="EST5EDT">EST5EDT</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Amsterdam">Europe/Amsterdam</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Andorra">Europe/Andorra</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Athens">Europe/Athens</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Belfast">Europe/Belfast</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Belgrade">Europe/Belgrade</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Berlin">Europe/Berlin</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Bratislava">Europe/Bratislava</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Brussels">Europe/Brussels</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Bucharest">Europe/Bucharest</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Budapest">Europe/Budapest</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Chisinau">Europe/Chisinau</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Copenhagen">Europe/Copenhagen</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Dublin">Europe/Dublin</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Gibraltar">Europe/Gibraltar</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Helsinki">Europe/Helsinki</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Istanbul">Europe/Istanbul</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Kaliningrad">Europe/Kaliningrad</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Kiev">Europe/Kiev</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Lisbon">Europe/Lisbon</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Ljubljana">Europe/Ljubljana</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/London">Europe/London</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Luxembourg">Europe/Luxembourg</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Madrid">Europe/Madrid</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Malta">Europe/Malta</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Minsk">Europe/Minsk</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Monaco">Europe/Monaco</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Moscow">Europe/Moscow</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Oslo">Europe/Oslo</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Paris">Europe/Paris</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Prague">Europe/Prague</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Riga">Europe/Riga</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Rome">Europe/Rome</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Samara">Europe/Samara</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/San_Marino">Europe/San_Marino</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Sarajevo">Europe/Sarajevo</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Simferopol">Europe/Simferopol</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Skopje">Europe/Skopje</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Sofia">Europe/Sofia</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Stockholm">Europe/Stockholm</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tallinn">Europe/Tallinn</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tirane">Europe/Tirane</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Tiraspol">Europe/Tiraspol</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Uzhgorod">Europe/Uzhgorod</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vaduz">Europe/Vaduz</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vatican">Europe/Vatican</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vienna">Europe/Vienna</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Vilnius">Europe/Vilnius</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Warsaw">Europe/Warsaw</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zagreb">Europe/Zagreb</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zaporozhye">Europe/Zaporozhye</OPTION>\n\t\t\t\t<OPTION VALUE="Europe/Zurich">Europe/Zurich</OPTION>\n\t\t\t\t<OPTION VALUE="Factory">Factory</OPTION>\n\t\t\t\t<OPTION VALUE="GB">GB</OPTION>\n\t\t\t\t<OPTION VALUE="GB-Eire">GB-Eire</OPTION>\n\t\t\t\t<OPTION VALUE="GMT" SELECTED>GMT</OPTION>\n\t\t\t\t<OPTION VALUE="GMT0">GMT0</OPTION>\n\t\t\t\t<OPTION VALUE="GMT-0">GMT-0</OPTION>\n\t\t\t\t<OPTION VALUE="GMT+0">GMT+0</OPTION>\n\t\t\t\t<OPTION VALUE="Greenwich">Greenwich</OPTION>\n\t\t\t\t<OPTION VALUE="Hongkong">Hongkong</OPTION>\n\t\t\t\t<OPTION VALUE="HST">HST</OPTION>\n\t\t\t\t<OPTION VALUE="Iceland">Iceland</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Antananarivo">Indian/Antananarivo</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Chagos">Indian/Chagos</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Christmas">Indian/Christmas</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Cocos">Indian/Cocos</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Comoro">Indian/Comoro</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Kerguelen">Indian/Kerguelen</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mahe">Indian/Mahe</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Maldives">Indian/Maldives</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mauritius">Indian/Mauritius</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Mayotte">Indian/Mayotte</OPTION>\n\t\t\t\t<OPTION VALUE="Indian/Reunion">Indian/Reunion</OPTION>\n\t\t\t\t<OPTION VALUE="Iran">Iran</OPTION>\n\t\t\t\t<OPTION VALUE="Israel">Israel</OPTION>\n\t\t\t\t<OPTION VALUE="Jamaica">Jamaica</OPTION>\n\t\t\t\t<OPTION VALUE="Japan">Japan</OPTION>\n\t\t\t\t<OPTION VALUE="Kwajalein">Kwajalein</OPTION>\n\t\t\t\t<OPTION VALUE="Libya">Libya</OPTION>\n\t\t\t\t<OPTION VALUE="MET">MET</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/BajaNorte">Mexico/BajaNorte</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/BajaSur">Mexico/BajaSur</OPTION>\n\t\t\t\t<OPTION VALUE="Mexico/General">Mexico/General</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh87">Mideast/Riyadh87</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh88">Mideast/Riyadh88</OPTION>\n\t\t\t\t<OPTION VALUE="Mideast/Riyadh89">Mideast/Riyadh89</OPTION>\n\t\t\t\t<OPTION VALUE="MST">MST</OPTION>\n\t\t\t\t<OPTION VALUE="MST7MDT">MST7MDT</OPTION>\n\t\t\t\t<OPTION VALUE="Navajo">Navajo</OPTION>\n\t\t\t\t<OPTION VALUE="NZ">NZ</OPTION>\n\t\t\t\t<OPTION VALUE="NZ-CHAT">NZ-CHAT</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Apia">Pacific/Apia</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Auckland">Pacific/Auckland</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Chatham">Pacific/Chatham</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Easter">Pacific/Easter</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Efate">Pacific/Efate</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Enderbury">Pacific/Enderbury</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Fakaofo">Pacific/Fakaofo</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Fiji">Pacific/Fiji</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Funafuti">Pacific/Funafuti</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Galapagos">Pacific/Galapagos</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Gambier">Pacific/Gambier</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Guadalcanal">Pacific/Guadalcanal</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Guam">Pacific/Guam</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Honolulu">Pacific/Honolulu</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Johnston">Pacific/Johnston</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kiritimati">Pacific/Kiritimati</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kosrae">Pacific/Kosrae</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Kwajalein">Pacific/Kwajalein</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Majuro">Pacific/Majuro</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Marquesas">Pacific/Marquesas</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Midway">Pacific/Midway</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Nauru">Pacific/Nauru</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Niue">Pacific/Niue</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Norfolk">Pacific/Norfolk</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Noumea">Pacific/Noumea</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Pago_Pago">Pacific/Pago_Pago</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Palau">Pacific/Palau</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Pitcairn">Pacific/Pitcairn</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Ponape">Pacific/Ponape</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Port_Moresby">Pacific/Port_Moresby</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Rarotonga">Pacific/Rarotonga</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Saipan">Pacific/Saipan</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Samoa">Pacific/Samoa</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tahiti">Pacific/Tahiti</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tarawa">Pacific/Tarawa</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Tongatapu">Pacific/Tongatapu</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Truk">Pacific/Truk</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Wake">Pacific/Wake</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Wallis">Pacific/Wallis</OPTION>\n\t\t\t\t<OPTION VALUE="Pacific/Yap">Pacific/Yap</OPTION>\n\t\t\t\t<OPTION VALUE="Poland">Poland</OPTION>\n\t\t\t\t<OPTION VALUE="Portugal">Portugal</OPTION>\n\t\t\t\t<OPTION VALUE="PRC">PRC</OPTION>\n\t\t\t\t<OPTION VALUE="PST8PDT">PST8PDT</OPTION>\n\t\t\t\t<OPTION VALUE="ROC">ROC</OPTION>\n\t\t\t\t<OPTION VALUE="ROK">ROK</OPTION>\n\t\t\t\t<OPTION VALUE="Singapore">Singapore</OPTION>\n\t\t\t\t<OPTION VALUE="Turkey">Turkey</OPTION>\n\t\t\t\t<OPTION VALUE="UCT">UCT</OPTION>\n\t\t\t\t<OPTION VALUE="Universal">Universal</OPTION>\n\t\t\t\t<OPTION VALUE="UTC">UTC</OPTION>\n\t\t\t\t<OPTION VALUE="WET">WET</OPTION>\n\t\t\t\t<OPTION VALUE="W-SU">W-SU</OPTION>\n\t\t\t\t<OPTION VALUE="Zulu">Zulu</OPTION>\n\t\t</SELECT><P>\nEmail Address *:\n<BR><I>This email address will be verified before account activation.\nIt will not be displayed on the site. You will receive a mail forward\naccount at &lt;loginname@users.company.com&gt; that will forward to\nthis address.</I>\n<BR><INPUT size=30 type="text" name="email" value="">\n<P>\n<INPUT type="checkbox" name="mail_site" value="1" checked>\nReceive Email about Site Updates <I>(Very low traffic and includes\nsecurity notices. Highly Recommended.)</I>\n<P>\n<INPUT type="checkbox" name="mail_va" value="1">\nReceive additional community mailings. <I>(Low traffic.)</I>\n<p>\nFields marked with * are mandatory.\n</p>\n<p>\n<input type="submit" name="submit" value="Register">\n</form>\n\n\t<!-- end content -->\n\t<p>&nbsp;</p>\n\t</td>\n\t<td width="9" bgcolor="#FFFFFF">&nbsp;\n\t</td>\n\n\t</tr>\n\t</table>\n\t\t</td>\n\t\t<td width="17" background="//themes/forged/images/rightbar1.png" align="right" valign="bottom"><IMG src="//themes/forged/images/rightbar1.png" border=0 width=17 height=17></td>\n\t</tr>\n\t<tr>\n\t\t<td background="//themes/forged/images/bbar1.png" height="17"><IMG src="//themes/forged/images/bleft1.png" border=0 width=17 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" align="center" colspan="3"><IMG src="//themes/forged/images/bbar1.png" border=0 width=1 height=17></td>\n\t\t<td background="//themes/forged/images/bbar1.png" bgcolor="#7c8188"><IMG src="//themes/forged/images/bright1.png" border=0 width=17 height=17></td>\n\t</tr>\n</table>\n</div>\n\n<!-- themed page footer -->\n<P><A HREF="/source.php?page_url=/account/register.php"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P><P class="footer">\n<font face="arial, helvetica" size="1" color="#cccccc">\nVA Linux Systems and SourceForge are trademarks of VA Linux Systems, Inc.\nLinux is a registered trademark of Linus Torvalds.  All other trademarks and\ncopyrights on this page are property of their respective owners.\nFor information about other site Content ownership and sitewide\nterms of service, please see the\n<a href="/tos/tos.php" class="legallink">SourceForge Terms of Service</a>.\nFor privacy\npolicy information, please see the <a href="/tos/privacy.php" class="legallink"\n>SourceForge Privacy Policy</a>.\nContent owned by VA Linux Systems is copyright \n1999-2001 VA Linux Systems, Inc.  All rights reserved.\n</font>\n<BR>&nbsp;\n\n\n<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility='hide' onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility='show';"></LAYER>\n</body>\n</html>\n\t	1023282187
\.
--
-- Data for TOC Entry ID 546 (OID 45491152)
--
-- Name: user_metric_history Type: TABLE DATA Owner: tperdue
--


COPY "user_metric_history" FROM stdin;
\.
--
-- Data for TOC Entry ID 547 (OID 45491154)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_filetotal_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 548 (OID 45491156)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_grouptotal_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 549 (OID 45491158)
--
-- Name: frs_dlstats_group_agg Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_group_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 550 (OID 45491160)
--
-- Name: stats_project_months Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_months" FROM stdin;
\.
--
-- Data for TOC Entry ID 551 (OID 45491162)
--
-- Name: stats_project_all Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_all" FROM stdin;
\.
--
-- Data for TOC Entry ID 552 (OID 45491164)
--
-- Name: stats_project_developers_last30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_developers_last30" FROM stdin;
\.
--
-- Data for TOC Entry ID 553 (OID 45491166)
--
-- Name: stats_project_last_30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_project_last_30" FROM stdin;
\.
--
-- Data for TOC Entry ID 554 (OID 45491168)
--
-- Name: stats_site_pages_by_month Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_pages_by_month" FROM stdin;
\.
--
-- Data for TOC Entry ID 555 (OID 45491170)
--
-- Name: stats_site_last_30 Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_last_30" FROM stdin;
\.
--
-- Data for TOC Entry ID 556 (OID 45491172)
--
-- Name: stats_site_months Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_months" FROM stdin;
\.
--
-- Data for TOC Entry ID 557 (OID 45491174)
--
-- Name: stats_site_all Type: TABLE DATA Owner: tperdue
--


COPY "stats_site_all" FROM stdin;
\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.
--
-- Data for TOC Entry ID 558 (OID 45491176)
--
-- Name: trove_agg Type: TABLE DATA Owner: tperdue
--


COPY "trove_agg" FROM stdin;
\.
--
-- Data for TOC Entry ID 559 (OID 45491180)
--
-- Name: trove_treesums Type: TABLE DATA Owner: tperdue
--


COPY "trove_treesums" FROM stdin;
\.
--
-- Data for TOC Entry ID 560 (OID 45491183)
--
-- Name: frs_dlstats_file Type: TABLE DATA Owner: tperdue
--


COPY "frs_dlstats_file" FROM stdin;
\.
--
-- TOC Entry ID 296 (OID 45491627)
--
-- Name: "db_images_group" Type: INDEX Owner: tperdue
--

CREATE INDEX db_images_group ON db_images USING btree (group_id);

--
-- TOC Entry ID 297 (OID 45491628)
--
-- Name: "doc_group_doc_group" Type: INDEX Owner: tperdue
--

CREATE INDEX doc_group_doc_group ON doc_data USING btree (doc_group);

--
-- TOC Entry ID 298 (OID 45491629)
--
-- Name: "doc_groups_group" Type: INDEX Owner: tperdue
--

CREATE INDEX doc_groups_group ON doc_groups USING btree (group_id);

--
-- TOC Entry ID 299 (OID 45491630)
--
-- Name: "filemodule_monitor_id" Type: INDEX Owner: tperdue
--

CREATE INDEX filemodule_monitor_id ON filemodule_monitor USING btree (filemodule_id);

--
-- TOC Entry ID 300 (OID 45491631)
--
-- Name: "filemodulemonitor_userid" Type: INDEX Owner: tperdue
--

CREATE INDEX filemodulemonitor_userid ON filemodule_monitor USING btree (user_id);

--
-- TOC Entry ID 301 (OID 45491632)
--
-- Name: "forum_forumid_msgid" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_forumid_msgid ON forum USING btree (group_forum_id, msg_id);

--
-- TOC Entry ID 302 (OID 45491633)
--
-- Name: "forum_group_forum_id" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_group_forum_id ON forum USING btree (group_forum_id);

--
-- TOC Entry ID 303 (OID 45491634)
--
-- Name: "forum_forumid_isfollowupto" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_forumid_isfollowupto ON forum USING btree (group_forum_id, is_followup_to);

--
-- TOC Entry ID 304 (OID 45491635)
--
-- Name: "forum_forumid_threadid_mostrece" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_forumid_threadid_mostrece ON forum USING btree (group_forum_id, thread_id, most_recent_date);

--
-- TOC Entry ID 305 (OID 45491636)
--
-- Name: "forum_threadid_isfollowupto" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_threadid_isfollowupto ON forum USING btree (thread_id, is_followup_to);

--
-- TOC Entry ID 306 (OID 45491637)
--
-- Name: "forum_forumid_isfollto_mostrece" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_forumid_isfollto_mostrece ON forum USING btree (group_forum_id, is_followup_to, most_recent_date);

--
-- TOC Entry ID 307 (OID 45491638)
--
-- Name: "forum_group_list_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_group_list_group_id ON forum_group_list USING btree (group_id);

--
-- TOC Entry ID 308 (OID 45491639)
--
-- Name: "forummonitoredforums_user" Type: INDEX Owner: tperdue
--

CREATE INDEX forummonitoredforums_user ON forum_monitored_forums USING btree (user_id);

--
-- TOC Entry ID 309 (OID 45491640)
--
-- Name: "forum_monitor_combo_id" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_monitor_combo_id ON forum_monitored_forums USING btree (forum_id, user_id);

--
-- TOC Entry ID 310 (OID 45491641)
--
-- Name: "forum_monitor_thread_id" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_monitor_thread_id ON forum_monitored_forums USING btree (forum_id);

--
-- TOC Entry ID 311 (OID 45491642)
--
-- Name: "frs_file_date" Type: INDEX Owner: tperdue
--

CREATE INDEX frs_file_date ON frs_file USING btree (post_date);

--
-- TOC Entry ID 312 (OID 45491643)
--
-- Name: "frs_file_release_id" Type: INDEX Owner: tperdue
--

CREATE INDEX frs_file_release_id ON frs_file USING btree (release_id);

--
-- TOC Entry ID 313 (OID 45491644)
--
-- Name: "package_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX package_group_id ON frs_package USING btree (group_id);

--
-- TOC Entry ID 314 (OID 45491645)
--
-- Name: "frs_release_package" Type: INDEX Owner: tperdue
--

CREATE INDEX frs_release_package ON frs_release USING btree (package_id);

--
-- TOC Entry ID 315 (OID 45491646)
--
-- Name: "group_history_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX group_history_group_id ON group_history USING btree (group_id);

--
-- TOC Entry ID 316 (OID 45491647)
--
-- Name: "group_unix_uniq" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX group_unix_uniq ON groups USING btree (unix_group_name);

--
-- TOC Entry ID 317 (OID 45491648)
--
-- Name: "groups_type" Type: INDEX Owner: tperdue
--

CREATE INDEX groups_type ON groups USING btree ("type");

--
-- TOC Entry ID 318 (OID 45491649)
--
-- Name: "groups_public" Type: INDEX Owner: tperdue
--

CREATE INDEX groups_public ON groups USING btree (is_public);

--
-- TOC Entry ID 319 (OID 45491650)
--
-- Name: "groups_status" Type: INDEX Owner: tperdue
--

CREATE INDEX groups_status ON groups USING btree (status);

--
-- TOC Entry ID 320 (OID 45491651)
--
-- Name: "mail_group_list_group" Type: INDEX Owner: tperdue
--

CREATE INDEX mail_group_list_group ON mail_group_list USING btree (group_id);

--
-- TOC Entry ID 321 (OID 45491652)
--
-- Name: "news_bytes_group" Type: INDEX Owner: tperdue
--

CREATE INDEX news_bytes_group ON news_bytes USING btree (group_id);

--
-- TOC Entry ID 322 (OID 45491653)
--
-- Name: "news_bytes_approved" Type: INDEX Owner: tperdue
--

CREATE INDEX news_bytes_approved ON news_bytes USING btree (is_approved);

--
-- TOC Entry ID 323 (OID 45491654)
--
-- Name: "news_bytes_forum" Type: INDEX Owner: tperdue
--

CREATE INDEX news_bytes_forum ON news_bytes USING btree (forum_id);

--
-- TOC Entry ID 324 (OID 45491655)
--
-- Name: "news_group_date" Type: INDEX Owner: tperdue
--

CREATE INDEX news_group_date ON news_bytes USING btree (group_id, date);

--
-- TOC Entry ID 325 (OID 45491656)
--
-- Name: "news_approved_date" Type: INDEX Owner: tperdue
--

CREATE INDEX news_approved_date ON news_bytes USING btree (is_approved, date);

--
-- TOC Entry ID 326 (OID 45491657)
--
-- Name: "people_job_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX people_job_group_id ON people_job USING btree (group_id);

--
-- TOC Entry ID 327 (OID 45491658)
--
-- Name: "project_assigned_to_assigned_to" Type: INDEX Owner: tperdue
--

CREATE INDEX project_assigned_to_assigned_to ON project_assigned_to USING btree (assigned_to_id);

--
-- TOC Entry ID 328 (OID 45491659)
--
-- Name: "project_assigned_to_task_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_assigned_to_task_id ON project_assigned_to USING btree (project_task_id);

--
-- TOC Entry ID 329 (OID 45491660)
--
-- Name: "project_is_dependent_on_task_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_is_dependent_on_task_id ON project_dependencies USING btree (is_dependent_on_task_id);

--
-- TOC Entry ID 330 (OID 45491661)
--
-- Name: "project_dependencies_task_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_dependencies_task_id ON project_dependencies USING btree (project_task_id);

--
-- TOC Entry ID 331 (OID 45491662)
--
-- Name: "project_group_list_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_group_list_group_id ON project_group_list USING btree (group_id);

--
-- TOC Entry ID 332 (OID 45491663)
--
-- Name: "project_history_task_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_history_task_id ON project_history USING btree (project_task_id);

--
-- TOC Entry ID 333 (OID 45491664)
--
-- Name: "project_metric_group" Type: INDEX Owner: tperdue
--

CREATE INDEX project_metric_group ON project_metric USING btree (group_id);

--
-- TOC Entry ID 334 (OID 45491665)
--
-- Name: "projecttask_projid_status" Type: INDEX Owner: tperdue
--

CREATE INDEX projecttask_projid_status ON project_task USING btree (group_project_id, status_id);

--
-- TOC Entry ID 335 (OID 45491666)
--
-- Name: "project_task_group_project_id" Type: INDEX Owner: tperdue
--

CREATE INDEX project_task_group_project_id ON project_task USING btree (group_project_id);

--
-- TOC Entry ID 336 (OID 45491667)
--
-- Name: "projectweeklymetric_ranking" Type: INDEX Owner: tperdue
--

CREATE INDEX projectweeklymetric_ranking ON project_weekly_metric USING btree (ranking);

--
-- TOC Entry ID 337 (OID 45491668)
--
-- Name: "project_metric_weekly_group" Type: INDEX Owner: tperdue
--

CREATE INDEX project_metric_weekly_group ON project_weekly_metric USING btree (group_id);

--
-- TOC Entry ID 338 (OID 45491669)
--
-- Name: "session_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX session_user_id ON "session" USING btree (user_id);

--
-- TOC Entry ID 339 (OID 45491670)
--
-- Name: "session_time" Type: INDEX Owner: tperdue
--

CREATE INDEX session_time ON "session" USING btree ("time");

--
-- TOC Entry ID 340 (OID 45491671)
--
-- Name: "snippet_language" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_language ON snippet USING btree ("language");

--
-- TOC Entry ID 341 (OID 45491672)
--
-- Name: "snippet_category" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_category ON snippet USING btree (category);

--
-- TOC Entry ID 342 (OID 45491673)
--
-- Name: "snippet_package_language" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_package_language ON snippet_package USING btree ("language");

--
-- TOC Entry ID 343 (OID 45491674)
--
-- Name: "snippet_package_category" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_package_category ON snippet_package USING btree (category);

--
-- TOC Entry ID 344 (OID 45491675)
--
-- Name: "snippet_package_item_pkg_ver" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_package_item_pkg_ver ON snippet_package_item USING btree (snippet_package_version_id);

--
-- TOC Entry ID 345 (OID 45491676)
--
-- Name: "snippet_package_version_pkg_id" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_package_version_pkg_id ON snippet_package_version USING btree (snippet_package_id);

--
-- TOC Entry ID 346 (OID 45491677)
--
-- Name: "snippet_version_snippet_id" Type: INDEX Owner: tperdue
--

CREATE INDEX snippet_version_snippet_id ON snippet_version USING btree (snippet_id);

--
-- TOC Entry ID 347 (OID 45491678)
--
-- Name: "pages_by_day_day" Type: INDEX Owner: tperdue
--

CREATE INDEX pages_by_day_day ON stats_agg_pages_by_day USING btree ("day");

--
-- TOC Entry ID 348 (OID 45491681)
--
-- Name: "supported_languages_code" Type: INDEX Owner: tperdue
--

CREATE INDEX supported_languages_code ON supported_languages USING btree (language_code);

--
-- TOC Entry ID 349 (OID 45491682)
--
-- Name: "survey_questions_group" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_questions_group ON survey_questions USING btree (group_id);

--
-- TOC Entry ID 350 (OID 45491683)
--
-- Name: "survey_rating_aggregate_type_id" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_rating_aggregate_type_id ON survey_rating_aggregate USING btree ("type", id);

--
-- TOC Entry ID 351 (OID 45491684)
--
-- Name: "survey_rating_responses_user_ty" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_rating_responses_user_ty ON survey_rating_response USING btree (user_id, "type", id);

--
-- TOC Entry ID 352 (OID 45491685)
--
-- Name: "survey_rating_responses_type_id" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_rating_responses_type_id ON survey_rating_response USING btree ("type", id);

--
-- TOC Entry ID 353 (OID 45491686)
--
-- Name: "survey_responses_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_responses_group_id ON survey_responses USING btree (group_id);

--
-- TOC Entry ID 354 (OID 45491687)
--
-- Name: "survey_responses_user_survey_qu" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_responses_user_survey_qu ON survey_responses USING btree (user_id, survey_id, question_id);

--
-- TOC Entry ID 355 (OID 45491688)
--
-- Name: "survey_responses_user_survey" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_responses_user_survey ON survey_responses USING btree (user_id, survey_id);

--
-- TOC Entry ID 356 (OID 45491689)
--
-- Name: "survey_responses_survey_questio" Type: INDEX Owner: tperdue
--

CREATE INDEX survey_responses_survey_questio ON survey_responses USING btree (survey_id, question_id);

--
-- TOC Entry ID 357 (OID 45491690)
--
-- Name: "surveys_group" Type: INDEX Owner: tperdue
--

CREATE INDEX surveys_group ON surveys USING btree (group_id);

--
-- TOC Entry ID 358 (OID 45491696)
--
-- Name: "parent_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX parent_idx ON trove_cat USING btree (parent);

--
-- TOC Entry ID 359 (OID 45491697)
--
-- Name: "root_parent_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX root_parent_idx ON trove_cat USING btree (root_parent);

--
-- TOC Entry ID 360 (OID 45491698)
--
-- Name: "version_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX version_idx ON trove_cat USING btree ("version");

--
-- TOC Entry ID 361 (OID 45491699)
--
-- Name: "trove_group_link_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX trove_group_link_group_id ON trove_group_link USING btree (group_id);

--
-- TOC Entry ID 362 (OID 45491700)
--
-- Name: "trove_group_link_cat_id" Type: INDEX Owner: tperdue
--

CREATE INDEX trove_group_link_cat_id ON trove_group_link USING btree (trove_cat_id);

--
-- TOC Entry ID 363 (OID 45491701)
--
-- Name: "user_bookmark_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_bookmark_user_id ON user_bookmarks USING btree (user_id);

--
-- TOC Entry ID 364 (OID 45491702)
--
-- Name: "user_diary_user" Type: INDEX Owner: tperdue
--

CREATE INDEX user_diary_user ON user_diary USING btree (user_id);

--
-- TOC Entry ID 365 (OID 45491703)
--
-- Name: "user_diary_user_date" Type: INDEX Owner: tperdue
--

CREATE INDEX user_diary_user_date ON user_diary USING btree (user_id, date_posted);

--
-- TOC Entry ID 366 (OID 45491704)
--
-- Name: "user_diary_date" Type: INDEX Owner: tperdue
--

CREATE INDEX user_diary_date ON user_diary USING btree (date_posted);

--
-- TOC Entry ID 367 (OID 45491705)
--
-- Name: "user_diary_monitor_user" Type: INDEX Owner: tperdue
--

CREATE INDEX user_diary_monitor_user ON user_diary_monitor USING btree (user_id);

--
-- TOC Entry ID 368 (OID 45491706)
--
-- Name: "user_diary_monitor_monitored_us" Type: INDEX Owner: tperdue
--

CREATE INDEX user_diary_monitor_monitored_us ON user_diary_monitor USING btree (monitored_user);

--
-- TOC Entry ID 369 (OID 45491707)
--
-- Name: "user_group_group_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_group_group_id ON user_group USING btree (group_id);

--
-- TOC Entry ID 370 (OID 45491708)
--
-- Name: "bug_flags_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX bug_flags_idx ON user_group USING btree (bug_flags);

--
-- TOC Entry ID 371 (OID 45491709)
--
-- Name: "project_flags_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX project_flags_idx ON user_group USING btree (project_flags);

--
-- TOC Entry ID 372 (OID 45491710)
--
-- Name: "user_group_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_group_user_id ON user_group USING btree (user_id);

--
-- TOC Entry ID 373 (OID 45491711)
--
-- Name: "admin_flags_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX admin_flags_idx ON user_group USING btree (admin_flags);

--
-- TOC Entry ID 374 (OID 45491712)
--
-- Name: "forum_flags_idx" Type: INDEX Owner: tperdue
--

CREATE INDEX forum_flags_idx ON user_group USING btree (forum_flags);

--
-- TOC Entry ID 375 (OID 45491713)
--
-- Name: "usergroup_uniq_groupid_userid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX usergroup_uniq_groupid_userid ON user_group USING btree (group_id, user_id);

--
-- TOC Entry ID 376 (OID 45491714)
--
-- Name: "user_metric0_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_metric0_user_id ON user_metric0 USING btree (user_id);

--
-- TOC Entry ID 377 (OID 45491715)
--
-- Name: "user_pref_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_pref_user_id ON user_preferences USING btree (user_id);

--
-- TOC Entry ID 378 (OID 45491716)
--
-- Name: "user_ratings_rated_by" Type: INDEX Owner: tperdue
--

CREATE INDEX user_ratings_rated_by ON user_ratings USING btree (rated_by);

--
-- TOC Entry ID 379 (OID 45491717)
--
-- Name: "user_ratings_user_id" Type: INDEX Owner: tperdue
--

CREATE INDEX user_ratings_user_id ON user_ratings USING btree (user_id);

--
-- TOC Entry ID 380 (OID 45491718)
--
-- Name: "users_namename_uniq" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX users_namename_uniq ON users USING btree (user_name);

--
-- TOC Entry ID 381 (OID 45491719)
--
-- Name: "users_status" Type: INDEX Owner: tperdue
--

CREATE INDEX users_status ON users USING btree (status);

--
-- TOC Entry ID 382 (OID 45491720)
--
-- Name: "users_user_pw" Type: INDEX Owner: tperdue
--

CREATE INDEX users_user_pw ON users USING btree (user_pw);

--
-- TOC Entry ID 383 (OID 45491721)
--
-- Name: "projectsumsagg_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX projectsumsagg_groupid ON project_sums_agg USING btree (group_id);

--
-- TOC Entry ID 384 (OID 45491722)
--
-- Name: "idx_prdb_dbname" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX idx_prdb_dbname ON prdb_dbs USING btree (dbname);

--
-- TOC Entry ID 385 (OID 45491723)
--
-- Name: "idx_vhost_groups" Type: INDEX Owner: tperdue
--

CREATE INDEX idx_vhost_groups ON prweb_vhost USING btree (group_id);

--
-- TOC Entry ID 386 (OID 45491724)
--
-- Name: "idx_vhost_hostnames" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX idx_vhost_hostnames ON prweb_vhost USING btree (vhost_name);

--
-- TOC Entry ID 387 (OID 45491725)
--
-- Name: "artgrouplist_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX artgrouplist_groupid ON artifact_group_list USING btree (group_id);

--
-- TOC Entry ID 388 (OID 45491726)
--
-- Name: "artgrouplist_groupid_public" Type: INDEX Owner: tperdue
--

CREATE INDEX artgrouplist_groupid_public ON artifact_group_list USING btree (group_id, is_public);

--
-- TOC Entry ID 389 (OID 45491727)
--
-- Name: "artperm_groupartifactid_userid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX artperm_groupartifactid_userid ON artifact_perm USING btree (group_artifact_id, user_id);

--
-- TOC Entry ID 390 (OID 45491728)
--
-- Name: "artperm_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE INDEX artperm_groupartifactid ON artifact_perm USING btree (group_artifact_id);

--
-- TOC Entry ID 391 (OID 45491729)
--
-- Name: "artcategory_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE INDEX artcategory_groupartifactid ON artifact_category USING btree (group_artifact_id);

--
-- TOC Entry ID 392 (OID 45491730)
--
-- Name: "artgroup_groupartifactid" Type: INDEX Owner: tperdue
--

CREATE INDEX artgroup_groupartifactid ON artifact_group USING btree (group_artifact_id);

--
-- TOC Entry ID 393 (OID 45491731)
--
-- Name: "art_groupartid" Type: INDEX Owner: tperdue
--

CREATE INDEX art_groupartid ON artifact USING btree (group_artifact_id);

--
-- TOC Entry ID 394 (OID 45491732)
--
-- Name: "art_groupartid_statusid" Type: INDEX Owner: tperdue
--

CREATE INDEX art_groupartid_statusid ON artifact USING btree (group_artifact_id, status_id);

--
-- TOC Entry ID 395 (OID 45491733)
--
-- Name: "art_groupartid_assign" Type: INDEX Owner: tperdue
--

CREATE INDEX art_groupartid_assign ON artifact USING btree (group_artifact_id, assigned_to);

--
-- TOC Entry ID 396 (OID 45491734)
--
-- Name: "art_groupartid_submit" Type: INDEX Owner: tperdue
--

CREATE INDEX art_groupartid_submit ON artifact USING btree (group_artifact_id, submitted_by);

--
-- TOC Entry ID 397 (OID 45491735)
--
-- Name: "art_submit_status" Type: INDEX Owner: tperdue
--

CREATE INDEX art_submit_status ON artifact USING btree (submitted_by, status_id);

--
-- TOC Entry ID 398 (OID 45491736)
--
-- Name: "art_assign_status" Type: INDEX Owner: tperdue
--

CREATE INDEX art_assign_status ON artifact USING btree (assigned_to, status_id);

--
-- TOC Entry ID 399 (OID 45491737)
--
-- Name: "art_groupartid_artifactid" Type: INDEX Owner: tperdue
--

CREATE INDEX art_groupartid_artifactid ON artifact USING btree (group_artifact_id, artifact_id);

--
-- TOC Entry ID 400 (OID 45491738)
--
-- Name: "arthistory_artid" Type: INDEX Owner: tperdue
--

CREATE INDEX arthistory_artid ON artifact_history USING btree (artifact_id);

--
-- TOC Entry ID 401 (OID 45491739)
--
-- Name: "arthistory_artid_entrydate" Type: INDEX Owner: tperdue
--

CREATE INDEX arthistory_artid_entrydate ON artifact_history USING btree (artifact_id, entrydate);

--
-- TOC Entry ID 402 (OID 45491740)
--
-- Name: "artfile_artid" Type: INDEX Owner: tperdue
--

CREATE INDEX artfile_artid ON artifact_file USING btree (artifact_id);

--
-- TOC Entry ID 403 (OID 45491741)
--
-- Name: "artfile_artid_adddate" Type: INDEX Owner: tperdue
--

CREATE INDEX artfile_artid_adddate ON artifact_file USING btree (artifact_id, adddate);

--
-- TOC Entry ID 404 (OID 45491742)
--
-- Name: "artmessage_artid" Type: INDEX Owner: tperdue
--

CREATE INDEX artmessage_artid ON artifact_message USING btree (artifact_id);

--
-- TOC Entry ID 405 (OID 45491743)
--
-- Name: "artmessage_artid_adddate" Type: INDEX Owner: tperdue
--

CREATE INDEX artmessage_artid_adddate ON artifact_message USING btree (artifact_id, adddate);

--
-- TOC Entry ID 406 (OID 45491744)
--
-- Name: "artmonitor_artifactid" Type: INDEX Owner: tperdue
--

CREATE INDEX artmonitor_artifactid ON artifact_monitor USING btree (artifact_id);

--
-- TOC Entry ID 407 (OID 45491745)
--
-- Name: "artifactcannedresponses_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX artifactcannedresponses_groupid ON artifact_canned_responses USING btree (group_artifact_id);

--
-- TOC Entry ID 408 (OID 45491746)
--
-- Name: "artifactcountsagg_groupartid" Type: INDEX Owner: tperdue
--

CREATE INDEX artifactcountsagg_groupartid ON artifact_counts_agg USING btree (group_artifact_id);

--
-- TOC Entry ID 409 (OID 45491747)
--
-- Name: "statssitepgsbyday_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssitepgsbyday_oid ON stats_site_pages_by_day USING btree (oid);

--
-- TOC Entry ID 410 (OID 45491748)
--
-- Name: "statssitepagesbyday_month_day" Type: INDEX Owner: tperdue
--

CREATE INDEX statssitepagesbyday_month_day ON stats_site_pages_by_day USING btree ("month", "day");

--
-- TOC Entry ID 411 (OID 45491749)
--
-- Name: "frsdlfileagg_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX frsdlfileagg_oid ON frs_dlstats_file_agg USING btree (oid);

--
-- TOC Entry ID 412 (OID 45491750)
--
-- Name: "frsdlfileagg_month_day_file" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX frsdlfileagg_month_day_file ON frs_dlstats_file_agg USING btree ("month", "day", file_id);

--
-- TOC Entry ID 413 (OID 45491751)
--
-- Name: "statsaggsitebygrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsaggsitebygrp_oid ON stats_agg_site_by_group USING btree (oid);

--
-- TOC Entry ID 414 (OID 45491752)
--
-- Name: "statssitebygroup_month_day_grou" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssitebygroup_month_day_grou ON stats_agg_site_by_group USING btree ("month", "day", group_id);

--
-- TOC Entry ID 415 (OID 45491753)
--
-- Name: "statsprojectmetric_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsprojectmetric_oid ON stats_project_metric USING btree (oid);

--
-- TOC Entry ID 416 (OID 45491754)
--
-- Name: "statsprojectmetric_month_day_gr" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsprojectmetric_month_day_gr ON stats_project_metric USING btree ("month", "day", group_id);

--
-- TOC Entry ID 417 (OID 45491755)
--
-- Name: "statsagglogobygrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsagglogobygrp_oid ON stats_agg_logo_by_group USING btree (oid);

--
-- TOC Entry ID 418 (OID 45491756)
--
-- Name: "statslogobygroup_month_day_grou" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statslogobygroup_month_day_grou ON stats_agg_logo_by_group USING btree ("month", "day", group_id);

--
-- TOC Entry ID 419 (OID 45491757)
--
-- Name: "statssubdpages_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssubdpages_oid ON stats_subd_pages USING btree (oid);

--
-- TOC Entry ID 420 (OID 45491758)
--
-- Name: "statssubdpages_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssubdpages_month_day_group ON stats_subd_pages USING btree ("month", "day", group_id);

--
-- TOC Entry ID 421 (OID 45491759)
--
-- Name: "statscvsgrp_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statscvsgrp_oid ON stats_cvs_group USING btree (oid);

--
-- TOC Entry ID 422 (OID 45491760)
--
-- Name: "statscvsgroup_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statscvsgroup_month_day_group ON stats_cvs_group USING btree ("month", "day", group_id);

--
-- TOC Entry ID 423 (OID 45491761)
--
-- Name: "statsprojectdevelop_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsprojectdevelop_oid ON stats_project_developers USING btree (oid);

--
-- TOC Entry ID 424 (OID 45491762)
--
-- Name: "statsprojectdev_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsprojectdev_month_day_group ON stats_project_developers USING btree ("month", "day", group_id);

--
-- TOC Entry ID 425 (OID 45491763)
--
-- Name: "statsproject_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsproject_oid ON stats_project USING btree (oid);

--
-- TOC Entry ID 426 (OID 45491764)
--
-- Name: "statsproject_month_day_group" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statsproject_month_day_group ON stats_project USING btree ("month", "day", group_id);

--
-- TOC Entry ID 427 (OID 45491765)
--
-- Name: "statssite_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssite_oid ON stats_site USING btree (oid);

--
-- TOC Entry ID 428 (OID 45491766)
--
-- Name: "statssite_month_day" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX statssite_month_day ON stats_site USING btree ("month", "day");

--
-- TOC Entry ID 429 (OID 45491767)
--
-- Name: "user_metric_history_date_userid" Type: INDEX Owner: tperdue
--

CREATE INDEX user_metric_history_date_userid ON user_metric_history USING btree ("month", "day", user_id);

--
-- TOC Entry ID 430 (OID 45491768)
--
-- Name: "frsdlfiletotal_fileid" Type: INDEX Owner: tperdue
--

CREATE INDEX frsdlfiletotal_fileid ON frs_dlstats_filetotal_agg USING btree (file_id);

--
-- TOC Entry ID 431 (OID 45491769)
--
-- Name: "frsdlgrouptotal_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX frsdlgrouptotal_groupid ON frs_dlstats_grouptotal_agg USING btree (group_id);

--
-- TOC Entry ID 432 (OID 45491770)
--
-- Name: "frsdlgroup_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX frsdlgroup_groupid ON frs_dlstats_group_agg USING btree (group_id);

--
-- TOC Entry ID 433 (OID 45491771)
--
-- Name: "frsdlgroup_month_day_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX frsdlgroup_month_day_groupid ON frs_dlstats_group_agg USING btree ("month", "day", group_id);

--
-- TOC Entry ID 434 (OID 45491772)
--
-- Name: "statsprojectmonths_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX statsprojectmonths_groupid ON stats_project_months USING btree (group_id);

--
-- TOC Entry ID 435 (OID 45491773)
--
-- Name: "statsprojectmonths_groupid_mont" Type: INDEX Owner: tperdue
--

CREATE INDEX statsprojectmonths_groupid_mont ON stats_project_months USING btree (group_id, "month");

--
-- TOC Entry ID 436 (OID 45491774)
--
-- Name: "statsprojectall_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX statsprojectall_groupid ON stats_project_all USING btree (group_id);

--
-- TOC Entry ID 437 (OID 45491775)
--
-- Name: "statsproject30_groupid" Type: INDEX Owner: tperdue
--

CREATE INDEX statsproject30_groupid ON stats_project_last_30 USING btree (group_id);

--
-- TOC Entry ID 438 (OID 45491776)
--
-- Name: "statssitelast30_month_day" Type: INDEX Owner: tperdue
--

CREATE INDEX statssitelast30_month_day ON stats_site_last_30 USING btree ("month", "day");

--
-- TOC Entry ID 439 (OID 45491777)
--
-- Name: "statssitemonths_month" Type: INDEX Owner: tperdue
--

CREATE INDEX statssitemonths_month ON stats_site_months USING btree ("month");

--
-- TOC Entry ID 440 (OID 45491778)
--
-- Name: "troveagg_trovecatid" Type: INDEX Owner: tperdue
--

CREATE INDEX troveagg_trovecatid ON trove_agg USING btree (trove_cat_id);

--
-- TOC Entry ID 441 (OID 45491779)
--
-- Name: "troveagg_trovecatid_ranking" Type: INDEX Owner: tperdue
--

CREATE INDEX troveagg_trovecatid_ranking ON trove_agg USING btree (trove_cat_id, ranking);

--
-- TOC Entry ID 611 (OID 45491781)
--
-- Name: "RI_ConstraintTrigger_45491780" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER INSERT OR UPDATE ON "user_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 613 (OID 45491783)
--
-- Name: "RI_ConstraintTrigger_45491782" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 614 (OID 45491785)
--
-- Name: "RI_ConstraintTrigger_45491784" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_user_id_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 612 (OID 45491787)
--
-- Name: "RI_ConstraintTrigger_45491786" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER INSERT OR UPDATE ON "user_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 591 (OID 45491789)
--
-- Name: "RI_ConstraintTrigger_45491788" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 592 (OID 45491791)
--
-- Name: "RI_ConstraintTrigger_45491790" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "user_group_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 561 (OID 45491793)
--
-- Name: "RI_ConstraintTrigger_45491792" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 615 (OID 45491795)
--
-- Name: "RI_ConstraintTrigger_45491794" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 616 (OID 45491797)
--
-- Name: "RI_ConstraintTrigger_45491796" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 562 (OID 45491799)
--
-- Name: "RI_ConstraintTrigger_45491798" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 565 (OID 45491801)
--
-- Name: "RI_ConstraintTrigger_45491800" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER DELETE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 566 (OID 45491803)
--
-- Name: "RI_ConstraintTrigger_45491802" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 567 (OID 45491805)
--
-- Name: "RI_ConstraintTrigger_45491804" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER INSERT OR UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 593 (OID 45491807)
--
-- Name: "RI_ConstraintTrigger_45491806" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 594 (OID 45491809)
--
-- Name: "RI_ConstraintTrigger_45491808" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_list_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 563 (OID 45491811)
--
-- Name: "RI_ConstraintTrigger_45491810" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 617 (OID 45491813)
--
-- Name: "RI_ConstraintTrigger_45491812" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 618 (OID 45491815)
--
-- Name: "RI_ConstraintTrigger_45491814" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_posted_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 564 (OID 45491817)
--
-- Name: "RI_ConstraintTrigger_45491816" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER INSERT OR UPDATE ON "forum"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 568 (OID 45491819)
--
-- Name: "RI_ConstraintTrigger_45491818" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER DELETE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 569 (OID 45491821)
--
-- Name: "RI_ConstraintTrigger_45491820" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "forum_group_forum_id_fk" AFTER UPDATE ON "forum_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 601 (OID 45491823)
--
-- Name: "RI_ConstraintTrigger_45491822" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER INSERT OR UPDATE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 595 (OID 45491825)
--
-- Name: "RI_ConstraintTrigger_45491824" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 596 (OID 45491827)
--
-- Name: "RI_ConstraintTrigger_45491826" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_group_list_group_id_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 606 (OID 45491829)
--
-- Name: "RI_ConstraintTrigger_45491828" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 602 (OID 45491831)
--
-- Name: "RI_ConstraintTrigger_45491830" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER DELETE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 603 (OID 45491833)
--
-- Name: "RI_ConstraintTrigger_45491832" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_group_project_id_f" AFTER UPDATE ON "project_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 607 (OID 45491835)
--
-- Name: "RI_ConstraintTrigger_45491834" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 619 (OID 45491837)
--
-- Name: "RI_ConstraintTrigger_45491836" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 620 (OID 45491839)
--
-- Name: "RI_ConstraintTrigger_45491838" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_created_by_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 608 (OID 45491841)
--
-- Name: "RI_ConstraintTrigger_45491840" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER INSERT OR UPDATE ON "project_task"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 604 (OID 45491843)
--
-- Name: "RI_ConstraintTrigger_45491842" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER DELETE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 605 (OID 45491845)
--
-- Name: "RI_ConstraintTrigger_45491844" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "project_task_status_id_fk" AFTER UPDATE ON "project_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 621 (OID 45491847)
--
-- Name: "RI_ConstraintTrigger_45491846" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER INSERT OR UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 609 (OID 45491849)
--
-- Name: "RI_ConstraintTrigger_45491848" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER DELETE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 610 (OID 45491851)
--
-- Name: "RI_ConstraintTrigger_45491850" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "users_languageid_fk" AFTER UPDATE ON "supported_languages"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 685 (OID 45491853)
--
-- Name: "RI_ConstraintTrigger_45491852" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_monitor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 661 (OID 45491855)
--
-- Name: "RI_ConstraintTrigger_45491854" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 662 (OID 45491857)
--
-- Name: "RI_ConstraintTrigger_45491856" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 638 (OID 45491859)
--
-- Name: "RI_ConstraintTrigger_45491858" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER INSERT OR UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 597 (OID 45491861)
--
-- Name: "RI_ConstraintTrigger_45491860" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 598 (OID 45491863)
--
-- Name: "RI_ConstraintTrigger_45491862" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupid_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactgroup_groupid_fk', 'artifact_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 650 (OID 45491865)
--
-- Name: "RI_ConstraintTrigger_45491864" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER INSERT OR UPDATE ON "artifact_perm"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 622 (OID 45491867)
--
-- Name: "RI_ConstraintTrigger_45491866" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 623 (OID 45491869)
--
-- Name: "RI_ConstraintTrigger_45491868" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_userid_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactperm_userid_fk', 'artifact_perm', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 651 (OID 45491871)
--
-- Name: "RI_ConstraintTrigger_45491870" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER INSERT OR UPDATE ON "artifact_perm"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 639 (OID 45491873)
--
-- Name: "RI_ConstraintTrigger_45491872" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 640 (OID 45491875)
--
-- Name: "RI_ConstraintTrigger_45491874" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactperm_groupartifactid_fk" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactperm_groupartifactid_fk', 'artifact_perm', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 652 (OID 45491877)
--
-- Name: "RI_ConstraintTrigger_45491876" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER INSERT OR UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 641 (OID 45491879)
--
-- Name: "RI_ConstraintTrigger_45491878" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 642 (OID 45491881)
--
-- Name: "RI_ConstraintTrigger_45491880" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_groupartifacti" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactcategory_groupartifacti', 'artifact_category', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 653 (OID 45491883)
--
-- Name: "RI_ConstraintTrigger_45491882" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER INSERT OR UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');

--
-- TOC Entry ID 624 (OID 45491885)
--
-- Name: "RI_ConstraintTrigger_45491884" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');

--
-- TOC Entry ID 625 (OID 45491887)
--
-- Name: "RI_ConstraintTrigger_45491886" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactcategory_autoassignto_f" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactcategory_autoassignto_f', 'artifact_category', 'users', 'FULL', 'auto_assign_to', 'user_id');

--
-- TOC Entry ID 656 (OID 45491889)
--
-- Name: "RI_ConstraintTrigger_45491888" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER INSERT OR UPDATE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 643 (OID 45491891)
--
-- Name: "RI_ConstraintTrigger_45491890" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 644 (OID 45491893)
--
-- Name: "RI_ConstraintTrigger_45491892" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactgroup_groupartifactid_f" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactgroup_groupartifactid_f', 'artifact_group', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 663 (OID 45491895)
--
-- Name: "RI_ConstraintTrigger_45491894" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 645 (OID 45491897)
--
-- Name: "RI_ConstraintTrigger_45491896" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER DELETE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 646 (OID 45491899)
--
-- Name: "RI_ConstraintTrigger_45491898" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_groupartifactid_fk" AFTER UPDATE ON "artifact_group_list"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_groupartifactid_fk', 'artifact', 'artifact_group_list', 'FULL', 'group_artifact_id', 'group_artifact_id');

--
-- TOC Entry ID 664 (OID 45491901)
--
-- Name: "RI_ConstraintTrigger_45491900" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');

--
-- TOC Entry ID 659 (OID 45491903)
--
-- Name: "RI_ConstraintTrigger_45491902" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER DELETE ON "artifact_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');

--
-- TOC Entry ID 660 (OID 45491905)
--
-- Name: "RI_ConstraintTrigger_45491904" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_statusid_fk" AFTER UPDATE ON "artifact_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_statusid_fk', 'artifact', 'artifact_status', 'FULL', 'status_id', 'id');

--
-- TOC Entry ID 665 (OID 45491907)
--
-- Name: "RI_ConstraintTrigger_45491906" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');

--
-- TOC Entry ID 654 (OID 45491909)
--
-- Name: "RI_ConstraintTrigger_45491908" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER DELETE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');

--
-- TOC Entry ID 655 (OID 45491911)
--
-- Name: "RI_ConstraintTrigger_45491910" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_categoryid_fk" AFTER UPDATE ON "artifact_category"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_categoryid_fk', 'artifact', 'artifact_category', 'FULL', 'category_id', 'id');

--
-- TOC Entry ID 666 (OID 45491913)
--
-- Name: "RI_ConstraintTrigger_45491912" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');

--
-- TOC Entry ID 657 (OID 45491915)
--
-- Name: "RI_ConstraintTrigger_45491914" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER DELETE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');

--
-- TOC Entry ID 658 (OID 45491917)
--
-- Name: "RI_ConstraintTrigger_45491916" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_artifactgroupid_fk" AFTER UPDATE ON "artifact_group"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_artifactgroupid_fk', 'artifact', 'artifact_group', 'FULL', 'artifact_group_id', 'id');

--
-- TOC Entry ID 667 (OID 45491919)
--
-- Name: "RI_ConstraintTrigger_45491918" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 626 (OID 45491921)
--
-- Name: "RI_ConstraintTrigger_45491920" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 627 (OID 45491923)
--
-- Name: "RI_ConstraintTrigger_45491922" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_submittedby_fk', 'artifact', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 668 (OID 45491925)
--
-- Name: "RI_ConstraintTrigger_45491924" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 628 (OID 45491927)
--
-- Name: "RI_ConstraintTrigger_45491926" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 629 (OID 45491929)
--
-- Name: "RI_ConstraintTrigger_45491928" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_assignedto_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_assignedto_fk', 'artifact', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 669 (OID 45491931)
--
-- Name: "RI_ConstraintTrigger_45491930" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER INSERT OR UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');

--
-- TOC Entry ID 648 (OID 45491933)
--
-- Name: "RI_ConstraintTrigger_45491932" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER DELETE ON "artifact_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');

--
-- TOC Entry ID 649 (OID 45491935)
--
-- Name: "RI_ConstraintTrigger_45491934" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifact_resolutionid_fk" AFTER UPDATE ON "artifact_resolution"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifact_resolutionid_fk', 'artifact', 'artifact_resolution', 'FULL', 'resolution_id', 'id');

--
-- TOC Entry ID 679 (OID 45491937)
--
-- Name: "RI_ConstraintTrigger_45491936" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_history"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 670 (OID 45491939)
--
-- Name: "RI_ConstraintTrigger_45491938" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 671 (OID 45491941)
--
-- Name: "RI_ConstraintTrigger_45491940" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifacthistory_artifactid_fk', 'artifact_history', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 680 (OID 45491943)
--
-- Name: "RI_ConstraintTrigger_45491942" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER INSERT OR UPDATE ON "artifact_history"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');

--
-- TOC Entry ID 630 (OID 45491945)
--
-- Name: "RI_ConstraintTrigger_45491944" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');

--
-- TOC Entry ID 631 (OID 45491947)
--
-- Name: "RI_ConstraintTrigger_45491946" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifacthistory_modby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifacthistory_modby_fk', 'artifact_history', 'users', 'FULL', 'mod_by', 'user_id');

--
-- TOC Entry ID 681 (OID 45491949)
--
-- Name: "RI_ConstraintTrigger_45491948" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 672 (OID 45491951)
--
-- Name: "RI_ConstraintTrigger_45491950" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 673 (OID 45491953)
--
-- Name: "RI_ConstraintTrigger_45491952" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactfile_artifactid_fk', 'artifact_file', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 682 (OID 45491955)
--
-- Name: "RI_ConstraintTrigger_45491954" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 632 (OID 45491957)
--
-- Name: "RI_ConstraintTrigger_45491956" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 633 (OID 45491959)
--
-- Name: "RI_ConstraintTrigger_45491958" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactfile_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactfile_submittedby_fk', 'artifact_file', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 683 (OID 45491961)
--
-- Name: "RI_ConstraintTrigger_45491960" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_message"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 674 (OID 45491963)
--
-- Name: "RI_ConstraintTrigger_45491962" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 675 (OID 45491965)
--
-- Name: "RI_ConstraintTrigger_45491964" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmessage_artifactid_fk', 'artifact_message', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 684 (OID 45491967)
--
-- Name: "RI_ConstraintTrigger_45491966" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER INSERT OR UPDATE ON "artifact_message"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 634 (OID 45491969)
--
-- Name: "RI_ConstraintTrigger_45491968" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 635 (OID 45491971)
--
-- Name: "RI_ConstraintTrigger_45491970" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmessage_submittedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmessage_submittedby_fk', 'artifact_message', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 686 (OID 45491973)
--
-- Name: "RI_ConstraintTrigger_45491972" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER INSERT OR UPDATE ON "artifact_monitor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 676 (OID 45491975)
--
-- Name: "RI_ConstraintTrigger_45491974" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER DELETE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 677 (OID 45491977)
--
-- Name: "RI_ConstraintTrigger_45491976" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "artifactmonitor_artifactid_fk" AFTER UPDATE ON "artifact"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('artifactmonitor_artifactid_fk', 'artifact_monitor', 'artifact', 'FULL', 'artifact_id', 'artifact_id');

--
-- TOC Entry ID 647 (OID 45491978)
--
-- Name: artifactgrouplist_insert_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "artifactgrouplist_insert_trig" AFTER INSERT ON "artifact_group_list"  FOR EACH ROW EXECUTE PROCEDURE "artifactgrouplist_insert_agg" ();

--
-- TOC Entry ID 678 (OID 45491979)
--
-- Name: artifactgroup_update_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "artifactgroup_update_trig" AFTER UPDATE ON "artifact"  FOR EACH ROW EXECUTE PROCEDURE "artifactgroup_update_agg" ();

--
-- TOC Entry ID 570 (OID 45491980)
--
-- Name: forumgrouplist_insert_trig Type: TRIGGER Owner: tperdue
--

CREATE TRIGGER "forumgrouplist_insert_trig" AFTER INSERT ON "forum_group_list"  FOR EACH ROW EXECUTE PROCEDURE "forumgrouplist_insert_agg" ();

--
-- TOC Entry ID 571 (OID 45491982)
--
-- Name: "RI_ConstraintTrigger_45491981" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');

--
-- TOC Entry ID 582 (OID 45491984)
--
-- Name: "RI_ConstraintTrigger_45491983" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER DELETE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');

--
-- TOC Entry ID 583 (OID 45491986)
--
-- Name: "RI_ConstraintTrigger_45491985" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_releaseid_fk" AFTER UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_releaseid_fk', 'frs_file', 'frs_release', 'FULL', 'release_id', 'release_id');

--
-- TOC Entry ID 572 (OID 45491988)
--
-- Name: "RI_ConstraintTrigger_45491987" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');

--
-- TOC Entry ID 574 (OID 45491990)
--
-- Name: "RI_ConstraintTrigger_45491989" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER DELETE ON "frs_filetype"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');

--
-- TOC Entry ID 575 (OID 45491992)
--
-- Name: "RI_ConstraintTrigger_45491991" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_typeid_fk" AFTER UPDATE ON "frs_filetype"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_typeid_fk', 'frs_file', 'frs_filetype', 'FULL', 'type_id', 'type_id');

--
-- TOC Entry ID 573 (OID 45491994)
--
-- Name: "RI_ConstraintTrigger_45491993" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER INSERT OR UPDATE ON "frs_file"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');

--
-- TOC Entry ID 580 (OID 45491996)
--
-- Name: "RI_ConstraintTrigger_45491995" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER DELETE ON "frs_processor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');

--
-- TOC Entry ID 581 (OID 45491998)
--
-- Name: "RI_ConstraintTrigger_45491997" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsfile_processorid_fk" AFTER UPDATE ON "frs_processor"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsfile_processorid_fk', 'frs_file', 'frs_processor', 'FULL', 'processor_id', 'processor_id');

--
-- TOC Entry ID 576 (OID 45492000)
--
-- Name: "RI_ConstraintTrigger_45491999" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER INSERT OR UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 599 (OID 45492002)
--
-- Name: "RI_ConstraintTrigger_45492001" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER DELETE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 600 (OID 45492004)
--
-- Name: "RI_ConstraintTrigger_45492003" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_groupid_fk" AFTER UPDATE ON "groups"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frspackage_groupid_fk', 'frs_package', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 577 (OID 45492006)
--
-- Name: "RI_ConstraintTrigger_45492005" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER INSERT OR UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 587 (OID 45492008)
--
-- Name: "RI_ConstraintTrigger_45492007" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER DELETE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 588 (OID 45492010)
--
-- Name: "RI_ConstraintTrigger_45492009" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frspackage_statusid_fk" AFTER UPDATE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frspackage_statusid_fk', 'frs_package', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 584 (OID 45492012)
--
-- Name: "RI_ConstraintTrigger_45492011" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');

--
-- TOC Entry ID 578 (OID 45492014)
--
-- Name: "RI_ConstraintTrigger_45492013" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER DELETE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');

--
-- TOC Entry ID 579 (OID 45492016)
--
-- Name: "RI_ConstraintTrigger_45492015" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_packageid_fk" AFTER UPDATE ON "frs_package"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_packageid_fk', 'frs_release', 'frs_package', 'FULL', 'package_id', 'package_id');

--
-- TOC Entry ID 585 (OID 45492018)
--
-- Name: "RI_ConstraintTrigger_45492017" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 589 (OID 45492020)
--
-- Name: "RI_ConstraintTrigger_45492019" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER DELETE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 590 (OID 45492022)
--
-- Name: "RI_ConstraintTrigger_45492021" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_statusid_fk" AFTER UPDATE ON "frs_status"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_statusid_fk', 'frs_release', 'frs_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 586 (OID 45492024)
--
-- Name: "RI_ConstraintTrigger_45492023" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER INSERT OR UPDATE ON "frs_release"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_check_ins" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');

--
-- TOC Entry ID 636 (OID 45492026)
--
-- Name: "RI_ConstraintTrigger_45492025" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER DELETE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_del" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');

--
-- TOC Entry ID 637 (OID 45492028)
--
-- Name: "RI_ConstraintTrigger_45492027" Type: TRIGGER Owner: tperdue
--

CREATE CONSTRAINT TRIGGER "frsrelease_releasedby_fk" AFTER UPDATE ON "users"  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE "RI_FKey_noaction_upd" ('frsrelease_releasedby_fk', 'frs_release', 'users', 'FULL', 'released_by', 'user_id');

--
-- TOC Entry ID 687 (OID 45492029)
--
-- Name: forum_insert_agg Type: RULE Owner: tperdue
--

CREATE RULE forum_insert_agg AS ON INSERT TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count + 1) WHERE (forum_agg_msg_count.group_forum_id = new.group_forum_id);
--
-- TOC Entry ID 688 (OID 45492030)
--
-- Name: forum_delete_agg Type: RULE Owner: tperdue
--

CREATE RULE forum_delete_agg AS ON DELETE TO forum DO UPDATE forum_agg_msg_count SET count = (forum_agg_msg_count.count - 1) WHERE (forum_agg_msg_count.group_forum_id = old.group_forum_id);
--
-- TOC Entry ID 689 (OID 45492031)
--
-- Name: artifact_insert_agg Type: RULE Owner: tperdue
--

CREATE RULE artifact_insert_agg AS ON INSERT TO artifact DO UPDATE artifact_counts_agg SET count = (artifact_counts_agg.count + 1), open_count = (artifact_counts_agg.open_count + 1) WHERE (artifact_counts_agg.group_artifact_id = new.group_artifact_id);
--
-- TOC Entry ID 3 (OID 45490495)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"canned_responses_pk_seq"', 1, false);

--
-- TOC Entry ID 5 (OID 45490503)
--
-- Name: db_images_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"db_images_pk_seq"', 1, false);

--
-- TOC Entry ID 7 (OID 45490511)
--
-- Name: doc_data_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"doc_data_pk_seq"', 1, false);

--
-- TOC Entry ID 9 (OID 45490519)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"doc_groups_pk_seq"', 1, false);

--
-- TOC Entry ID 11 (OID 45490524)
--
-- Name: doc_states_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"doc_states_pk_seq"', 1, false);

--
-- TOC Entry ID 13 (OID 45490529)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"filemodule_monitor_pk_seq"', 1, false);

--
-- TOC Entry ID 15 (OID 45490534)
--
-- Name: forum_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"forum_pk_seq"', 1, false);

--
-- TOC Entry ID 17 (OID 45490545)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"forum_group_list_pk_seq"', 1, false);

--
-- TOC Entry ID 19 (OID 45490553)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"forum_monitored_forums_pk_seq"', 1, false);

--
-- TOC Entry ID 21 (OID 45490558)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"forum_saved_place_pk_seq"', 1, false);

--
-- TOC Entry ID 23 (OID 45490563)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"foundry_news_pk_seq"', 1, false);

--
-- TOC Entry ID 25 (OID 45490565)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"foundry_preferred_projec_pk_seq"', 1, false);

--
-- TOC Entry ID 27 (OID 45490567)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"foundry_projects_pk_seq"', 1, false);

--
-- TOC Entry ID 29 (OID 45490569)
--
-- Name: frs_file_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_file_pk_seq"', 1, false);

--
-- TOC Entry ID 31 (OID 45490577)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_filetype_pk_seq"', 9999, true);

--
-- TOC Entry ID 33 (OID 45490585)
--
-- Name: frs_package_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_package_pk_seq"', 1, false);

--
-- TOC Entry ID 35 (OID 45490593)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_processor_pk_seq"', 9999, true);

--
-- TOC Entry ID 37 (OID 45490601)
--
-- Name: frs_release_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_release_pk_seq"', 1, false);

--
-- TOC Entry ID 39 (OID 45490609)
--
-- Name: frs_status_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"frs_status_pk_seq"', 3, true);

--
-- TOC Entry ID 41 (OID 45490617)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"group_cvs_history_pk_seq"', 1, false);

--
-- TOC Entry ID 43 (OID 45490619)
--
-- Name: group_history_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"group_history_pk_seq"', 1, false);

--
-- TOC Entry ID 45 (OID 45490627)
--
-- Name: group_type_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"group_type_pk_seq"', 1, false);

--
-- TOC Entry ID 47 (OID 45490635)
--
-- Name: groups_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"groups_pk_seq"', 4, true);

--
-- TOC Entry ID 49 (OID 45490649)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"mail_group_list_pk_seq"', 1, false);

--
-- TOC Entry ID 51 (OID 45490657)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"news_bytes_pk_seq"', 1, false);

--
-- TOC Entry ID 53 (OID 45490665)
--
-- Name: people_job_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_job_pk_seq"', 1, false);

--
-- TOC Entry ID 55 (OID 45490673)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_job_category_pk_seq"', 7, true);

--
-- TOC Entry ID 57 (OID 45490681)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_job_inventory_pk_seq"', 1, false);

--
-- TOC Entry ID 59 (OID 45490686)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_job_status_pk_seq"', 1, false);

--
-- TOC Entry ID 61 (OID 45490694)
--
-- Name: people_skill_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_skill_pk_seq"', 1, false);

--
-- TOC Entry ID 63 (OID 45490702)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_skill_inventory_pk_seq"', 1, false);

--
-- TOC Entry ID 65 (OID 45490707)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_skill_level_pk_seq"', 5, true);

--
-- TOC Entry ID 67 (OID 45490715)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"people_skill_year_pk_seq"', 5, true);

--
-- TOC Entry ID 69 (OID 45490723)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_assigned_to_pk_seq"', 1, false);

--
-- TOC Entry ID 71 (OID 45490728)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_dependencies_pk_seq"', 1, false);

--
-- TOC Entry ID 73 (OID 45490733)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_group_list_pk_seq"', 1, true);

--
-- TOC Entry ID 75 (OID 45490741)
--
-- Name: project_history_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_history_pk_seq"', 1, false);

--
-- TOC Entry ID 77 (OID 45490749)
--
-- Name: project_metric_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_metric_pk_seq"', 1, false);

--
-- TOC Entry ID 79 (OID 45490754)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_metric_tmp1_pk_seq"', 1, false);

--
-- TOC Entry ID 81 (OID 45490759)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_metric_weekly_tm_pk_seq"', 1, false);

--
-- TOC Entry ID 83 (OID 45490761)
--
-- Name: project_status_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_status_pk_seq"', 1, false);

--
-- TOC Entry ID 85 (OID 45490769)
--
-- Name: project_task_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_task_pk_seq"', 1, true);

--
-- TOC Entry ID 87 (OID 45490777)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_weekly_metric_pk_seq"', 1, false);

--
-- TOC Entry ID 89 (OID 45490784)
--
-- Name: snippet_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"snippet_pk_seq"', 1, false);

--
-- TOC Entry ID 91 (OID 45490792)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"snippet_package_pk_seq"', 1, false);

--
-- TOC Entry ID 93 (OID 45490800)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"snippet_package_item_pk_seq"', 1, false);

--
-- TOC Entry ID 95 (OID 45490805)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"snippet_package_version_pk_seq"', 1, false);

--
-- TOC Entry ID 97 (OID 45490813)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"snippet_version_pk_seq"', 1, false);

--
-- TOC Entry ID 99 (OID 45490829)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"supported_languages_pk_seq"', 23, true);

--
-- TOC Entry ID 101 (OID 45490837)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"survey_question_types_pk_seq"', 1, false);

--
-- TOC Entry ID 103 (OID 45490845)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"survey_questions_pk_seq"', 1, false);

--
-- TOC Entry ID 105 (OID 45490862)
--
-- Name: surveys_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"surveys_pk_seq"', 1, false);

--
-- TOC Entry ID 107 (OID 45490870)
--
-- Name: system_history_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"system_history_pk_seq"', 1, false);

--
-- TOC Entry ID 109 (OID 45490872)
--
-- Name: system_machines_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"system_machines_pk_seq"', 1, false);

--
-- TOC Entry ID 111 (OID 45490874)
--
-- Name: system_news_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"system_news_pk_seq"', 1, false);

--
-- TOC Entry ID 113 (OID 45490876)
--
-- Name: system_services_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"system_services_pk_seq"', 1, false);

--
-- TOC Entry ID 115 (OID 45490878)
--
-- Name: system_status_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"system_status_pk_seq"', 1, false);

--
-- TOC Entry ID 117 (OID 45490880)
--
-- Name: themes_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"themes_pk_seq"', 1, true);

--
-- TOC Entry ID 119 (OID 45490884)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"trove_cat_pk_seq"', 305, true);

--
-- TOC Entry ID 121 (OID 45490892)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"trove_group_link_pk_seq"', 1, false);

--
-- TOC Entry ID 123 (OID 45490897)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"trove_treesums_pk_seq"', 1, false);

--
-- TOC Entry ID 125 (OID 45490899)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_bookmarks_pk_seq"', 1, false);

--
-- TOC Entry ID 127 (OID 45490907)
--
-- Name: user_diary_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_diary_pk_seq"', 1, false);

--
-- TOC Entry ID 129 (OID 45490915)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_diary_monitor_pk_seq"', 1, false);

--
-- TOC Entry ID 131 (OID 45490920)
--
-- Name: user_group_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_group_pk_seq"', 1, false);

--
-- TOC Entry ID 133 (OID 45490925)
--
-- Name: user_metric_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_metric_pk_seq"', 1, false);

--
-- TOC Entry ID 135 (OID 45490930)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"user_metric0_pk_seq"', 1, false);

--
-- TOC Entry ID 137 (OID 45490942)
--
-- Name: users_pk_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"users_pk_seq"', 101, true);

--
-- TOC Entry ID 139 (OID 45490950)
--
-- Name: unix_uid_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"unix_uid_seq"', 1, false);

--
-- TOC Entry ID 141 (OID 45490952)
--
-- Name: forum_thread_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"forum_thread_seq"', 1, false);

--
-- TOC Entry ID 143 (OID 45490956)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"project_metric_wee_ranking1_seq"', 1, false);

--
-- TOC Entry ID 145 (OID 45490958)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"prdb_dbs_dbid_seq"', 1, false);

--
-- TOC Entry ID 147 (OID 45490977)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"prweb_vhost_vhostid_seq"', 1, false);

--
-- TOC Entry ID 149 (OID 45490985)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_grou_group_artifac_seq"', 100, true);

--
-- TOC Entry ID 151 (OID 45490993)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_resolution_id_seq"', 101, true);

--
-- TOC Entry ID 153 (OID 45491001)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_perm_id_seq"', 1, false);

--
-- TOC Entry ID 155 (OID 45491012)
--
-- Name: artifact_category_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_category_id_seq"', 100, true);

--
-- TOC Entry ID 157 (OID 45491020)
--
-- Name: artifact_group_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_group_id_seq"', 1, false);

--
-- TOC Entry ID 159 (OID 45491028)
--
-- Name: artifact_status_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_status_id_seq"', 3, true);

--
-- TOC Entry ID 161 (OID 45491036)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_artifact_id_seq"', 1, false);

--
-- TOC Entry ID 163 (OID 45491047)
--
-- Name: artifact_history_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_history_id_seq"', 1, false);

--
-- TOC Entry ID 165 (OID 45491058)
--
-- Name: artifact_file_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_file_id_seq"', 1, false);

--
-- TOC Entry ID 167 (OID 45491069)
--
-- Name: artifact_message_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_message_id_seq"', 1, false);

--
-- TOC Entry ID 169 (OID 45491080)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_monitor_id_seq"', 1, false);

--
-- TOC Entry ID 171 (OID 45491088)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"artifact_canned_response_id_seq"', 1, false);

--
-- TOC Entry ID 173 (OID 45491103)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"massmail_queue_id_seq"', 1, false);

--
-- TOC Entry ID 175 (OID 45491178)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE SET Owner: tperdue
--

SELECT setval ('"trove_treesum_trove_treesum_seq"', 1, false);


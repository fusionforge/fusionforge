--
-- Selected TOC Entries:
--
DROP TABLE "cache_store";
--
-- TOC Entry ID 2 (OID 469439)
--
-- Name: cache_store Type: TABLE Owner: sourceforge
--

CREATE TABLE "cache_store" (
	"name" character varying(255) NOT NULL,
	"data" text,
	"indate" integer DEFAULT 0 NOT NULL,
	Constraint "cache_store_pkey" Primary Key ("name")
);

--
-- Data for TOC Entry ID 3 (OID 469439)
--
-- Name: cache_store Type: TABLE DATA Owner: sourceforge
--


--
-- Selected TOC Entries:
--
DROP INDEX "foundryprojdlsagg_foundryid_dls";
DROP TABLE "foundry_project_downloads_agg";
--
-- TOC Entry ID 2 (OID 469499)
--
-- Name: foundry_project_downloads_agg Type: TABLE Owner: sourceforge
--

CREATE TABLE "foundry_project_downloads_agg" (
	"foundry_id" integer,
	"downloads" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30)
);

--
-- Data for TOC Entry ID 4 (OID 469499)
--
-- Name: foundry_project_downloads_agg Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469499)
--
-- Name: "foundryprojdlsagg_foundryid_dls" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "foundryprojdlsagg_foundryid_dls" on "foundry_project_downloads_agg" using btree ( "foundry_id" "int4_ops", "downloads" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP INDEX "foundryprojectrankingsagg_found";
DROP TABLE "foundry_project_rankings_agg";
--
-- TOC Entry ID 2 (OID 469484)
--
-- Name: foundry_project_rankings_agg Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 4 (OID 469484)
--
-- Name: foundry_project_rankings_agg Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469484)
--
-- Name: "foundryprojectrankingsagg_found" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "foundryprojectrankingsagg_found" on "foundry_project_rankings_agg" using btree ( "foundry_id" "int4_ops", "ranking" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP INDEX "project_metric_group";
DROP TABLE "project_metric";
--
-- TOC Entry ID 2 (OID 466426)
--
-- Name: project_metric Type: TABLE Owner: sourceforge
--

CREATE TABLE "project_metric" (
	"ranking" integer DEFAULT nextval('project_metric_pk_seq'::text) NOT NULL,
	"percentile" double precision,
	"group_id" integer DEFAULT '0' NOT NULL,
	Constraint "project_metric_pkey" Primary Key ("ranking")
);

--
-- Data for TOC Entry ID 4 (OID 466426)
--
-- Name: project_metric Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 466426)
--
-- Name: "project_metric_group" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "project_metric_group" on "project_metric" using btree ( "group_id" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP TABLE "project_metric_tmp1";
--
-- TOC Entry ID 2 (OID 466462)
--
-- Name: project_metric_tmp1 Type: TABLE Owner: sourceforge
--

CREATE TABLE "project_metric_tmp1" (
	"ranking" integer DEFAULT nextval('project_metric_tmp1_pk_seq'::text) NOT NULL,
	"group_id" integer DEFAULT '0' NOT NULL,
	"value" double precision,
	Constraint "project_metric_tmp1_pkey" Primary Key ("ranking")
);

--
-- Data for TOC Entry ID 3 (OID 466462)
--
-- Name: project_metric_tmp1 Type: TABLE DATA Owner: sourceforge
--


--
-- Selected TOC Entries:
--
DROP INDEX "statsprojectall_groupid";
DROP TABLE "stats_project_all";
--
-- TOC Entry ID 2 (OID 469584)
--
-- Name: stats_project_all Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 4 (OID 469584)
--
-- Name: stats_project_all Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469584)
--
-- Name: "statsprojectall_groupid" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statsprojectall_groupid" on "stats_project_all" using btree ( "group_id" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP TABLE "stats_project_developers_last30";
--
-- TOC Entry ID 2 (OID 469618)
--
-- Name: stats_project_developers_last30 Type: TABLE Owner: sourceforge
--

CREATE TABLE "stats_project_developers_last30" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"developers" integer
);

--
-- Data for TOC Entry ID 3 (OID 469618)
--
-- Name: stats_project_developers_last30 Type: TABLE DATA Owner: sourceforge
--


--
-- Selected TOC Entries:
--
DROP INDEX "statsproject30_groupid";
DROP TABLE "stats_project_last_30";
--
-- TOC Entry ID 2 (OID 469631)
--
-- Name: stats_project_last_30 Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 4 (OID 469631)
--
-- Name: stats_project_last_30 Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469631)
--
-- Name: "statsproject30_groupid" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statsproject30_groupid" on "stats_project_last_30" using btree ( "group_id" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP INDEX "statsprojectmonths_groupid_mont";
DROP INDEX "statsprojectmonths_groupid";
DROP TABLE "stats_project_months";
--
-- TOC Entry ID 2 (OID 469548)
--
-- Name: stats_project_months Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 5 (OID 469548)
--
-- Name: stats_project_months Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469548)
--
-- Name: "statsprojectmonths_groupid" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statsprojectmonths_groupid" on "stats_project_months" using btree ( "group_id" "int4_ops" );

--
-- TOC Entry ID 4 (OID 469548)
--
-- Name: "statsprojectmonths_groupid_mont" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statsprojectmonths_groupid_mont" on "stats_project_months" using btree ( "group_id" "int4_ops", "month" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP TABLE "stats_site_all";
--
-- TOC Entry ID 2 (OID 469736)
--
-- Name: stats_site_all Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 3 (OID 469736)
--
-- Name: stats_site_all Type: TABLE DATA Owner: sourceforge
--


INSERT INTO "stats_site_all" VALUES (NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
--
-- Selected TOC Entries:
--
DROP INDEX "statssitelast30_month_day";
DROP TABLE "stats_site_last_30";
--
-- TOC Entry ID 2 (OID 469679)
--
-- Name: stats_site_last_30 Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 4 (OID 469679)
--
-- Name: stats_site_last_30 Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469679)
--
-- Name: "statssitelast30_month_day" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statssitelast30_month_day" on "stats_site_last_30" using btree ( "month" "int4_ops", "day" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP INDEX "statssitemonths_month";
DROP TABLE "stats_site_months";
--
-- TOC Entry ID 2 (OID 469708)
--
-- Name: stats_site_months Type: TABLE Owner: sourceforge
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
-- Data for TOC Entry ID 4 (OID 469708)
--
-- Name: stats_site_months Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469708)
--
-- Name: "statssitemonths_month" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statssitemonths_month" on "stats_site_months" using btree ( "month" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP INDEX "statssitepagesbyday_month_day";
DROP INDEX "statssitepgsbyday_oid";
DROP TABLE "stats_site_pages_by_day";
--
-- TOC Entry ID 2 (OID 469048)
--
-- Name: stats_site_pages_by_day Type: TABLE Owner: sourceforge
--

CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);

--
-- Data for TOC Entry ID 5 (OID 469048)
--
-- Name: stats_site_pages_by_day Type: TABLE DATA Owner: sourceforge
--


--
-- TOC Entry ID 3 (OID 469048)
--
-- Name: "statssitepgsbyday_oid" Type: INDEX Owner: sourceforge
--

CREATE UNIQUE INDEX "statssitepgsbyday_oid" on "stats_site_pages_by_day" using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 4 (OID 469048)
--
-- Name: "statssitepagesbyday_month_day" Type: INDEX Owner: sourceforge
--

CREATE  INDEX "statssitepagesbyday_month_day" on "stats_site_pages_by_day" using btree ( "month" "int4_ops", "day" "int4_ops" );

--
-- Selected TOC Entries:
--
DROP TABLE "stats_site_pages_by_month";
--
-- TOC Entry ID 2 (OID 469668)
--
-- Name: stats_site_pages_by_month Type: TABLE Owner: sourceforge
--

CREATE TABLE "stats_site_pages_by_month" (
	"month" integer,
	"site_page_views" integer
);

--
-- Data for TOC Entry ID 3 (OID 469668)
--
-- Name: stats_site_pages_by_month Type: TABLE DATA Owner: sourceforge
--


--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_artifact_id_seq";
--
-- TOC Entry ID 2 (OID 468604)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468604)
--
-- Name: artifact_artifact_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_artifact_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_canned_response_id_seq";
--
-- TOC Entry ID 2 (OID 468985)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468985)
--
-- Name: artifact_canned_response_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_canned_response_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_category_id_seq";
--
-- TOC Entry ID 2 (OID 468453)
--
-- Name: artifact_category_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468453)
--
-- Name: artifact_category_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_category_id_seq"', 100, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_file_id_seq";
--
-- TOC Entry ID 2 (OID 468783)
--
-- Name: artifact_file_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468783)
--
-- Name: artifact_file_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_file_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_grou_group_artifac_seq";
--
-- TOC Entry ID 2 (OID 468268)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468268)
--
-- Name: artifact_grou_group_artifac_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_grou_group_artifac_seq"', 100, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_group_id_seq";
--
-- TOC Entry ID 2 (OID 468505)
--
-- Name: artifact_group_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468505)
--
-- Name: artifact_group_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_group_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_history_id_seq";
--
-- TOC Entry ID 2 (OID 468709)
--
-- Name: artifact_history_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468709)
--
-- Name: artifact_history_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_history_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_message_id_seq";
--
-- TOC Entry ID 2 (OID 468861)
--
-- Name: artifact_message_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468861)
--
-- Name: artifact_message_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_message_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_monitor_id_seq";
--
-- TOC Entry ID 2 (OID 468934)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468934)
--
-- Name: artifact_monitor_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_monitor_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_perm_id_seq";
--
-- TOC Entry ID 2 (OID 468384)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468384)
--
-- Name: artifact_perm_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_perm_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_resolution_id_seq";
--
-- TOC Entry ID 2 (OID 468335)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468335)
--
-- Name: artifact_resolution_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_resolution_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "artifact_status_id_seq";
--
-- TOC Entry ID 2 (OID 468555)
--
-- Name: artifact_status_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468555)
--
-- Name: artifact_status_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"artifact_status_id_seq"', 3, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "canned_responses_pk_seq";
--
-- TOC Entry ID 2 (OID 464514)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "canned_responses_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464514)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"canned_responses_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "db_images_pk_seq";
--
-- TOC Entry ID 2 (OID 464564)
--
-- Name: db_images_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "db_images_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464564)
--
-- Name: db_images_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"db_images_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "doc_data_pk_seq";
--
-- TOC Entry ID 2 (OID 464630)
--
-- Name: doc_data_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "doc_data_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464630)
--
-- Name: doc_data_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_data_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "doc_groups_pk_seq";
--
-- TOC Entry ID 2 (OID 464695)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "doc_groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464695)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_groups_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "doc_states_pk_seq";
--
-- TOC Entry ID 2 (OID 464732)
--
-- Name: doc_states_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "doc_states_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464732)
--
-- Name: doc_states_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"doc_states_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "filemodule_monitor_pk_seq";
--
-- TOC Entry ID 2 (OID 464767)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "filemodule_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464767)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"filemodule_monitor_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "forum_group_list_pk_seq";
--
-- TOC Entry ID 2 (OID 464886)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "forum_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464886)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_group_list_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "forum_monitored_forums_pk_seq";
--
-- TOC Entry ID 2 (OID 464944)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "forum_monitored_forums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464944)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_monitored_forums_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "forum_pk_seq";
--
-- TOC Entry ID 2 (OID 464804)
--
-- Name: forum_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "forum_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464804)
--
-- Name: forum_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "forum_saved_place_pk_seq";
--
-- TOC Entry ID 2 (OID 464981)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "forum_saved_place_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 464981)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_saved_place_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "forum_thread_seq";
--
-- TOC Entry ID 2 (OID 468052)
--
-- Name: forum_thread_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "forum_thread_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468052)
--
-- Name: forum_thread_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"forum_thread_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "foundry_news_pk_seq";
--
-- TOC Entry ID 2 (OID 465058)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "foundry_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465058)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_news_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "foundry_preferred_projec_pk_seq";
--
-- TOC Entry ID 2 (OID 465099)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "foundry_preferred_projec_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465099)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_preferred_projec_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "foundry_projects_pk_seq";
--
-- TOC Entry ID 2 (OID 465138)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "foundry_projects_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465138)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"foundry_projects_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_file_pk_seq";
--
-- TOC Entry ID 2 (OID 465175)
--
-- Name: frs_file_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_file_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465175)
--
-- Name: frs_file_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_file_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_filetype_pk_seq";
--
-- TOC Entry ID 2 (OID 465236)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_filetype_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465236)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_filetype_pk_seq"', 9999, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_package_pk_seq";
--
-- TOC Entry ID 2 (OID 465285)
--
-- Name: frs_package_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465285)
--
-- Name: frs_package_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_package_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_processor_pk_seq";
--
-- TOC Entry ID 2 (OID 465338)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_processor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465338)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_processor_pk_seq"', 9999, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_release_pk_seq";
--
-- TOC Entry ID 2 (OID 465387)
--
-- Name: frs_release_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_release_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465387)
--
-- Name: frs_release_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_release_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "frs_status_pk_seq";
--
-- TOC Entry ID 2 (OID 465448)
--
-- Name: frs_status_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "frs_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465448)
--
-- Name: frs_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"frs_status_pk_seq"', 3, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "group_cvs_history_pk_seq";
--
-- TOC Entry ID 2 (OID 465497)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "group_cvs_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465497)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_cvs_history_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "group_history_pk_seq";
--
-- TOC Entry ID 2 (OID 465516)
--
-- Name: group_history_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "group_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465516)
--
-- Name: group_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_history_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "groups_pk_seq";
--
-- TOC Entry ID 2 (OID 465622)
--
-- Name: groups_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "groups_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465622)
--
-- Name: groups_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"groups_pk_seq"', 4, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "group_type_pk_seq";
--
-- TOC Entry ID 2 (OID 465573)
--
-- Name: group_type_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "group_type_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465573)
--
-- Name: group_type_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"group_type_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "mail_group_list_pk_seq";
--
-- TOC Entry ID 2 (OID 465712)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "mail_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465712)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"mail_group_list_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "massmail_queue_id_seq";
--
-- TOC Entry ID 2 (OID 469063)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 469063)
--
-- Name: massmail_queue_id_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"massmail_queue_id_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "news_bytes_pk_seq";
--
-- TOC Entry ID 2 (OID 465771)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "news_bytes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465771)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"news_bytes_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_job_category_pk_seq";
--
-- TOC Entry ID 2 (OID 465891)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_job_category_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465891)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_category_pk_seq"', 7, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_job_inventory_pk_seq";
--
-- TOC Entry ID 2 (OID 465942)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_job_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465942)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_inventory_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_job_pk_seq";
--
-- TOC Entry ID 2 (OID 465831)
--
-- Name: people_job_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_job_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465831)
--
-- Name: people_job_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_job_status_pk_seq";
--
-- TOC Entry ID 2 (OID 465983)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_job_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 465983)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_job_status_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_skill_inventory_pk_seq";
--
-- TOC Entry ID 2 (OID 466081)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_skill_inventory_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466081)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_inventory_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_skill_level_pk_seq";
--
-- TOC Entry ID 2 (OID 466122)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_skill_level_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466122)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_level_pk_seq"', 5, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_skill_pk_seq";
--
-- TOC Entry ID 2 (OID 466032)
--
-- Name: people_skill_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_skill_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466032)
--
-- Name: people_skill_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_pk_seq"', 9, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "people_skill_year_pk_seq";
--
-- TOC Entry ID 2 (OID 466171)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "people_skill_year_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466171)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"people_skill_year_pk_seq"', 5, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "prdb_dbs_dbid_seq";
--
-- TOC Entry ID 2 (OID 468104)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468104)
--
-- Name: prdb_dbs_dbid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"prdb_dbs_dbid_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_assigned_to_pk_seq";
--
-- TOC Entry ID 2 (OID 466220)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_assigned_to_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466220)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_assigned_to_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_dependencies_pk_seq";
--
-- TOC Entry ID 2 (OID 466257)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_dependencies_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466257)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_dependencies_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_group_list_pk_seq";
--
-- TOC Entry ID 2 (OID 466294)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_group_list_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466294)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_group_list_pk_seq"', 1, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_history_pk_seq";
--
-- TOC Entry ID 2 (OID 466349)
--
-- Name: project_history_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466349)
--
-- Name: project_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_history_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_metric_pk_seq";
--
-- TOC Entry ID 2 (OID 466407)
--
-- Name: project_metric_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466407)
--
-- Name: project_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_metric_tmp1_pk_seq";
--
-- TOC Entry ID 2 (OID 466443)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_metric_tmp1_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466443)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_tmp1_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_metric_weekly_tm_pk_seq";
--
-- TOC Entry ID 2 (OID 466479)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_metric_weekly_tm_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466479)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_weekly_tm_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_metric_wee_ranking1_seq";
--
-- TOC Entry ID 2 (OID 468085)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_metric_wee_ranking1_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468085)
--
-- Name: project_metric_wee_ranking1_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_metric_wee_ranking1_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_status_pk_seq";
--
-- TOC Entry ID 2 (OID 466498)
--
-- Name: project_status_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466498)
--
-- Name: project_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_status_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_task_pk_seq";
--
-- TOC Entry ID 2 (OID 466548)
--
-- Name: project_task_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_task_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466548)
--
-- Name: project_task_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_task_pk_seq"', 1, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "project_weekly_metric_pk_seq";
--
-- TOC Entry ID 2 (OID 466616)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "project_weekly_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466616)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"project_weekly_metric_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "prweb_vhost_vhostid_seq";
--
-- TOC Entry ID 2 (OID 468216)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468216)
--
-- Name: prweb_vhost_vhostid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"prweb_vhost_vhostid_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "snippet_package_item_pk_seq";
--
-- TOC Entry ID 2 (OID 466785)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "snippet_package_item_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466785)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_item_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "snippet_package_pk_seq";
--
-- TOC Entry ID 2 (OID 466729)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "snippet_package_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466729)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "snippet_package_version_pk_seq";
--
-- TOC Entry ID 2 (OID 466822)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "snippet_package_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466822)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_package_version_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "snippet_pk_seq";
--
-- TOC Entry ID 2 (OID 466669)
--
-- Name: snippet_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "snippet_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466669)
--
-- Name: snippet_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "snippet_version_pk_seq";
--
-- TOC Entry ID 2 (OID 466878)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "snippet_version_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466878)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"snippet_version_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "supported_languages_pk_seq";
--
-- TOC Entry ID 2 (OID 466993)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "supported_languages_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 466993)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"supported_languages_pk_seq"', 23, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "survey_questions_pk_seq";
--
-- TOC Entry ID 2 (OID 467095)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "survey_questions_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467095)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"survey_questions_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "survey_question_types_pk_seq";
--
-- TOC Entry ID 2 (OID 467045)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "survey_question_types_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467045)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"survey_question_types_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "surveys_pk_seq";
--
-- TOC Entry ID 2 (OID 467221)
--
-- Name: surveys_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "surveys_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467221)
--
-- Name: surveys_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"surveys_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "system_history_pk_seq";
--
-- TOC Entry ID 2 (OID 467277)
--
-- Name: system_history_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "system_history_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467277)
--
-- Name: system_history_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_history_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "system_machines_pk_seq";
--
-- TOC Entry ID 2 (OID 467296)
--
-- Name: system_machines_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "system_machines_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467296)
--
-- Name: system_machines_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_machines_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "system_news_pk_seq";
--
-- TOC Entry ID 2 (OID 467315)
--
-- Name: system_news_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "system_news_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467315)
--
-- Name: system_news_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_news_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "system_services_pk_seq";
--
-- TOC Entry ID 2 (OID 467334)
--
-- Name: system_services_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "system_services_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467334)
--
-- Name: system_services_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_services_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "system_status_pk_seq";
--
-- TOC Entry ID 2 (OID 467353)
--
-- Name: system_status_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "system_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467353)
--
-- Name: system_status_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"system_status_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "themes_pk_seq";
--
-- TOC Entry ID 2 (OID 467400)
--
-- Name: themes_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "themes_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467400)
--
-- Name: themes_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"themes_pk_seq"', 1, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "trove_cat_pk_seq";
--
-- TOC Entry ID 2 (OID 467477)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "trove_cat_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467477)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_cat_pk_seq"', 305, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "trove_group_link_pk_seq";
--
-- TOC Entry ID 2 (OID 467541)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "trove_group_link_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467541)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_group_link_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "trove_treesums_pk_seq";
--
-- TOC Entry ID 2 (OID 467582)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467582)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_treesums_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "trove_treesum_trove_treesum_seq";
--
-- TOC Entry ID 2 (OID 469781)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "trove_treesum_trove_treesum_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 469781)
--
-- Name: trove_treesum_trove_treesum_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"trove_treesum_trove_treesum_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "unix_uid_seq";
--
-- TOC Entry ID 2 (OID 468033)
--
-- Name: unix_uid_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "unix_uid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 468033)
--
-- Name: unix_uid_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"unix_uid_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_bookmarks_pk_seq";
--
-- TOC Entry ID 2 (OID 467601)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_bookmarks_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467601)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_bookmarks_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_diary_monitor_pk_seq";
--
-- TOC Entry ID 2 (OID 467709)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_diary_monitor_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467709)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_diary_monitor_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_diary_pk_seq";
--
-- TOC Entry ID 2 (OID 467653)
--
-- Name: user_diary_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_diary_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467653)
--
-- Name: user_diary_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_diary_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_group_pk_seq";
--
-- TOC Entry ID 2 (OID 467746)
--
-- Name: user_group_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_group_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467746)
--
-- Name: user_group_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_group_pk_seq"', 4, 't');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_metric0_pk_seq";
--
-- TOC Entry ID 2 (OID 467851)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_metric0_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467851)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_metric0_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "user_metric_pk_seq";
--
-- TOC Entry ID 2 (OID 467804)
--
-- Name: user_metric_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "user_metric_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467804)
--
-- Name: user_metric_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"user_metric_pk_seq"', 1, 'f');

--
-- Selected TOC Entries:
--
DROP SEQUENCE "users_pk_seq";
--
-- TOC Entry ID 2 (OID 467946)
--
-- Name: users_pk_seq Type: SEQUENCE Owner: sourceforge
--

CREATE SEQUENCE "users_pk_seq" start 102 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 3 (OID 467946)
--
-- Name: users_pk_seq Type: SEQUENCE SET Owner:
--

SELECT setval ('"users_pk_seq"', 102, 'f');




DROP VIEW "artifact_file_user_vw";
--
-- TOC Entry ID 2 (OID 468860)
--
-- Name: artifact_file_user_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifact_file_user_vw" as SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname FROM artifact_file af, users WHERE (af.submitted_by = users.user_id);

--
-- Selected TOC Entries:
--
DROP VIEW "artifact_history_user_vw";
--
-- TOC Entry ID 2 (OID 468782)
--
-- Name: artifact_history_user_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifact_history_user_vw" as SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name FROM artifact_history ah, users WHERE (ah.mod_by = users.user_id);

--
-- Selected TOC Entries:
--
DROP VIEW "artifact_message_user_vw";
--
-- TOC Entry ID 2 (OID 468933)
--
-- Name: artifact_message_user_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifact_message_user_vw" as SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname FROM artifact_message am, users WHERE (am.submitted_by = users.user_id);

--
-- Selected TOC Entries:
--
DROP VIEW "artifactperm_artgrouplist_vw";
--
-- TOC Entry ID 2 (OID 468452)
--
-- Name: artifactperm_artgrouplist_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifactperm_artgrouplist_vw" as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id);

--
-- Selected TOC Entries:
--
DROP VIEW "artifactperm_user_vw";
--
-- TOC Entry ID 2 (OID 468436)
--
-- Name: artifactperm_user_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifactperm_user_vw" as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id);

--
-- Selected TOC Entries:
--
DROP VIEW "artifact_vw";
--
-- TOC Entry ID 2 (OID 468705)
--
-- Name: artifact_vw Type: VIEW Owner: sourceforge
--

CREATE VIEW "artifact_vw" as SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.category_id, artifact.artifact_group_id, artifact.resolution_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact_category.category_name, artifact_group.group_name, artifact_resolution.resolution_name FROM users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution WHERE ((((((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id)) AND (artifact.category_id = artifact_category.id)) AND (artifact.artifact_group_id = artifact_group.id)) AND (artifact.resolution_id = artifact_resolution.id));


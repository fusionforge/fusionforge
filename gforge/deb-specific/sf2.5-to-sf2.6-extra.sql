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
-- TOC Entry ID 456 (OID 25728)
--
-- Name: "foundryprojdlsagg_foundryid_dls" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundryprojdlsagg_foundryid_dls" on "foundry_project_downloads_agg" using btree ( "foundry_id" "int4_ops", "downloads" "int4_ops" );


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
-- TOC Entry ID 455 (OID 25710)
--
-- Name: "foundryprojectrankingsagg_found" Type: INDEX Owner: tperdue
--

CREATE  INDEX "foundryprojectrankingsagg_found" on "foundry_project_rankings_agg" using btree ( "foundry_id" "int4_ops", "ranking" "int4_ops" );

--
-- TOC Entry ID 436 (OID 23388)
--
-- Name: "frsdlfileagg_oid" Type: INDEX Owner: tperdue
--

CREATE UNIQUE INDEX "frsdlfileagg_oid" on "frs_dlstats_file_agg" using btree ( "oid" "oid_ops" );

--
-- TOC Entry ID 457 (OID 25745)
--
-- Name: "frsdlfiletotal_fileid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlfiletotal_fileid" on "frs_dlstats_filetotal_agg" using btree ( "file_id" "int4_ops" );

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
-- TOC Entry ID 458 (OID 25759)
--
-- Name: "frsdlgrouptotal_groupid" Type: INDEX Owner: tperdue
--

CREATE  INDEX "frsdlgrouptotal_groupid" on "frs_dlstats_grouptotal_agg" using btree ( "group_id" "int4_ops" );

-- A VOIR
-- project_metric
-- project_metric_group
-- project_metric_pkey

-- project_metric_tmp1
-- project_metric_tmp1_pkey

-- project_metric_wee_ranking1_seq

-- statsagglogobygrp_oid
-- statsaggsitebygrp_oid

-- statscvsgrp_oid

-- statsproject30_groupid
-- stats_project_all
-- statsprojectall_groupid

-- stats_project_developers_last30
-- statsprojectdevelop_oid

-- statsprojectall_groupid
-- statsprojectmetric_oid

-- stats_project_months
-- statsprojectmonths_groupid
-- statsprojectmonths_groupid_mont
-- statsproject_oid

-- stats_site_all
-- stats_site_last_30
-- statssitelast30_month_day

-- stats_site_months
-- statssitemonths_month
-- statssite_oid
-- stats_site_pages_by_day
-- statssitepagesbyday_month_day
-- stats_site_pages_by_month
-- statssitepgsbyday_oid

-- statssubdpages_oid

-- troveagg_trovecatid_ranking
-- trove_treesum_trove_treesum_seq

-- A SUPPRIMER ?
-- frs_dlstats_filetotal_agg_pkey
-- frs_dlstats_group_agg_day
-- frsdlstatsgroupagg_day_dls

-- frs_file_name
-- frs_file_processor
-- frs_file_type

-- frs_release_by
-- frs_release_date

-- ftpdl_day
-- group_id_idx

-- httpdl_day
-- intel_agreement
-- intel_agreement_pkey
-- news_date

-- project_counts_weekly_tmp

-- project_metric_weekly_tmp1
-- project_metric_weekly_tmp1_pkey
-- project_weekly_metric_pkey

-- stats_agr_tmp_fid
-- stats_agr_tmp_gid

-- user_metric1
-- user_metric1_ranking_key
-- user_metric1_ranking_seq
-- user_metric2
-- user_metric2_ranking_key
-- user_metric2_ranking_seq
-- user_metric3
-- user_metric3_ranking_key
-- user_metric3_ranking_seq
-- user_metric4
-- user_metric4_ranking_key
-- user_metric4_ranking_seq
-- user_metric5
-- user_metric5_ranking_key
-- user_metric5_ranking_seq
-- user_metric6
-- user_metric6_ranking_key
-- user_metric6_ranking_seq
-- user_metric7
-- user_metric7_ranking_key
-- user_metric7_ranking_seq
-- user_metric8
-- user_metric8_ranking_key
-- user_metric8_ranking_seq

-- user_metric_tmp1_1
-- user_metric_tmp1_2
-- user_metric_tmp1_3
-- user_metric_tmp1_4
-- user_metric_tmp1_5
-- user_metric_tmp1_6
-- user_metric_tmp1_7
-- user_metric_tmp1_8



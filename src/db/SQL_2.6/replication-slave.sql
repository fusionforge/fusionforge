--
--	SourceForge: Breaking Down the Barriers to Open Source Development
--	Copyright 1999-2001 (c) VA Linux Systems
--	http://sourceforge.net
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
CREATE UNIQUE INDEX "statssite_month_day" on "stats_site" using btree ( "month" "int4_ops", "day" "int4_ops" );


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
CREATE UNIQUE INDEX "statsproject_month_day_group" on "stats_project"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_project_developers" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "developers" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statsprojectdev_month_day_group" on "stats_project_developers"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_project_metric" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "ranking" integer DEFAULT 0 NOT NULL,
        "percentile" double precision DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statsprojectmetric_month_day_gr" on "stats_project_metric"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "frs_dlstats_file_agg" (
        "month" integer,
        "day" integer,
        "file_id" integer,
        "downloads" integer
);
CREATE UNIQUE INDEX "frsdlfileagg_month_day_file" on "frs_dlstats_file_agg"
	using btree ( "month" "int4_ops", "day" "int4_ops", "file_id" "int4_ops" );


CREATE TABLE "stats_subd_pages" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "pages" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statssubdpages_month_day_group" on "stats_subd_pages"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_agg_logo_by_group" (
        "month" integer,
        "day" integer,
        "group_id" integer,
        "count" integer
);
CREATE UNIQUE INDEX "statslogobygroup_month_day_grou" on "stats_agg_logo_by_group"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_cvs_group" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "checkouts" integer DEFAULT 0 NOT NULL,
        "commits" integer DEFAULT 0 NOT NULL,
        "adds" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statscvsgroup_month_day_group" on "stats_cvs_group"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_agg_site_by_group" (
        "month" integer,
        "day" integer,
        "group_id" integer,
        "count" integer
);
CREATE UNIQUE INDEX "statssitebygroup_month_day_grou" on "stats_agg_site_by_group"
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );


CREATE TABLE "stats_site_pages_by_day" (
        "month" integer,
        "day" integer,
        "site_page_views" integer
);
CREATE  INDEX "statssitepagesbyday_month_day" on "stats_site_pages_by_day"
	using btree ( "month" "int4_ops", "day" "int4_ops" );


CREATE TABLE "frs_package" (
        "package_id" integer DEFAULT nextval('frs_package_pk_seq'::text) NOT NULL,
        "group_id" integer DEFAULT '0' NOT NULL,
        "name" text,
        "status_id" integer DEFAULT '0' NOT NULL,
        Constraint "frs_package_pkey" Primary Key ("package_id")
);
CREATE  INDEX "package_group_id" on "frs_package" using btree ( "group_id" "int4_ops" );


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
CREATE  INDEX "frs_release_package" on "frs_release" using btree ( "package_id" "int4_ops" );


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
CREATE  INDEX "frs_file_date" on "frs_file" using btree ( "post_date" "int4_ops" );
CREATE  INDEX "frs_file_release_id" on "frs_file" using btree ( "release_id" "int4_ops" );


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
        Constraint "users_pkey" Primary Key ("user_id")
);
CREATE UNIQUE INDEX "users_namename_uniq" on "users" using btree ( "user_name" "text_ops" );
CREATE  INDEX "users_status" on "users" using btree ( "status" "bpchar_ops" );
CREATE  INDEX "users_user_pw" on "users" using btree ( "user_pw" "varchar_ops" );


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
CREATE UNIQUE INDEX "group_unix_uniq" on "groups" using btree ( "unix_group_name" "varchar_ops" );
CREATE  INDEX "groups_type" on "groups" using btree ( "type" "int4_ops" );
CREATE  INDEX "groups_public" on "groups" using btree ( "is_public" "int4_ops" );
CREATE  INDEX "groups_status" on "groups" using btree ( "status" "bpchar_ops" );


CREATE TABLE "frs_processor" (
        "processor_id" integer DEFAULT nextval('frs_processor_pk_seq'::text) NOT NULL,
        "name" text,
        Constraint "frs_processor_pkey" Primary Key ("processor_id")
);


CREATE TABLE "frs_filetype" (
        "type_id" integer DEFAULT nextval('frs_filetype_pk_seq'::text) NOT NULL,
        "name" text,
        Constraint "frs_filetype_pkey" Primary Key ("type_id")
);


CREATE TABLE "project_weekly_metric" (
        "ranking" integer DEFAULT nextval('project_weekly_metric_pk_seq'::text) NOT NULL,
        "percentile" double precision,
        "group_id" integer DEFAULT '0' NOT NULL,
        Constraint "project_weekly_metric_pkey" Primary Key ("ranking")
);
CREATE  INDEX "projectweeklymetric_ranking" on "project_weekly_metric" using btree ( "ranking" "int4_ops" );
CREATE  INDEX "project_metric_weekly_group" on "project_weekly_metric" using btree ( "group_id" "int4_ops" );


CREATE TABLE "trove_group_link" (
        "trove_group_id" integer DEFAULT nextval('trove_group_link_pk_seq'::text) NOT NULL,
        "trove_cat_id" integer DEFAULT '0' NOT NULL,
        "trove_cat_version" integer DEFAULT '0' NOT NULL,
        "group_id" integer DEFAULT '0' NOT NULL,
        "trove_cat_root" integer DEFAULT '0' NOT NULL,
        Constraint "trove_group_link_pkey" Primary Key ("trove_group_id")
);
CREATE  INDEX "trove_group_link_group_id" on "trove_group_link" using btree ( "group_id" "int4_ops" );
CREATE  INDEX "trove_group_link_cat_id" on "trove_group_link" using btree ( "trove_cat_id" "int4_ops" );


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
CREATE  INDEX "parent_idx" on "trove_cat" using btree ( "parent" "int4_ops" );
CREATE  INDEX "root_parent_idx" on "trove_cat" using btree ( "root_parent" "int4_ops" );
CREATE  INDEX "version_idx" on "trove_cat" using btree ( "version" "int4_ops" );


CREATE SEQUENCE "trove_treesums_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "trove_treesums" (
        "trove_treesums_id" integer DEFAULT nextval('trove_treesums_pk_seq'::text) NOT NULL,
        "trove_cat_id" integer DEFAULT '0' NOT NULL,
        "limit_1" integer DEFAULT '0' NOT NULL,
        "subprojects" integer DEFAULT '0' NOT NULL,
        Constraint "trove_treesums_pkey" Primary Key ("trove_treesums_id")
);

CREATE TABLE "foundry_projects" (
        "id" integer DEFAULT nextval('foundry_projects_pk_seq'::text) NOT NULL,
        "foundry_id" integer DEFAULT '0' NOT NULL,
        "project_id" integer DEFAULT '0' NOT NULL,
        Constraint "foundry_projects_pkey" Primary Key ("id")
);
CREATE  INDEX "foundry_projects_foundry" on "foundry_projects" using btree (
"foundry_id" "int4_ops" );

COPY foundry_projects WITH oids from '/home/tperdue/dumpfiles/foundry_projects.dump';
COPY stats_site WITH OIDS FROM '/home/tperdue/dumpfiles/stats_site.dump';
COPY stats_project WITH OIDS FROM '/home/tperdue/dumpfiles/stats_project.dump';
COPY stats_project_developers WITH OIDS FROM '/home/tperdue/dumpfiles/stats_project_developers.dump';
COPY stats_project_metric WITH OIDS FROM '/home/tperdue/dumpfiles/stats_project_metric.dump';
COPY frs_dlstats_file_agg WITH OIDS FROM '/home/tperdue/dumpfiles/frs_dlstats_file_agg.dump';
COPY stats_subd_pages WITH OIDS FROM '/home/tperdue/dumpfiles/stats_subd_pages.dump';
COPY stats_agg_logo_by_group WITH OIDS FROM '/home/tperdue/dumpfiles/stats_agg_logo_by_group.dump';
COPY stats_cvs_group WITH OIDS FROM '/home/tperdue/dumpfiles/stats_cvs_group.dump';
COPY stats_agg_site_by_group WITH OIDS FROM '/home/tperdue/dumpfiles/stats_agg_site_by_group.dump';
COPY stats_site_pages_by_day WITH OIDS FROM '/home/tperdue/dumpfiles/stats_site_pages_by_day.dump';
COPY frs_package WITH OIDS FROM '/home/tperdue/dumpfiles/frs_package.dump';
COPY frs_release WITH OIDS FROM '/home/tperdue/dumpfiles/frs_release.dump';
COPY frs_processor WITH OIDS FROM '/home/tperdue/dumpfiles/frs_processor.dump';
COPY frs_filetype WITH OIDS FROM '/home/tperdue/dumpfiles/frs_filetype.dump';
COPY frs_file WITH OIDS FROM '/home/tperdue/dumpfiles/frs_file.dump';
COPY users WITH OIDS FROM '/home/tperdue/dumpfiles/users.dump';
COPY groups WITH OIDS FROM '/home/tperdue/dumpfiles/groups.dump';
COPY project_weekly_metric WITH OIDS FROM '/home/tperdue/dumpfiles/project_weekly_metric.dump';
COPY trove_cat WITH OIDS FROM '/home/tperdue/dumpfiles/trove_cat.dump';
COPY trove_group_link WITH OIDS FROM '/home/tperdue/dumpfiles/trove_group_link.dump';

drop index project_weekly_metric_pkey;

CREATE UNIQUE INDEX statssite_oid ON stats_site(oid);
CREATE UNIQUE INDEX statsproject_oid ON stats_project(oid);
CREATE UNIQUE INDEX statsprojectdevelop_oid ON stats_project_developers(oid);
CREATE UNIQUE INDEX statsprojectmetric_oid ON stats_project_metric(oid);
CREATE UNIQUE INDEX frsdlfileagg_oid ON frs_dlstats_file_agg(oid);
CREATE UNIQUE INDEX statssubdpages_oid ON stats_subd_pages(oid);
CREATE UNIQUE INDEX statsagglogobygrp_oid ON stats_agg_logo_by_group(oid);
CREATE UNIQUE INDEX statscvsgrp_oid ON stats_cvs_group(oid);
CREATE UNIQUE INDEX statsaggsitebygrp_oid ON stats_agg_site_by_group(oid);
CREATE UNIQUE INDEX statssitepgsbyday_oid ON stats_site_pages_by_day(oid);

CREATE SEQUENCE plugin_scmcvs_grp_usage_pk_seq ;

CREATE TABLE plugin_scmcvs_group_usage (
	group_usage_id integer DEFAULT nextval('plugin_scmcvs_grp_usage_pk_seq'::text) NOT NULL,
	group_id integer DEFAULT 0 NOT NULL,
	cvs_host text DEFAULT '' NOT NULL,
	anon_cvs integer DEFAULT 0 NOT NULL,
	CONSTRAINT "plugin_scmcvs_group_usage_pkey" PRIMARY KEY ("group_usage_id"),
	CONSTRAINT "plugin_scmcvs_groupusage_groupid_fkey" FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE
) ;

CREATE SEQUENCE plugin_scmcvs_stats_pk_seq ;

CREATE TABLE plugin_scmcvs_stats (
	stats_id integer DEFAULT nextval('plugin_scmcvs_stats_pk_seq'::text) NOT NULL,
	group_id integer DEFAULT 0 NOT NULL,
	last_check_date integer DEFAULT 0 NOT NULL,
	last_repo_version integer DEFAULT 0 NOT NULL,
	adds integer DEFAULT 0 NOT NULL,
	deletes integer DEFAULT 0 NOT NULL,
	commits integer DEFAULT 0 NOT NULL,
	changes integer DEFAULT 0 NOT NULL,
	CONSTRAINT "plugin_scmcvs_stats_pkey" PRIMARY KEY ("stats_id"),
	CONSTRAINT "plugin_scmcvs_stats_groupid_fkey" FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE
) ;

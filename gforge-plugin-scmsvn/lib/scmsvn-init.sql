CREATE SEQUENCE plugin_scmsvn_grp_usage_pk_seq ;

CREATE TABLE plugin_scmsvn_group_usage (
	group_usage_id integer DEFAULT nextval('plugin_scmsvn_grp_usage_pk_seq'::text) NOT NULL,
	group_id integer DEFAULT 0 NOT NULL,
	svn_host text DEFAULT '' NOT NULL,
	anon_svn integer DEFAULT 0 NOT NULL,
	CONSTRAINT "plugin_scmsvn_group_usage_pkey" PRIMARY KEY ("group_usage_id"),
	CONSTRAINT "plugin_scmsvn_groupusage_groupid_fkey" FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE
) ;

CREATE SEQUENCE plugin_scmsvn_stats_pk_seq ;

CREATE TABLE plugin_scmsvn_stats (
	stats_id integer DEFAULT nextval('plugin_scmsvn_stats_pk_seq'::text) NOT NULL,
	group_id integer DEFAULT 0 NOT NULL,
	last_check_date integer DEFAULT 0 NOT NULL,
	last_repo_version integer DEFAULT 0 NOT NULL,
	adds integer DEFAULT 0 NOT NULL,
	deletes integer DEFAULT 0 NOT NULL,
	commits integer DEFAULT 0 NOT NULL,
	changes integer DEFAULT 0 NOT NULL,
	CONSTRAINT "plugin_scmsvn_stats_pkey" PRIMARY KEY ("stats_id"),
	CONSTRAINT "plugin_scmsvn_stats_groupid_fkey" FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ON DELETE CASCADE
) ;

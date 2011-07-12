--
--	reloid's get lost during a dump/restore - reset them
--
UPDATE _rserv_slave_tables_
SET reloid=
(select oid
FROM pg_class pgc
WHERE relname=_rserv_slave_tables_.tname);

--
--	Blank out all the tables
--
TRUNCATE foundry_projects ;
TRUNCATE stats_site ;
TRUNCATE stats_project ;
TRUNCATE stats_project_developers ;
TRUNCATE stats_project_metric ;
TRUNCATE frs_dlstats_file_agg ;
TRUNCATE stats_subd_pages ;
TRUNCATE stats_agg_logo_by_group ;
TRUNCATE stats_cvs_group ;
TRUNCATE stats_agg_site_by_group ;
TRUNCATE stats_site_pages_by_day ;
TRUNCATE frs_package ;
TRUNCATE frs_release ;
TRUNCATE frs_processor ;
TRUNCATE frs_filetype ;
TRUNCATE frs_file ;
TRUNCATE users ;
TRUNCATE groups ;
TRUNCATE project_weekly_metric ;
TRUNCATE trove_cat ;
TRUNCATE trove_group_link ;

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

DELETE FROM _rserv_slave_sync_;


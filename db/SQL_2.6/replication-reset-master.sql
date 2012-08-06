BEGIN;

--
--	reloid's get lost during dump/restore - reset them
--
UPDATE _rserv_tables_
SET reloid=
(select oid
FROM pg_class pgc
WHERE relname=_rserv_tables_.tname);

--
--	Purge pending sync logs
--
DELETE FROM _rserv_log_;
DELETE FROM _rserv_sync_;

--
--	Get a dump of all tables inside a transaction to guarantee integrity
--
COPY stats_site WITH OIDS TO '/home/tperdue/dumpfiles/stats_site.dump';
COPY stats_project WITH OIDS TO '/home/tperdue/dumpfiles/stats_project.dump';
COPY stats_project_developers WITH OIDS TO '/home/tperdue/dumpfiles/stats_project_developers.dump';
COPY stats_project_metric WITH OIDS TO '/home/tperdue/dumpfiles/stats_project_metric.dump';
COPY frs_dlstats_file_agg WITH OIDS TO '/home/tperdue/dumpfiles/frs_dlstats_file_agg.dump';
COPY stats_subd_pages WITH OIDS TO '/home/tperdue/dumpfiles/stats_subd_pages.dump';
COPY stats_agg_logo_by_group WITH OIDS TO '/home/tperdue/dumpfiles/stats_agg_logo_by_group.dump';
COPY stats_cvs_group WITH OIDS TO '/home/tperdue/dumpfiles/stats_cvs_group.dump';
COPY stats_agg_site_by_group WITH OIDS TO '/home/tperdue/dumpfiles/stats_agg_site_by_group.dump';
COPY stats_site_pages_by_day WITH OIDS TO '/home/tperdue/dumpfiles/stats_site_pages_by_day.dump';
COPY frs_package WITH OIDS TO '/home/tperdue/dumpfiles/frs_package.dump';
COPY frs_release WITH OIDS TO '/home/tperdue/dumpfiles/frs_release.dump';
COPY frs_processor WITH OIDS TO '/home/tperdue/dumpfiles/frs_processor.dump';
COPY frs_filetype WITH OIDS TO '/home/tperdue/dumpfiles/frs_filetype.dump';
COPY frs_file WITH OIDS TO '/home/tperdue/dumpfiles/frs_file.dump';
COPY project_weekly_metric WITH OIDS TO '/home/tperdue/dumpfiles/project_weekly_metric.dump';
COPY trove_cat WITH OIDS TO '/home/tperdue/dumpfiles/trove_cat.dump';
COPY trove_group_link WITH OIDS TO '/home/tperdue/dumpfiles/trove_group_link.dump';
COPY users WITH OIDS TO '/home/tperdue/dumpfiles/users.dump';
COPY groups WITH OIDS TO '/home/tperdue/dumpfiles/groups.dump';
COPY foundry_projects WITH oids to '/home/tperdue/dumpfiles/foundry_projects.dump';

COMMIT;

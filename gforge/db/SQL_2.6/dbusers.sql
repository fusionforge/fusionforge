--
--	SourceForge: Breaking Down the Barriers to Open Source Development
--	Copyright 1999-2001 (c) VA Linux Systems
--	http://sourceforge.net
--

CREATE USER backend WITH PASSWORD 'xxxxx' NOCREATEDB NOCREATEUSER;

GRANT SELECT ON prweb_vhost,users,mail_group_list TO backend;
GRANT SELECT,UPDATE ON prdb_dbs TO backend;


CREATE USER stats WITH PASSWORD 'xxxxx' NOCREATEDB NOCREATEUSER;

GRANT SELECT ON
groups,
frs_file,
frs_package,
frs_release
TO stats;

GRANT ALL ON
frs_dlstats_file_agg,
stats_ftp_downloads,
stats_http_downloads,
stats_cvs_group,
stats_subd_pages
TO stats;

grant all on _rserv_log_ to stats;

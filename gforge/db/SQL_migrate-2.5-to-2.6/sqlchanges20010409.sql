--
--	Stats structure and process changes
--

drop table stats_project_build_tmp;
drop table tmp_projs_releases_tmp;
begin; delete from stats_project where day is null or week is null; commit;
drop table stats_project_tmp;
drop table topproj_admins;
DROP TABLE frs_dlstats_agg;
DROP TABLE frs_dlstats_filetotal_agg_old;
DROP TABLE stats_agg_pages_by_browser;
DROP TABLE stats_agg_pages_by_day_old;
DROP TABLE stats_agr_filerelease;
DROP TABLE stats_agr_project;
drop table group_cvs_history;
drop table project_counts_tmp;


--
--  Change the date format of stats_agg_site_by_group
--
--  Populated daily by site_stats.php
--
CREATE TABLE frs_dlstats_file_agg_tmp AS
SELECT
	substring(day::text from 1 for 6)::int AS month,
	substring(day::text from 7 for 2)::int AS day,
	file_id,
	downloads
	from frs_dlstats_file_agg;

DROP TABLE frs_dlstats_file_agg;
ALTER TABLE frs_dlstats_file_agg_tmp RENAME TO frs_dlstats_file_agg;

CREATE UNIQUE INDEX frsdlfileagg_month_day_file ON frs_dlstats_file_agg(month,day,file_id);


drop index httpdl_fid;
drop index httpdl_group_id;
create index statshttpdl_day_fileid ON stats_http_downloads(day,filerelease_id);
drop index ftpdl_fid;
drop index ftpdl_group_id;
create index statsftpdl_day_fileid ON stats_ftp_downloads(day,filerelease_id);

--
--	Create an archive table of project_weekly_metric
--
--	Populated by project_weekly_metric.php
--
CREATE TABLE stats_project_metric (
month int not null default 0,
day int not null default 0,
ranking int not null default 0,
precentile float not null default 0,
group_id int not null default 0
);

copy stats_project_metric from '/tmp/stats_project_metric.dump';

CREATE UNIQUE INDEX statsprojectmetric_month_day_group ON stats_project_metric(month,day,group_id);


--
--	Change the date format of stats_agg_site_by_group
--
--	Populated daily by site_stats.php
--
CREATE TABLE stats_agg_site_by_group_tmp AS
SELECT 
	substring(day::text from 1 for 6)::int AS month, 
	substring(day::text from 7 for 2)::int AS day, 
	group_id,
	count
	from stats_agg_site_by_group ;

DROP TABLE stats_agg_site_by_group;
ALTER TABLE stats_agg_site_by_group_tmp RENAME TO stats_agg_site_by_group;

DROP TABLE stats_agg_site_by_day;

CREATE UNIQUE INDEX statssitebygroup_month_day_group ON stats_agg_site_by_group(month,day,group_id);


--
--	Change the date format of stats_agg_logo_by_group
--
--	Populated daily by site_stats.php
--
CREATE TABLE stats_agg_logo_by_group_tmp AS
SELECT	   
	substring(day::text from 1 for 6)::int AS month,
	substring(day::text from 7 for 2)::int AS day,
	group_id,
	count 
	from stats_agg_logo_by_group ;

DROP TABLE stats_agg_logo_by_group;
ALTER TABLE stats_agg_logo_by_group_tmp RENAME TO stats_agg_logo_by_group;

CREATE UNIQUE INDEX statslogobygroup_month_day_group ON stats_agg_logo_by_group(month,day,group_id);


--
-- Subdomain pages
--
create table stats_subd_pages (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
pages INT NOT NULL DEFAULT 0
);

--
--	Migrate data from old stats_project table
--
INSERT INTO stats_subd_pages
SELECT month,day,group_id,subdomain_views 
FROM stats_project WHERE subdomain_views > 0;

CREATE UNIQUE INDEX statssubdpages_month_day_group ON stats_subd_pages(month,day,group_id);


create table stats_cvs_user (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
user_id INT NOT NULL DEFAULT 0,
checkouts INT NOT NULL DEFAULT 0,
commits INT NOT NULL DEFAULT 0,
adds INT NOT NULL DEFAULT 0
);

create table stats_cvs_group (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
checkouts INT NOT NULL DEFAULT 0,
commits INT NOT NULL DEFAULT 0,
adds INT NOT NULL DEFAULT 0
);

--
--	Migrate data from old stats_project table
--
INSERT INTO stats_cvs_group 
SELECT month,day,group_id,cvs_checkouts,cvs_commits,cvs_adds 
FROM stats_project 
WHERE cvs_checkouts > 0 
OR cvs_commits > 0 
OR cvs_adds > 0;

CREATE UNIQUE INDEX statscvsgroup_month_day_group ON stats_cvs_group(month,day,group_id);


DROP INDEX archive_project_day;
DROP INDEX archive_project_month;
DROP INDEX archive_project_monthday;
DROP INDEX archive_project_week;
DROP INDEX project_log_group;

--
--	Populated daily by site_stats.php
--
create table stats_project_developers (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
developers INT NOT NULL DEFAULT 0
);

--
--	Migrate data from old stats_project table
--
COPY stats_project_developers from '/tmp/stats_project_developers';
CREATE UNIQUE INDEX statsprojectdev_month_day_group ON stats_project_developers(month,day,group_id);


--
--	Reorg and normalize stats_project as much as feasible
--
--	Populated daily by site_stats.php
--
DROP TABLE stats_project;

create table stats_project (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
file_releases INT DEFAULT 0,
msg_posted INT DEFAULT 0,
msg_uniq_auth INT DEFAULT 0,
bugs_opened INT DEFAULT 0,
bugs_closed INT DEFAULT 0,
support_opened INT DEFAULT 0,
support_closed INT DEFAULT 0,
patches_opened INT DEFAULT 0,
patches_closed INT DEFAULT 0,
artifacts_opened INT DEFAULT 0,
artifacts_closed INT DEFAULT 0,
tasks_opened INT DEFAULT 0,
tasks_closed INT DEFAULT 0,
help_requests INT DEFAULT 0
);

copy stats_project from '/tmp/stats_project.dump';

CREATE UNIQUE INDEX statsproject_month_day_group ON stats_project(month,day,group_id);


--
--	Reorg and normalize the stats_site table
--
--	Populated daily by site_stats.php
--
create table stats_site_tmp AS 
select month,day,uniq_users,sessions,total_users,new_users,new_projects 
from stats_site;

DROP TABLE stats_site;
ALTER TABLE stats_site_tmp RENAME TO stats_site;

CREATE UNIQUE INDEX statssite_month_day on stats_site(month,day);

grant all on stats_cvs_group to stats;
grant all on stats_project to stats;
grant all on stats_subd_pages to stats;

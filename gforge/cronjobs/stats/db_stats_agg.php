#! /usr/bin/php4 -f
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require ('squal_pre.php');

/*

	Aggregation script

	Since we cannot crunch down all the data on the fly anymore, 
	we need to crunch it down once daily into separate tables

*/


/*
	FIRST TIME RUN:

--
--	Create a table of total downloads by file
--
--	NOTE: Builds on stats_sum.pl
--
DROP TABLE frs_dlstats_filetotal_agg;
CREATE TABLE frs_dlstats_filetotal_agg AS
SELECT file_id,sum(downloads)::int AS downloads
FROM frs_dlstats_file_agg
GROUP BY file_id;

CREATE INDEX frsdlfiletotal_fileid on frs_dlstats_filetotal_agg(file_id);

--
--	Create a table of total downloads by group
--
--	NOTE: Builds on prior step (frs_dlstats_filetotal_agg)
--
DROP TABLE frs_dlstats_grouptotal_agg;
CREATE TABLE frs_dlstats_grouptotal_agg AS
SELECT frs_package.group_id, sum(frs_dlstats_filetotal_agg.downloads)::int AS downloads
FROM frs_package,frs_release,frs_file,frs_dlstats_filetotal_agg
WHERE frs_package.package_id=frs_release.package_id 
AND frs_release.release_id=frs_file.release_id 
AND frs_file.file_id=frs_dlstats_filetotal_agg.file_id
GROUP BY frs_package.group_id;

CREATE INDEX frsdlgrouptotal_groupid ON frs_dlstats_grouptotal_agg(group_id);

--
--	Create table of total downloads by group/day
--
--	NOTE: Builds on frs_dlstats_filetotal_agg
--
DROP TABLE frs_dlstats_group_agg;
CREATE TABLE frs_dlstats_group_agg AS
SELECT 
frs_package.group_id::int AS group_id, 
fdfa.month::int AS month, 
fdfa.day::int AS day, 
sum(fdfa.downloads)::int AS downloads
FROM frs_package,frs_release,frs_file,frs_dlstats_file_agg fdfa
WHERE frs_package.package_id=frs_release.package_id 
AND frs_release.release_id=frs_file.release_id 
AND frs_file.file_id=fdfa.file_id
GROUP BY frs_package.group_id,fdfa.month, fdfa.day;

CREATE INDEX frsdlgroup_groupid ON frs_dlstats_group_agg(group_id);
CREATE INDEX frsdlgroup_month_day_groupid ON frs_dlstats_group_agg(month,day,group_id);

--
--	Create a table containing project_stats grouped by month
--
DROP TABLE stats_project_months;
CREATE TABLE stats_project_months AS
SELECT spd.month::int AS month,
	spd.group_id::int AS group_id,
	spd.developers::int AS developers,
	spm.group_ranking::int AS group_ranking,
	spm.group_metric::float AS group_metric,
	salbg.logo_showings::int AS logo_showings,
	fdga.downloads::int AS downloads,
	sasbg.site_views::int AS site_views,
	ssp.subdomain_views::int AS subdomain_views,
	(coalesce(sasbg.site_views,0) + coalesce(ssp.subdomain_views,0))::int AS page_views,
	sp.file_releases::int AS file_releases,
	sp.msg_posted::int AS msg_posted,
	sp.msg_uniq_auth::int AS msg_uniq_auth,
	sp.bugs_opened::int AS bugs_opened,
	sp.bugs_closed::int AS bugs_closed,
	sp.support_opened::int AS support_opened,
	sp.support_closed::int AS support_closed,
	sp.patches_opened::int AS patches_opened,
	sp.patches_closed::int AS patches_closed,
	sp.artifacts_opened::int AS artifacts_opened,
	sp.artifacts_closed::int AS artifacts_closed,
	sp.tasks_opened::int AS tasks_opened,
	sp.tasks_closed::int AS tasks_closed,
	sp.help_requests::int AS help_requests,
	scg.cvs_checkouts::int AS cvs_checkouts,
	scg.cvs_commits::int AS cvs_commits,
	scg.cvs_adds::int AS cvs_adds
	FROM (
		SELECT month,group_id,avg(developers)::int AS developers 
		FROM stats_project_developers GROUP BY month,group_id 
		) spd 

	LEFT JOIN (
		SELECT month,group_id,sum(file_releases) AS file_releases,
			sum(msg_posted) AS msg_posted,
			sum(msg_uniq_auth) AS msg_uniq_auth,
			sum(bugs_opened) AS bugs_opened,
			sum(bugs_closed) AS bugs_closed, 
			sum(support_opened) AS support_opened,
			sum(support_closed) AS support_closed,
			sum(patches_opened) AS patches_opened,
			sum(patches_closed) AS patches_closed,
			sum(artifacts_opened) AS artifacts_opened,
			sum(artifacts_closed) AS artifacts_closed,
			sum(tasks_opened) AS tasks_opened,
			sum(tasks_closed) AS tasks_closed,
			sum(help_requests) AS help_requests
		FROM
			stats_project
		GROUP BY month,group_id
		) sp USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(count) AS logo_showings 
		FROM stats_agg_logo_by_group 
		GROUP BY month,group_id
		) salbg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,avg(ranking)::int AS group_ranking,avg(percentile)::float AS group_metric
		FROM stats_project_metric 
		GROUP BY month,group_id
		) spm USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(checkouts) AS cvs_checkouts,sum(commits) AS cvs_commits,sum(adds) AS cvs_adds
		FROM stats_cvs_group 
		GROUP BY month,group_id
		) scg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(count) AS site_views
		FROM stats_agg_site_by_group 
		GROUP BY month,group_id
		) sasbg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(pages) AS subdomain_views
		FROM stats_subd_pages 
		GROUP BY month,group_id
		) ssp USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(downloads) AS downloads
		FROM frs_dlstats_group_agg 
		GROUP BY month,group_id
		) fdga USING (month,group_id);

CREATE INDEX statsprojectmonths_groupid on stats_project_months(group_id);
CREATE INDEX statsprojectmonths_groupid_month on stats_project_months(group_id,month);

--
--	Create a table containing project_stats grouped by all time
--
--	NOTE: Builds on results in stats_project_months
--
DROP TABLE stats_project_all;
CREATE TABLE stats_project_all AS
SELECT group_id::int AS group_id,
	AVG(developers)::int AS developers,
	AVG(group_ranking)::int AS group_ranking,
	AVG(group_metric)::float AS group_metric, 
	SUM(logo_showings)::int AS logo_showings,
	SUM(downloads)::int AS downloads, 
	SUM(site_views)::int AS site_views,
	SUM(subdomain_views)::int AS subdomain_views,
	SUM(page_views)::int AS page_views, 
	SUM(msg_posted)::int AS msg_posted, 
	AVG(msg_uniq_auth)::int AS msg_uniq_auth,
	SUM(bugs_opened)::int AS bugs_opened,
	SUM(bugs_closed)::int AS bugs_closed, 
	SUM(support_opened)::int AS support_opened, 
	SUM(support_closed)::int AS support_closed, 
	SUM(patches_opened)::int AS patches_opened, 
	SUM(patches_closed)::int AS patches_closed, 
	SUM(artifacts_opened)::int AS artifacts_opened, 
	SUM(artifacts_closed)::int AS artifacts_closed, 
	SUM(tasks_opened)::int AS tasks_opened, 
	SUM(tasks_closed)::int AS tasks_closed, 
	SUM(help_requests)::int AS help_requests, 
	SUM(cvs_checkouts)::int AS cvs_checkouts, 
	SUM(cvs_commits)::int AS cvs_commits, 
	SUM(cvs_adds)::int AS cvs_adds 
	FROM stats_project_months 
	GROUP BY group_id
	ORDER BY group_id DESC;

CREATE INDEX statsprojectall_groupid on stats_project_all(group_id);

--
--	We unforunately have to create this temp table as postgres
--	fails to produce the right results in production (works on webdev)
--
DROP TABLE stats_project_developers_last30;
CREATE TABLE stats_project_developers_last30 AS
SELECT * FROM stats_project_developers 
WHERE (month = 200104 AND day >= 8 ) OR ( month > 200104 );

--
--	Build a table with only the last 30 days of data in it
--
DROP TABLE stats_project_last_30;
CREATE TABLE stats_project_last_30 AS
SELECT spd.month::int AS month,
spd.day::int AS day,
spd.group_id::int AS group_id,
spd.developers::int AS developers,
spm.ranking::int AS group_ranking,
spm.percentile::float AS group_metric,
salbg.count::int AS logo_showings,
fdga.downloads::int AS downloads,
sasbg.count::int AS site_views,
ssp.pages::int AS subdomain_views,
(coalesce(sasbg.count,0) + coalesce(ssp.pages,0))::int AS page_views,
sp.file_releases::int AS filereleases,
sp.msg_posted::int AS msg_posted,
sp.msg_uniq_auth::int AS msg_uniq_auth,
sp.bugs_opened::int AS bugs_opened,
sp.bugs_closed::int AS bugs_closed,
sp.support_opened::int AS support_opened,
sp.support_closed::int AS support_closed,
sp.patches_opened::int AS patches_opened,
sp.patches_closed::int AS patches_closed,
sp.artifacts_opened::int AS artifacts_opened,
sp.artifacts_closed::int AS artifacts_closed,
sp.tasks_opened::int AS tasks_opened,
sp.tasks_closed::int AS tasks_closed,
sp.help_requests::int AS help_requests,
scg.checkouts::int AS cvs_checkouts,
scg.commits::int AS cvs_commits,
scg.adds::int AS cvs_adds

FROM stats_project_developers_last30 spd

LEFT JOIN frs_dlstats_group_agg fdga USING (month,day,group_id)
LEFT JOIN stats_project sp USING (month,day,group_id)
LEFT JOIN stats_agg_logo_by_group salbg USING (month,day,group_id)
LEFT JOIN stats_project_metric spm USING (month,day,group_id)
LEFT JOIN stats_cvs_group scg USING (month,day,group_id)
LEFT JOIN stats_agg_site_by_group sasbg USING (month,day,group_id)
LEFT JOIN stats_subd_pages ssp USING (month,day,group_id)
;

CREATE INDEX statsproject30_groupid on stats_project_last_30(group_id);

--
--	create a table containing main site page views grouped by month
--
DROP TABLE stats_site_pages_by_month;
CREATE TABLE stats_site_pages_by_month AS
select month,sum(site_page_views)::int as site_page_views 
	from stats_site_pages_by_day group by month;

--
--	Create a table joining stats_site and stats_project
--	with the last 30 days of data only
--
--	NOTE: Builds on results of stats_project_last_30
--
DROP TABLE stats_site_last_30;
CREATE TABLE stats_site_last_30 AS
SELECT p.month::int AS month, 
	p.day::int AS day, 
	sspbd.site_page_views::int AS site_page_views,
	SUM(p.downloads)::int AS downloads, 
	SUM(p.subdomain_views)::int AS subdomain_views,
	SUM(p.msg_posted)::int AS msg_posted, 
	SUM(p.bugs_opened)::int AS bugs_opened, 
	SUM(p.bugs_closed)::int AS bugs_closed, 
	SUM(p.support_opened)::int AS support_opened, 
	SUM(p.support_closed)::int AS support_closed, 
	SUM(p.patches_opened)::int AS patches_opened, 
	SUM(p.patches_closed)::int AS patches_closed, 
	SUM(p.artifacts_opened)::int AS artifacts_opened,
	SUM(p.artifacts_closed)::int AS artifacts_closed,
	SUM(p.tasks_opened)::int AS tasks_opened, 
	SUM(p.tasks_closed)::int AS tasks_closed, 
	SUM(p.help_requests)::int AS help_requests, 
	SUM(p.cvs_checkouts)::int AS cvs_checkouts, 
	SUM(p.cvs_commits)::int AS cvs_commits, 
	SUM(p.cvs_adds)::int AS cvs_adds 
	FROM stats_project_last_30 p, stats_site_pages_by_day sspbd
		WHERE p.month=sspbd.month AND p.day=sspbd.day
	GROUP BY p.month, p.day, sspbd.site_page_views;

CREATE INDEX statssitelast30_month_day on stats_site_last_30 (month,day);

--
--  Create a table joining stats_site and stats_project
--  grouped by month
--
--	NOTICE - this builds on the results of stats_project_months
--
DROP TABLE stats_site_months;
CREATE TABLE stats_site_months AS
SELECT spm.month::int AS month, 
	sspbm.site_page_views::int AS site_page_views,
	SUM(spm.downloads)::int AS downloads,
	SUM(spm.subdomain_views)::int AS subdomain_views,
	SUM(spm.msg_posted)::int AS msg_posted, 
	SUM(spm.bugs_opened)::int AS bugs_opened, 
	SUM(spm.bugs_closed)::int AS bugs_closed, 
	SUM(spm.support_opened)::int AS support_opened, 
	SUM(spm.support_closed)::int AS support_closed, 
	SUM(spm.patches_opened)::int AS patches_opened, 
	SUM(spm.patches_closed)::int AS patches_closed, 
	SUM(spm.artifacts_opened)::int AS artifacts_opened,
	SUM(spm.artifacts_closed)::int AS artifacts_closed,
	SUM(spm.tasks_opened)::int AS tasks_opened, 
	SUM(spm.tasks_closed)::int AS tasks_closed, 
	SUM(spm.help_requests)::int AS help_requests,
	SUM(spm.cvs_checkouts)::int AS cvs_checkouts, 
	SUM(spm.cvs_commits)::int AS cvs_commits, 
	SUM(spm.cvs_adds)::int AS cvs_adds 
	FROM stats_project_months spm, stats_site_pages_by_month sspbm
		WHERE spm.month=sspbm.month
	GROUP BY spm.month,sspbm.site_page_views
	ORDER BY spm.month ASC;

CREATE INDEX statssitemonths_month on stats_site_months(month);

--
--  Create a table joining stats_site and stats_project
--  grouped by all to get total
--
--  NOTICE - this builds on the results of stats_site_months
--
DROP TABLE stats_site_all;
CREATE TABLE stats_site_all AS
SELECT 
	SUM(site_page_views)::int AS site_page_views,
	SUM(downloads)::int AS downloads,
	SUM(subdomain_views)::int AS subdomain_views,
	SUM(msg_posted)::int AS msg_posted, 
	SUM(bugs_opened)::int AS bugs_opened, 
	SUM(bugs_closed)::int AS bugs_closed, 
	SUM(support_opened)::int AS support_opened, 
	SUM(support_closed)::int AS support_closed, 
	SUM(patches_opened)::int AS patches_opened, 
	SUM(patches_closed)::int AS patches_closed, 
	SUM(artifacts_opened)::int AS artifacts_opened,
	SUM(artifacts_closed)::int AS artifacts_closed,
	SUM(tasks_opened)::int AS tasks_opened, 
	SUM(tasks_closed)::int AS tasks_closed, 
	SUM(help_requests)::int AS help_requests,
	SUM(cvs_checkouts)::int AS cvs_checkouts, 
	SUM(cvs_commits)::int AS cvs_commits, 
	SUM(cvs_adds)::int AS cvs_adds 
	FROM stats_site_months;

*/



//
//  total file downloads by file
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning frs_dlstats_filetotal_agg: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM frs_dlstats_filetotal_agg;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO frs_dlstats_filetotal_agg
SELECT file_id,sum(downloads) AS downloads
FROM frs_dlstats_file_agg
GROUP BY file_id
;", -1, 0, SYS_DB_STATS);

if (!$rel) {
    echo "ERROR IN frs_dlstats_filetotal_agg";
}   

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE frs_dlstats_filetotal_agg;", -1, 0, SYS_DB_STATS);



//
//  total downloads by group
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning frs_dlstats_grouptotal_agg: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM frs_dlstats_grouptotal_agg;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO frs_dlstats_grouptotal_agg
SELECT frs_package.group_id, sum(frs_dlstats_filetotal_agg.downloads) AS downloads
FROM frs_package,frs_release,frs_file,frs_dlstats_filetotal_agg
WHERE frs_package.package_id=frs_release.package_id 
AND frs_release.release_id=frs_file.release_id 
AND frs_file.file_id=frs_dlstats_filetotal_agg.file_id
GROUP BY frs_package.group_id
;", -1, 0, SYS_DB_STATS);

if (!$rel) {
    echo "ERROR IN frs_dlstats_grouptotal_agg";
}

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE frs_dlstats_grouptotal_agg;", -1, 0, SYS_DB_STATS);



//
//  total downloads by group
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning frs_dlstats_group_agg: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM frs_dlstats_group_agg;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO frs_dlstats_group_agg
SELECT frs_package.group_id, fdfa.month, fdfa.day, sum(fdfa.downloads) AS downloads
FROM frs_package,frs_release,frs_file,frs_dlstats_file_agg fdfa
WHERE frs_package.package_id=frs_release.package_id 
AND frs_release.release_id=frs_file.release_id 
AND frs_file.file_id=fdfa.file_id
GROUP BY frs_package.group_id,fdfa.month, fdfa.day
;", -1, 0, SYS_DB_STATS);

if (!$rel) {
    echo "ERROR IN frs_dlstats_group_agg";
}

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE frs_dlstats_group_agg;", -1, 0, SYS_DB_STATS);

?><?php


//
//	project stats by month
//
db_begin(SYS_DB_STATS);
echo "\n\nBeginning stats_project_months: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_project_months;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_project_months
SELECT spd.month,
	spd.group_id,
	spd.developers,
	spm.group_ranking,
	spm.group_metric,
	salbg.logo_showings,
	fdga.downloads,
	sasbg.site_views,
	ssp.subdomain_views,
	(sasbg.site_views + ssp.subdomain_views) AS page_views,
	sp.file_releases,
	sp.msg_posted,
	sp.msg_uniq_auth,
	sp.bugs_opened,
	sp.bugs_closed,
	sp.support_opened,
	sp.support_closed,
	sp.patches_opened,
	sp.patches_closed,
	sp.artifacts_opened,
	sp.artifacts_closed,
	sp.tasks_opened,
	sp.tasks_closed,
	sp.help_requests,
	scg.cvs_checkouts,
	scg.cvs_commits,
	scg.cvs_adds
	FROM (
		SELECT month,group_id,avg(developers)::int AS developers 
		FROM stats_project_developers GROUP BY month,group_id 
		) spd 

	LEFT JOIN (
		SELECT month,group_id,sum(file_releases) AS file_releases,
			sum(msg_posted) AS msg_posted,
			sum(msg_uniq_auth) AS msg_uniq_auth,
			sum(bugs_opened) AS bugs_opened,
			sum(bugs_closed) AS bugs_closed, 
			sum(support_opened) AS support_opened,
			sum(support_closed) AS support_closed,
			sum(patches_opened) AS patches_opened,
			sum(patches_closed) AS patches_closed,
			sum(artifacts_opened) AS artifacts_opened,
			sum(artifacts_closed) AS artifacts_closed,
			sum(tasks_opened) AS tasks_opened,
			sum(tasks_closed) AS tasks_closed,
			sum(help_requests) AS help_requests
		FROM
			stats_project
		GROUP BY month,group_id
		) sp USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(count) AS logo_showings 
		FROM stats_agg_logo_by_group 
		GROUP BY month,group_id
		) salbg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,avg(ranking)::int AS group_ranking,avg(percentile)::float AS group_metric
		FROM stats_project_metric 
		GROUP BY month,group_id
		) spm USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(checkouts) AS cvs_checkouts,sum(commits) AS cvs_commits,sum(adds) AS cvs_adds
		FROM stats_cvs_group 
		GROUP BY month,group_id
		) scg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(count) AS site_views
		FROM stats_agg_site_by_group 
		GROUP BY month,group_id
		) sasbg USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(pages) AS subdomain_views
		FROM stats_subd_pages 
		GROUP BY month,group_id
		) ssp USING (month,group_id)

	LEFT JOIN (
		SELECT month,group_id,sum(downloads) AS downloads
		FROM frs_dlstats_group_agg 
		GROUP BY month,group_id
		) fdga USING (month,group_id);
", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_project_months;", -1, 0, SYS_DB_STATS);



//
//	All-time stats by group_id
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_project_all: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_project_all;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_project_all
SELECT group_id,
    AVG(developers)::int AS developers,
    AVG(group_ranking)::int AS group_ranking,
    AVG(group_metric) AS group_metric, 
    SUM(logo_showings) AS logo_showings,
    SUM(downloads) AS downloads, 
    SUM(site_views) AS site_views,
    SUM(subdomain_views) AS subdomain_views,
    SUM(page_views) AS page_views, 
    SUM(msg_posted) AS msg_posted, 
    AVG(msg_uniq_auth)::int AS msg_uniq_auth,
    SUM(bugs_opened) AS bugs_opened,
    SUM(bugs_closed) AS bugs_closed, 
    SUM(support_opened) AS support_opened, 
    SUM(support_closed) AS support_closed, 
    SUM(patches_opened) AS patches_opened, 
    SUM(patches_closed) AS patches_closed, 
    SUM(artifacts_opened) AS artifacts_opened, 
    SUM(artifacts_closed) AS artifacts_closed, 
    SUM(tasks_opened) AS tasks_opened, 
    SUM(tasks_closed) AS tasks_closed, 
    SUM(help_requests) AS help_requests, 
    SUM(cvs_checkouts) AS cvs_checkouts, 
    SUM(cvs_commits) AS cvs_commits, 
    SUM(cvs_adds) AS cvs_adds 
    FROM stats_project_months 
    GROUP BY group_id
    ORDER BY group_id DESC
;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_project_all;", -1, 0, SYS_DB_STATS);


$beg_year=date('Y',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
$beg_month=date('m',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
$beg_day=date('d',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));

echo "\n$beg_year$beg_month$beg_day";

$year=date('Y');
$month=date('m');

//
//	Table with just the last 30 days data sorted out
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_project_developers_last30: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_project_developers_last30;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_project_developers_last30
SELECT * FROM stats_project_developers
WHERE ( month = '$beg_year$beg_month' AND day >= '$beg_day' ) OR ( month > '$beg_year$beg_month' );
", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_project_developers_last30;", -1, 0, SYS_DB_STATS);

?><?php


//
//	Table with just the last 30 days data sorted out
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_project_last_30: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_project_last_30;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_project_last_30
SELECT spd.month,
spd.day,
spd.group_id,
spd.developers,
spm.ranking AS group_ranking,
spm.percentile AS group_metric,
salbg.count AS logo_showings,
fdga.downloads,
sasbg.count AS site_views,
ssp.pages AS subdomain_views,
sasbg.count+ssp.pages AS page_views,
sp.file_releases,
sp.msg_posted,
sp.msg_uniq_auth,
sp.bugs_opened,
sp.bugs_closed,
sp.support_opened,
sp.support_closed,
sp.patches_opened,
sp.patches_closed,
sp.artifacts_opened,
sp.artifacts_closed,
sp.tasks_opened,
sp.tasks_closed,
sp.help_requests,
scg.checkouts AS cvs_checkouts,
scg.commits AS cvs_commits,
scg.adds AS cvs_adds

FROM stats_project_developers_last30 spd

LEFT JOIN frs_dlstats_group_agg fdga USING (month,day,group_id)
LEFT JOIN stats_project sp USING (month,day,group_id)
LEFT JOIN stats_agg_logo_by_group salbg USING (month,day,group_id)
LEFT JOIN stats_project_metric spm USING (month,day,group_id)
LEFT JOIN stats_cvs_group scg USING (month,day,group_id)
LEFT JOIN stats_agg_site_by_group sasbg USING (month,day,group_id)
LEFT JOIN stats_subd_pages ssp USING (month,day,group_id)
;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_project_last_30;", -1, 0, SYS_DB_STATS);



//
//  main site page views by month
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_site_pages_by_month: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_site_pages_by_month;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_site_pages_by_month
select month,sum(site_page_views) as site_page_views 
    from stats_site_pages_by_day group by month;
", -1, 0, SYS_DB_STATS);

if (!$rel) {
	echo "ERROR IN stats_site_pages_by_month";
}

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_site_pages_by_month;", -1, 0, SYS_DB_STATS);



//
//  sitewide stats in last 30 days
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_site_last_30: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_site_last_30;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_site_last_30
SELECT p.month, 
	p.day, 
	sspbd.site_page_views,
	SUM(p.downloads) AS downloads, 
	SUM(p.subdomain_views) AS subdomain_views,
	SUM(p.msg_posted) AS msg_posted, 
	SUM(p.bugs_opened) AS bugs_opened, 
	SUM(p.bugs_closed) AS bugs_closed, 
	SUM(p.support_opened) AS support_opened, 
	SUM(p.support_closed) AS support_closed, 
	SUM(p.patches_opened) AS patches_opened, 
	SUM(p.patches_closed) AS patches_closed, 
    SUM(artifacts_opened) AS artifacts_opened, 
    SUM(artifacts_closed) AS artifacts_closed, 
	SUM(p.tasks_opened) AS tasks_opened, 
	SUM(p.tasks_closed) AS tasks_closed, 
	SUM(p.help_requests) AS help_requests, 
	SUM(p.cvs_checkouts) AS cvs_checkouts, 
	SUM(p.cvs_commits) AS cvs_commits, 
	SUM(p.cvs_adds) AS cvs_adds 
	FROM stats_project_last_30 p, stats_site_pages_by_day sspbd
		WHERE p.month=sspbd.month AND p.day=sspbd.day
	GROUP BY p.month, p.day, sspbd.site_page_views;
", -1, 0, SYS_DB_STATS);

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_site_last_30;", -1, 0, SYS_DB_STATS);



//
//  sitewide stats in last 30 days
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_site_months: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_site_months;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_site_months
SELECT spm.month, 
	sspbm.site_page_views,
	SUM(spm.downloads) AS downloads,
	SUM(spm.subdomain_views) AS subdomain_views,
	SUM(spm.msg_posted) AS msg_posted, 
	SUM(spm.bugs_opened) AS bugs_opened, 
	SUM(spm.bugs_closed) AS bugs_closed, 
	SUM(spm.support_opened) AS support_opened, 
	SUM(spm.support_closed) AS support_closed, 
	SUM(spm.patches_opened) AS patches_opened, 
	SUM(spm.patches_closed) AS patches_closed, 
	SUM(spm.artifacts_opened) AS artifacts_opened, 
	SUM(spm.artifacts_closed) AS artifacts_closed, 
	SUM(spm.tasks_opened) AS tasks_opened, 
	SUM(spm.tasks_closed) AS tasks_closed, 
	SUM(spm.help_requests) AS help_requests, 
	SUM(spm.cvs_checkouts) AS cvs_checkouts, 
	SUM(spm.cvs_commits) AS cvs_commits, 
	SUM(spm.cvs_adds) AS cvs_adds 
	FROM stats_project_months spm, stats_site_pages_by_month sspbm
	WHERE spm.month=sspbm.month
	GROUP BY spm.month,sspbm.site_page_views
	ORDER BY spm.month ASC;
", -1, 0, SYS_DB_STATS);

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_site_months;", -1, 0, SYS_DB_STATS);



//
//  sitewide stats all time
//
db_begin(SYS_DB_STATS);

echo "\n\nBeginning stats_site_all: ".date('Y-m-d H:i:s',time());

$rel = db_query("DELETE FROM stats_site_all;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO stats_site_all
SELECT 
	SUM(site_page_views) AS site_page_views,
	SUM(downloads) AS downloads,
	SUM(subdomain_views) AS subdomain_views,
	SUM(msg_posted) AS msg_posted, 
	SUM(bugs_opened) AS bugs_opened, 
	SUM(bugs_closed) AS bugs_closed, 
	SUM(support_opened) AS support_opened, 
	SUM(support_closed) AS support_closed, 
	SUM(patches_opened) AS patches_opened, 
	SUM(patches_closed) AS patches_closed, 
	SUM(artifacts_opened) AS artifacts_opened, 
	SUM(artifacts_closed) AS artifacts_closed, 
	SUM(tasks_opened) AS tasks_opened, 
	SUM(tasks_closed) AS tasks_closed, 
	SUM(help_requests) AS help_requests, 
	SUM(cvs_checkouts) AS cvs_checkouts, 
	SUM(cvs_commits) AS cvs_commits, 
	SUM(cvs_adds) AS cvs_adds 
	FROM stats_site_months;
", -1, 0, SYS_DB_STATS);

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);

db_query("VACUUM ANALYZE stats_site_all;", -1, 0, SYS_DB_STATS);


if (db_error(SYS_DB_STATS)) {
	echo "Error: ".db_error(SYS_DB_STATS);
}
echo "\n\nEnding\n";

?>

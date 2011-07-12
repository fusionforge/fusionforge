#! /usr/bin/php
<?php
/**
 * GForge
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright (c) GForge, LLC
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

$year=date('Y');
$day=date('d');
$month=date('m');


//
//	project stats by month
//
db_begin();
$err .= "\n\nBeginning stats_project_months: ".date('Y-m-d H:i:s',time());

$res = db_query_params ('DELETE FROM stats_project_months',
			array ());
$err .= db_error();

$res = db_query_params ('INSERT INTO stats_project_months
	SELECT month, group_id,
	avg(developers)::int AS developers,
    avg(group_ranking)::int AS group_ranking,
	avg(group_metric) AS group_metric,
	sum(logo_showings) AS logo_showings,
	sum(downloads) AS downloads,
	sum(site_views) AS site_views ,
	sum(subdomain_views) AS subdomain_views,
	sum(page_views) AS page_views,
	sum(file_releases) AS file_releases,
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
	sum(help_requests) AS help_requests,
	sum(cvs_checkouts) AS cvs_checkouts,
	sum(cvs_commits) AS cvs_commits,
	sum(cvs_adds) AS cvs_adds
FROM stats_project_vw
GROUP BY month,group_id',
			array ()) ;
$err .= db_error();

db_commit();

//
//  main site page views by month
//
db_begin();

$err .= "\n\nBeginning stats_site_pages_by_month: ".date('Y-m-d H:i:s',time());

$res = db_query_params ('DELETE FROM stats_site_pages_by_month',
			array ());
$err .= db_error();

$res = db_query_params ('INSERT INTO stats_site_pages_by_month
select month,sum(site_page_views) as site_page_views
    from stats_site_pages_by_day group by month',
			array ());

if (!$res) {
	$err .= "ERROR IN stats_site_pages_by_month";
}

$err .= db_error();

db_commit();

//
//  sitewide stats in last 30 days
//
db_begin();

$err .= "\n\nBeginning stats_site_months: ".date('Y-m-d H:i:s',time());

$res = db_query_params ('DELETE FROM stats_site_months',
			array ());
$err .= db_error();

$res = db_query_params ('INSERT INTO stats_site_months
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
',
			array ());

$err .= db_error();

db_commit();

cron_entry(4,$err);

?>

#!/usr/local/bin/php -q
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: db_foundry_boxes.php,v 1.8 2001/06/13 18:44:09 pfalcon Exp $
  *
  */

require ('squal_pre.php');

/*

	Aggregation script - 

	--
	--	must be run after foundry population and stats
	--

	Since we cannot crunch down all the data on the fly anymore, 
	we need to crunch it down once daily into a separate table

	DEPENDS ON 
		-download stats
		-project weekly metric

For the First-Time Run, Issue this SQL:

CREATE TABLE foundry_project_rankings_agg AS
SELECT 
	DISTINCT ON (fp.foundry_id,pwm.ranking)
	fp.foundry_id,
	groups.group_id,
	groups.group_name,
	groups.unix_group_name,
	pwm.ranking,
	pwm.percentile 
FROM
	groups,project_weekly_metric pwm, foundry_projects fp 
WHERE 
	groups.group_id=pwm.group_id 
	AND pwm.group_id=fp.project_id 
	AND groups.is_public=1 
	AND groups.type=1 
	ORDER BY
	foundry_id ASC, ranking ASC;

CREATE INDEX foundryprojectrankingsagg_foundry_ranking ON foundry_project_rankings_agg (foundry_id,ranking);


DROP TABLE foundry_project_downloads_agg;
CREATE TABLE foundry_project_downloads_agg AS
SELECT 
	DISTINCT ON (fp.foundry_id,frs_dlstats_group_agg.downloads,groups.group_id)
	fp.foundry_id,
	frs_dlstats_group_agg.downloads,
	groups.group_id, 
	groups.group_name, 
	groups.unix_group_name
FROM 
	frs_dlstats_group_agg,groups, foundry_projects fp 
WHERE
	frs_dlstats_group_agg.month='200102' AND
	frs_dlstats_group_agg.day='20' 
	AND frs_dlstats_group_agg.group_id=groups.group_id 
	AND groups.type=1 
	AND groups.is_public=1
	AND groups.group_id=fp.project_id
	ORDER BY foundry_id DESC, downloads DESC, group_id DESC;

CREATE INDEX foundryprojdlsagg_foundryid_dls ON foundry_project_downloads_agg (foundry_id,downloads);

*/


/*
    Create an aggregation table that includes foundry member project rankings
*/
db_begin(SYS_DB_STATS);

$rel = db_query("DELETE FROM foundry_project_rankings_agg;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO foundry_project_rankings_agg
SELECT 
    DISTINCT ON (fp.foundry_id,pwm.ranking)
    fp.foundry_id,
    groups.group_id,
    groups.group_name,
    groups.unix_group_name,
    pwm.ranking,
    pwm.percentile 
FROM
    groups,project_weekly_metric pwm, foundry_projects fp 
WHERE 
    groups.group_id=pwm.group_id 
    AND pwm.group_id=fp.project_id 
    AND groups.is_public=1 
    AND groups.type=1 
    ORDER BY
    foundry_id ASC, ranking ASC;
", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);


/*
	Now crunch down the top downloads for each foundry
*/

$month=date('Ym',(time()-(86400*2)));
$day=date('d',(time()-(86400*2)));

db_begin(SYS_DB_STATS);

$rel = db_query("DELETE FROM foundry_project_downloads_agg;", -1, 0, SYS_DB_STATS);
echo db_error(SYS_DB_STATS);

$rel=db_query("INSERT INTO foundry_project_downloads_agg
SELECT 
    DISTINCT ON (fp.foundry_id,frs_dlstats_group_agg.downloads,groups.group_id)
    fp.foundry_id,
    frs_dlstats_group_agg.downloads,
    groups.group_id, 
    groups.group_name, 
    groups.unix_group_name
FROM 
    frs_dlstats_group_agg,groups, foundry_projects fp 
WHERE
    frs_dlstats_group_agg.month='$month' 
    AND frs_dlstats_group_agg.day='$day' 
    AND frs_dlstats_group_agg.group_id=groups.group_id 
    AND groups.type=1 
    AND groups.is_public=1
    AND groups.group_id=fp.project_id
    ORDER BY foundry_id DESC, downloads DESC, group_id DESC;
", -1, 0, SYS_DB_STATS);

echo db_error(SYS_DB_STATS);

db_commit(SYS_DB_STATS);


?>

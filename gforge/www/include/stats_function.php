<?php
/**
 * stats_function.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

require_once('HTML_Graphs.php');

/**
 * stats_sf_stats() - Get SourceForge stats
 */
function stats_sf_stats() {
	global $sys_datefmt;
/*
	pages/day
*/
	$sql="SELECT * FROM stats_agg_pages_by_day";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		$j=0;
		for ($i=0; $i<$rows; $i++) {
			//echo $i." - ".($i%7)."<br />";
			if ($i % 7 == 0) {
				//echo $i."<br />";
				//increment the new weekly array
				//and set the beginning date for this week
				$j++;
				$name_string[$j]=db_result($result,$i,'day');
				$vals[$j]=0;
			}
			//add today to the week
                        $vals[$j] += db_result($result,$i,'count');
		}
		$j++;
		$vals[$j]='';
		$name_string[$j]='';
		GraphIt($name_string,$vals,'Page Views By Week');
	}

	echo '<p>&nbsp;</p>';

/*
	pages/hour
* /
	$sql="SELECT * FROM stats_agg_pages_by_hour";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		GraphResult($result,'Page Views By Hour');
	}
	echo '<p>';
*/

/*
	Groups added by week
*/
	$sql="select (round((register_time/604800),0)*604800) AS time ,count(*) from groups group by time";
	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		$count=array();
		$dates=array();
		$count=result_column_to_array($result,1);

		for ($i=0;$i<$rows;$i++) {
			//convert the dates and add to an array
			$dates[$i]=date($sys_datefmt,db_result($result,$i,0));
		}
		GraphIt($dates,$count,'New Projects Added Each Week');
	}
	echo '<p>&nbsp;</p>';

/*
	Users added by week
*/
	$sql="select (round((add_date/604800),0)*604800) AS time ,count(*) from users group by time";
	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		$count=array();
		$dates=array();
		$count=result_column_to_array($result,1);

		for ($i=0;$i<$rows;$i++) {
			//convert the dates and add to an array
			$dates[$i]=date($sys_datefmt,db_result($result,$i,0));
		}
		GraphIt($dates,$count,'New Users Added Each Week');
	}
	echo '<p>&nbsp;</p>';

}


/**
 * stats_project_stats() - Get project stats
 */
function stats_project_stats() {
/*
	logo impressions/day
*/
	$sql="SELECT * FROM stats_agg_logo_by_day";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		GraphResult($result,'Logo Showings By Day');
	}

	echo '<p>&nbsp;</p>';

/*
	logo impressions/group
*/
	$sql="SELECT group_id,sum(count) as count FROM stats_agg_logo_by_group GROUP BY group_id";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		GraphResult($result,'Logo Showings By Project');
	}

	echo '<p>&nbsp;</p>';

}


/**
 * stats_browser_stats() - Get browser stats
 */
function stats_browser_stats() {
/*
	Browser
*/
	$sql="SELECT * FROM stats_agg_pages_by_browser";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		GraphResult($result,'Page Views By Browser');
	}
	echo '<p>&nbsp;</p>';

/*
	Platform
*/
	$sql="SELECT * FROM stats_agg_pages_by_platform";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		GraphResult($result,'Page Views By Platform');
	}
	echo '<p>&nbsp;</p>';

/*
	Browser/ver
*/
	$sql="SELECT * FROM stats_agg_pages_by_plat_brow_ver";

	$result = db_query ($sql);
	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<h1>Stats Problem</h1>';
		echo db_error();
	} else {
		ShowResultSet($result,'Page Views By Platform/Browser Version');
	}
	echo '<p>&nbsp;</p>';
}

?>

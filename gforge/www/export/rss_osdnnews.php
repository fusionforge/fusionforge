<?php
/**
 * rss_osdnnews.php - Stats export page for the OSDN newsletter
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @author Darrell Brogdon <dbrogdon@valinux.com>
 * @date 2001-05-22
 * @version   $Id: rss_osdnnews.php,v 1.2 2001/07/10 00:03:08 pfalcon Exp $
 *
 */
require_once('pre.php');
require_once('rss_utils.inc');

if (!$days) {
	$days = '7';
}

//
// Get the dates
//
$udate = date('U') - ($days * 86400);
$currudate = date('U');

$tmp1 = getdate($udate);
$month = pad_number($tmp1['mon']);
$day = pad_number($tmp1['mday']);
$year = $tmp1['year'];

$tmp2 = getdate($currudate);
$curr_month = pad_number($tmp2['mon']);
$curr_day = pad_number($tmp2['mday']);
$curr_year = $tmp2['year'];

$month_day = $year . $month;
$curr_month_day = $curr_year . $curr_month;

header("Content-Type: text/plain");
print '<?xml version="1.0" encoding="utf-8"?>';
?>

<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="http://www.xml.com/xml/news.rss">
        <title><?php echo $GLOBALS['sys_name']; ?> Stats</title>
        <description><?php echo $GLOBALS['sys_name']; ?> Site Statistics</description>
        <item>
            <title>Pageviews</title>
            <description><?php echo get_pageviews(); ?></description>
        </item>
        <item>
            <title>Downloads</title>
            <description><?php echo get_downloads(); ?></description>
        </item>
        <item>
            <title>Registered Users</title>
            <description><?php echo get_registered_users(); ?></description>
        </item>
        <item>
            <title>Registered Projects</title>
            <description><?php echo get_registered_projects(); ?></description>
        </item>
    </channel>
</rdf:RDF>

<?php
/**
 * get_downloads() - Get downloaded files
 *
 * This function retrieves a count of downlaoded files as well as the
 *  total number of Kbytes made up of those files.
 *
 * @returns String A string of data containing file and filesize counts or 'ERROR' on error.
 *
 */
function get_downloads() {
	global $month_day, $day;

	$sql = "SELECT
				sum(s.downloads) AS downloads,
				sum(f.file_size) AS filesize
			FROM
				frs_dlstats_file_agg s,
				frs_file f
			WHERE
				s.file_id=f.file_id
			AND (
					s.month = '$month_day'
				AND
					s.day >= '$day'
				OR
					s.month > '$month_day'
			)";
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$http_stats = db_fetch_array($res);

	//
	// Determine the total number of files and file sizes
	//
	$total_files = number_format($ftp_stats[0] + $http_stats[0]);
	$total_size = number_format(($ftp_stats[1] + $http_stats[1]) / 1000);

	return "$total_files downloads accounting for " . $total_size . "Kb" . " of data.";
}

/**
 * get_registered_users() - Get a count of regiestered users
 *
 * This function retrieves both a count of current users and new users
 *  since '$days' ago.
 *
 * @returns String A string of data containing the current and new users or 'ERROR' on error.
 *
 */
function get_registered_users() { 
	global $udate, $days;

	//
	// Get the current users
	//
	$sql = "SELECT count(*) AS count FROM users WHERE status='A'";
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$curr_users = number_format(db_result($res, 0, 0));

	//
	// Get the users from '$days' offset
	//
	$sql = "SELECT count(*) AS count FROM users WHERE status='A' AND add_date >= '$udate'";
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$new_users = number_format(db_result($res, 0, 0));

	return "$curr_users up $new_users from $days days ago.";
}

/**
 * get_registered_projects() - Get a count of registered projects
 *
 * This function retrieves both a count of current registered projects and 
 *  new projects registered since '$days' ago.
 *
 * @returns String A string of data containing the current and new projects or 'ERROR' on error.
 *
 */
function get_registered_projects() {
	global $udate, $days;

	//
	// Get the current projects
	//
	$sql = "SELECT count(*) AS count FROM groups WHERE status='A'";
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$curr_projx = number_format(db_result($res, 0, 0));

	// 
	// Get the projects from '$days' offset
	//
	$sql = "SELECT count(*) AS count FROM groups WHERE status='A' AND register_time >= '$udate'";
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$new_projx = number_format(db_result($res, 0, 0));
	
	if (!$new_projx) {
		return 'ERROR';
	}

	return "$curr_projx up $new_projx from $days days ago.";
}

/**
 * get_pageviews() - Get the latest pageview count
 *
 * This function retreives the latest site-wide pageview count.
 *
 * @returns Int A count of total pageviews or 'ERROR' on error.
 *
 */
function get_pageviews() {
	$sql = 'SELECT sum(a.site_views) FROM stats_project_all a, groups g WHERE a.group_id=g.group_id';
	$res = db_query($sql, 1, 0, SYS_DB_STATS);
	if (!$res) {
		return 'ERROR';
	}
	$pageviews = number_format(db_result($res, 0, 0));

	return $pageviews;
}

/**
 * pad_number() - Zero-pad a number
 *
 * This function will zero-pad a single digit number
 *
 * @param $num Int A number that may need padding.
 * @returns Int The padded number if $num was a single digit number or 'ERROR' on error.
 *
 */
function pad_number($num) {
	if (strlen($num) < 2) {
		return ('0' . $num);
	} else {
		return $num;
	}
}
?>

#!/usr/bin/php -q
<?php

require_once('squal_pre.php');
require ('common/include/cron_utils.php');

/**
 *
 * Recurses through the /cvsroot directory tree and parses each projects
 * '~/CVSROOT/history' file, building agregate stats on the number of
 * checkouts, commits, and adds to each project over the past 24 hours.
 *
 * @version	 $Id$
 */

$ARGV = $GLOBALS['argv'];
$err = '';
$verbose = 1;
$debug = 1;
$cvsroot = "/cvsroot";

function debug($message) {
	global $debug, $err;
	if($debug) {
		$err .= $message."\n";
	}
}

if ( $ARGV[1] && $ARGV[2] && $ARGV[3] ) {

	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;

	$year = $ARGV[1];
	$month = $ARGV[2];
	$day = $ARGV[3];

} else {
	$local_time = localtime();
		## Start at midnight last night.
	$day_end = gmmktime( 0, 0, 0, $local_time[4] + 1, $local_time[3], $local_time[5] );

	//	$day_end = gmmktime( 0, 0, 0, (gmtime( time() ))[3,4,5] );
					 ## go until midnight yesterday.
	$day_begin = $day_end - 86400;
	//	$day_begin = timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] );

	$year	= gmstrftime("%Y", $day_begin );
	$month	= gmstrftime("%m", $day_begin );
	$day	= gmstrftime("%d", $day_begin );
}

debug(<<<EOF
day begin: $day_begin
day end: $day_end
day: $day
month: $month
year: $year
EOF
);

$month_string = sprintf( "%04d%02d", $year, $month );

if($verbose) {
	$err .= "Parsing cvs logs looking for traffic on day $day, " .
	"month $month, year $year.\n";
}

db_begin();

$rollback = false;

$root_dir =& opendir( $cvsroot );
while ( $group = readdir( $root_dir ) ) {
	if ( $group == '.' || $group == '..' ) 
		continue;
	if ( ! is_dir( "$cvsroot/$group" ) ) 
		continue;

	debug('Working on group '.$group);

	// trying to find the id of the group matching current repository name
	$group_res = db_query( "SELECT group_id FROM groups WHERE
		unix_group_name='$group' AND
		status='A'" );
	$group_id_row_count = db_numrows($group_res);
	if ( $group_id_row = db_fetch_array($group_res) ) {
		$group_id = $group_id_row['group_id'];
	} else {
		$err .= "Group $group does not appear to be active...	skipping.\n";
		continue;
	}
	if ( $group_id_row_count > 1 ) {
		$err .= "Group results are ambiguous... using group_id $group_id.\n";
	}

	$cvs_co		= 0;
	$cvs_commit = 0;
	$cvs_add	= 0;
	$usr_commit = array();
	$usr_add	= array();

	// opening CVSROOT/history file for the current repository
	$hist_file_path = $cvsroot.'/'.$group.'/CVSROOT/history';
	if( !file_exists($hist_file_path) || !is_readable($hist_file_path) || filesize($hist_file_path) == 0) {
		debug('History file for group '.$group.' does not exist or is not readable');
		continue;
	}
	$hist_file =& fopen( $hist_file_path, 'r' );
	if ( ! $hist_file ) {
		debug('Cannot open history file');
		continue;
	}
	$hist_cont = fread( $hist_file, filesize( $hist_file_path ) );
	fclose( $hist_file );
	$hist_lines = explode( "\n", $hist_cont );

	// cleaning stats_cvs_* table for the current day to avoid conflicting index problem
	$sql = "DELETE FROM stats_cvs_group
		WHERE month = '$month_string'
		AND day = '$day'
		AND group_id = '$group_id'";
	$res = db_query($sql);
	if(!$res) {
		$err .= 'Error cleaning stats_cvs_group for current day and current group: '.db_error();
	}

	$sql = "DELETE FROM stats_cvs_user
		WHERE month = '$month_string'
		AND day = '$day'
		AND group_id = '$group_id'";
	$res = db_query($sql);
	if(!$res) {
		$err .= 'Error cleaning stats_cvs_user for current day and current group: '.db_error();
	}

	// analyzing history file
	foreach ( $hist_lines as $hist_line ) {
		if ( preg_match( '/^\s*$/', $hist_line ) ) {
			continue;
		}
		list( $cvstime,$user,$curdir,$module,$rev,$file ) = explode( '|', $hist_line );

		$type = substr($cvstime, 0, 1);
		$time_parsed = hexdec( substr($cvstime, 1, 8) );

		if ( ($time_parsed > $day_begin) && ($time_parsed < $day_end) ) {
			if ( $type == 'M' ) {
				$cvs_commit++;
				$usr_commit[$user]++;
			} elseif ( $type == 'A' ) {
				$cvs_add++;
				$usr_add[$user]++;
			} elseif ( $type == 'O' ) {
				$cvs_co++;
				// ignoring checkouts on a per-user
			}
		} elseif ( $time_parsed > $day_end ) {
			if ( $verbose >= 2 ) {
				$err .= "Short circuting execution, parsed date " .
					"exceeded current threshold.\n";
			}
			break;
		}
	}

	// if we don't have any stats, skipping to next project
	if($cvs_co == 0 && $cvs_add == 0 && $cvs_commit == 0) {
		$err .= "No CVS stats for group ".$group.", skipping to next project";
		continue;
	}

	// inserting group results in stats_cvs_groups
	$sql = "INSERT INTO stats_cvs_group
		(month,day,group_id,checkouts,commits,adds)
		VALUES
		('$month_string',
		'$day',
		'$group_id',
		'$cvs_co',
		'$cvs_commit',
		'$cvs_add')";

	debug($sql);
	if ( !db_query( $sql ) ) {
		$err .= 'Insertion in stats_cvs_group failed: '.$sql.' - '.db_error();
		$rollback = true;
		break;
	}

	// building the user list
	$user_list = array_unique( array_merge( array_keys( $usr_add ), array_keys( $usr_commit ) ) );

	foreach ( $user_list as $user ) {
		// trying to get user id from user name
		$user_res = db_query( "SELECT user_id FROM users WHERE
			user_name='$user'" );
		if ( $user_row = db_fetch_array($user_res) ) {
			$user_id = $user_row['user_id'];
		} else {
			$err .= "User $user was not found...	skipping.\n";
			continue;
		}

		$sql = "INSERT INTO stats_cvs_user
			(month,day,group_id,user_id,commits,adds) VALUES
			('$month_string',
			'$day',
			'$group_id',
			'$user_id',
			'" . ($usr_commit{$user}?$usr_commit{$user}:0) . "',
			'" . ($usr_add{$user}?$usr_add{$user}:0) . "')";

		debug($sql);
		if ( !db_query( $sql )) {
			$err .= 'Insertion in stats_cvs_user failed: '.$sql.' - '.db_error();
			$rollback = true;
			break 2;
		}

	}
}

if ( $rollback ) {
	db_rollback();
} else {
	db_commit();
}

cron_entry(14,$err);

?>

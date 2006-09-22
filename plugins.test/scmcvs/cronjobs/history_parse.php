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
$debug = 0;
$cvsroot = "/cvsroot";
$groups = array();

function debug($message) {
	global $debug, $err;
	if($debug) {
		$err .= $message."\n";
	}
}


function get_all_days(){
	
	global $cvsroot;
	global $err;
	global $verbose;
	global $debug;

	$all_days = array();
  $root_dir =& opendir( $cvsroot );
	while ( $group = readdir( $root_dir ) ) {
		if ( $group == '.' || $group == '..' ) 
			continue;
		if ( ! is_dir( "$cvsroot/$group" ) ) 
			continue;

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

		// analyzing history file
		while (!feof($hist_file)) {
			$hist_line = fgets($hist_file, 1024);
			if ( preg_match( '/^\s*$/', $hist_line ) ) {
				continue;
			}
			list( $cvstime,$user,$curdir,$module,$rev,$file ) = explode( '|', $hist_line );
	
			$time_parsed = hexdec( substr($cvstime, 1, 8) );
			
			$day = gmmktime( 0, 0, 0, gmstrftime("%m", $time_parsed ), gmstrftime("%d", $time_parsed ), gmstrftime("%Y", $time_parsed ) );

			if(!in_array($day, $all_days))
				array_push($all_days, $day);
			
		}
		fclose( $hist_file );
	}

	return $all_days;
}

function process_day($day_begin, $day_end){
	
	global $cvsroot;
	global $err;
	global $verbose;
	global $debug;
	global $groups;
	
	
 	$year	= gmstrftime("%Y", $day_begin );
	$month	= gmstrftime("%m", $day_begin );
	$day	= gmstrftime("%d", $day_begin );
	
	$month_string = sprintf( "%04d%02d", $year, $month );

	if($verbose) {
		$err .= "Parsing cvs logs looking for traffic on day $day, " .
		"month $month, year $year.\n";
	}

	$rollback = false;
	
	$root_dir =& opendir( $cvsroot );
	while ( $group = readdir( $root_dir ) ) {
		if ( $group == '.' || $group == '..' ) 
			continue;
		if ( ! is_dir( "$cvsroot/$group" ) ) 
			continue;
	
		debug('Working on group '.$group);
	
		if (array_key_exists($group, $groups)) {
			$group_id = $groups[$group];
		} else {
			$err .= "Group $group does not appear to be active...	skipping.\n";
			continue;
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
		while (!feof($hist_file)) {
			$hist_line = fgets($hist_file, 1024);
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
				} elseif ( $type == 'O' || $type == 'E' ) {
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
		fclose( $hist_file );
		// if we don't have any stats, skipping to next project
		if($cvs_co == 0 && $cvs_add == 0 && $cvs_commit == 0) {
			$err .= "No CVS stats for group ".$group.", skipping to next project\n";
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
				$user_id = $user_row[0];
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
	return $rollback;
}

$res = db_query( "SELECT unix_group_name, group_id FROM groups WHERE status='A'" );
while ($row = db_fetch_array($res) ) {
	$groups[$row['unix_group_name']] = $row['group_id'];
}
if (count($groups) < 1) {
	$err = "Could not fetch list of group IDs";
	cron_entry(14,$err);
	exit;
}

if ( $ARGV[1] && $ARGV[2] && $ARGV[3] ) {
	
	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;
 
	db_begin();
	$rollback = process_day($day_begin, $day_end);
} else if($ARGV[1]=='all' && !$ARGV[2] && !$ARGV[3]) { 
  
	// Heavy (rarely used) operation, allow for 8hrs
	ini_set('max_execution_time', '30000');
	$all_days = &get_all_days();
	foreach ( $all_days as $day ) {
		echo date('Y-m-d', $day)."\n";  	
		db_begin();
		$rollback = process_day($day, $day + 86400);
		
		if($rollback) {
			break;
		} else {
			db_commit();
		}
	}
   
} else {

	$local_time = localtime();
		## Start at midnight last night.
	$day_end = gmmktime( 0, 0, 0, $local_time[4] + 1, $local_time[3], $local_time[5] );

	//	$day_end = gmmktime( 0, 0, 0, (gmtime( time() ))[3,4,5] );
					 ## go until midnight yesterday.
	$day_begin = $day_end - 86400;
	//	$day_begin = timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] );
	db_begin();
	$rollback = process_day($day_begin, $day_end);

}

if ( $rollback ) {
	db_rollback();
} else {
	db_commit();
}

cron_entry(14,$err);

?>
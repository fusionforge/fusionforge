<?php
/**
* This file is part of GForge.
* 
* This is a translation if svn-stats.pl
*
* GForge is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* GForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GForge; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
*/
require ('squal_pre.php');
require ('common/include/cron_utils.php');


$pluginname = "scmsvn" ;
$svnroot = "/home/norberto/test/";

db_begin();

$pluginid = get_plugin_id($pluginname);


db_begin();

if ( $ARGV[1] && $ARGV[2] && $ARGV[3] ) {
	
	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;
 
  $rollback = process_day($day_begin, $day_end);
} else if($ARGV[1]=='all' && !$ARGV[2] && !$ARGV[3]) { 
  
  $all_days = &get_all_days();
  foreach ( $all_days as $day ) {
			echo $day;  	
  	$rollback = process_day($day, $day + 86400);
  	
  	if($rollback)
  		break;
  	
  }
   
} else {

	$local_time = localtime();
		## Start at midnight last night.
	$day_end = gmmktime( 0, 0, 0, $local_time[4] + 1, $local_time[3], $local_time[5] );

	//	$day_end = gmmktime( 0, 0, 0, (gmtime( time() ))[3,4,5] );
					 ## go until midnight yesterday.
	$day_begin = $day_end - 86400;
	//	$day_begin = timegm( 0, 0, 0, (gmtime( time() - 86400 ))[3,4,5] );

	$rollback = process_day($day_begin, $day_end);

}

if ( $rollback ) {
	db_rollback();
} else {
	db_commit();
}

 

function process_day($day_begin, $day_end){
	
	global $err;
	global $verbose;
	global $pluginid;
	
	
 	$year	= gmstrftime("%Y", $day_begin );
	$month	= gmstrftime("%m", $day_begin );
	$day	= gmstrftime("%d", $day_begin );
	
	$month_string = sprintf( "%04d%02d", $year, $month );
	
	
	$res = db_query("SELECT group_plugin.group_id, groups.unix_group_name
				FROM group_plugin, groups
				WHERE group_plugin.plugin_id = $pluginid
				AND group_plugin.group_id = groups.group_id");
	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		return 1;
	}
		
	while ( $groups =& db_fetch_array($res) ) {
		$svnroot_group = $svnroot . $groups[1];	
		$currev = shell_exec( "svnlook youngest ". $svnroot_group ) ;
		$adds = 0 ;
		$deletes = 0 ;
		$updates = 0 ;
		$commits = 0 ;
		$rev = 0;
		while ($rev <= $currev) {
			$date = shell_exec( "svnlook date -r$rev ". $svnroot_group );
			$time_parsed = strtotime($date);
			if ($day_begin >= $time_parsed && $time_parsed <= $day_end) {
				$commits++;
				$author = shell_exec( "svnlook author -r$rev $svnroot_group ");
				$output = shell_exec("svnlook changed -r$rev $svnroot_group |");
				$lines = explode("\n", $output);
				foreach ($lines as $line) {
					if (!$line == "") {
						if (substr($line,0,1) == "A")
							$adds++;
						if (substr($line,0,1) == "D")
							$deletes++;
						if (substr($line,0,1) == "U")
							$updates++;
					}
				}
			}
			$rev++;
		} 
		
		$user_res = db_query( "SELECT user_id FROM users WHERE
		user_name='$author'" );
		if ( $user_row = db_fetch_array($user_res) ) {
			$user_id = $user_row[0];
		} else {
			$err .= "User $user was not found...	skipping.\n";
			break;
		}

		// cleaning stats_cvs_* table for the current day to avoid conflicting index problem
		$sql = "DELETE FROM stats_cvs_group
			WHERE month = '$month_string'
			AND day = '$day'
			AND group_id = '$groups[0]'";
		$res = db_query($sql);
		if(!$res) {
			$err .= 'Error cleaning stats_cvs_group for current day and current group: '.db_error();
			break;
		}
	
		$sql = "DELETE FROM stats_cvs_user
			WHERE month = '$month_string'
			AND day = '$day'
			AND group_id = '$groups[0]'";
		$res = db_query($sql);
		if(!$res) {
			$err .= 'Error cleaning stats_cvs_user for current day and current group: '.db_error();
			break;
		}
		
		$sql = "INSERT INTO stats_cvs_group
		(month,day,group_id,checkouts,commits,adds)
		VALUES
		('$month_string',
		'$day',
		'$groups[0]',
		'0',
		'$commits',
		'$adds')";

		if ( !db_query( $sql ) ) {
			$err .= 'Insertion in stats_cvs_group failed: '.$sql.' - '.db_error();
			break;
		}
		
		$sql = "INSERT INTO stats_cvs_user
		(month,day,group_id,user_id,commits,adds) VALUES
		('$month_string',
		'$day',
		'$groups[0]',
		'$user_id',
		'$commits',
		'$adds')";

		if ( !db_query( $sql )) {
			$err .= 'Insertion in stats_cvs_user failed: '.$sql.' - '.db_error();
			break;
		}

	}
	return 0;
}

function get_plugin_id($pluginname){
	$res = db_query("SELECT plugin_id FROM plugins WHERE plugin_name = '".$pluginname."'");	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		db_rollback();
		exit;
	}
	if ($row =& db_fetch_array($res)) {
		$plugin_id = $row[0];
	}
 
	return $plugin_id;
}


function get_all_days(){
	
	global $cvsroot;
	global $err;
	global $verbose;
	global $debug;

	$res = db_query("SELECT group_plugin.group_id, groups.unix_group_name
				FROM group_plugin, groups
				WHERE group_plugin.plugin_id = $pluginid
				AND group_plugin.group_id = groups.group_id");
	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		return 1;
	}
		
	while ( $groups =& db_fetch_array($res) ) {
		$svnroot_group = $svnroot . $groups[1];	
		$currev = shell_exec( "svnlook youngest ". $svnroot_group ) ;
		$rev = 0;
		while ($rev <= $currev) {
			$date = shell_exec( "svnlook date -r$rev ". $svnroot_group );
			$time_parsed = strtotime($date);
			
			if(!in_array($time_parsed, $all_days))
				array_push($all_days, $time_parsed);
				
		}
	}

	return $all_days;
}

?>
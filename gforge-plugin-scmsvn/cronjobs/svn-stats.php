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
require_once ('squal_pre.php');
require_once ('common/include/cron_utils.php');
require_once ('plugins/scmsvn/config.php');

$pluginname = "scmsvn" ;
// this variable should probably be moved to this plugin's config.php
$svnroot = "/var/svn/";
$ARGV = $GLOBALS['argv'];
$err = '';
$debug = 1;

// You should set this variable manually in the configuration file
if (!isset($svnlook_bin)) {
	$svnlook_bin = "svnlook";	//NOTE: This assumes svnlook is in the PATH
}

function debug($message) {
	global $debug, $err;
	if($debug) {
		$err .= $message."\n";
	}
}

db_begin();

$pluginid = get_plugin_id($pluginname);

//lenp db_begin();

if ( $ARGV[1] && $ARGV[2] && $ARGV[3] ) {
	//$ARGV[1] = Year
	//$ARGV[2] = Month
	//$ARGV[3] = Day
	
	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;
 
  $rollback = process_day($day_begin, $day_end);
} else if($ARGV[1]=='all' && !$ARGV[2] && !$ARGV[3]) { 
  
  $all_days = &get_all_days();
  foreach ( $all_days as $day ) {
	debug('Processing day '.$day);
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

// lenp Not sure about this...
cron_entry(14,$err);
//echo $err."\n";

function process_day($day_begin, $day_end){
	
	global $err;
	global $pluginid;
	global $svnroot;
	global $debug;
	global $svnlook_bin;
	
	
 	$year	= gmstrftime("%Y", $day_begin );
	$month	= gmstrftime("%m", $day_begin );
	$day	= gmstrftime("%d", $day_begin );
	
	$month_string = sprintf( "%04d%02d", $year, $month );

	debug('Checking with SVN for actions on day '.$day.' month '.$month.' year '.$year);
	
	$rollback = false;
	
	$res = db_query("SELECT group_plugin.group_id, groups.unix_group_name
				FROM group_plugin, groups
				WHERE group_plugin.plugin_id = $pluginid
				AND groups.use_scm = 1
				AND group_plugin.group_id = groups.group_id");
	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		return 1;
	}
		
	while ( $groups =& db_fetch_array($res) ) {
	        
		debug('Working on group '.$groups[1]);
		
		$svnroot_group = "$svnroot".$groups[1];	
		if (!is_dir($svnroot_group)) {
			debug("Skipping repository ".$svnroot_group.": doesn't exist");
		}
		exec($svnlook_bin." youngest ".$svnroot_group, $svn_out, $svn_retval);
		$svn_out = implode('', $svn_out);
		// Handle cases where the subversion repository is duff
		if ($svn_retval) {
			debug("Skipping repository ".$svnroot_group.": ".$svn_out);
			continue;
		}
		$currev = $svn_out;
		
		$adds = 0 ;
		$deletes = 0 ;
		$updates = 0 ;
		$commits = 0 ;
		$usr_adds = array();
		$usr_deletes = array();
		$usr_updates = array();
		$rev = 0;
		// lenp Do we always have to start at rev 0 !!!???
		while ($rev <= $currev) {
			$date = shell_exec( $svnlook_bin." date -r$rev ". $svnroot_group );

			$time_parsed = strtotime($date);

			if ($day_begin <= $time_parsed && $day_end >= $time_parsed) {
				$commits++;
				$author = shell_exec($svnlook_bin." author -r$rev $svnroot_group ");
				$author = rtrim($author);

				$output = shell_exec($svnlook_bin." changed -r$rev $svnroot_group");
				$lines = explode("\n", $output);
				foreach ($lines as $line) {
					if (!$line == "") {
						if (substr($line,0,1) == "A") {
							$adds++;
							$usr_adds[$author]++;
						}
						else if (substr($line,0,1) == "D") {
							$deletes++;
							$usr_deletes[$author]++;
						}
						else if (substr($line,0,1) == "U") {
							$updates++;
							$usr_updates[$author]++;
						}
					}
				}
			}
			$rev++;
		} 

		// (marcelo) This piece of code should probably be deleted
/*
		$user_res = db_query( "SELECT user_id FROM users WHERE
		user_name='$author'" );
		if ( $user_row = db_fetch_array($user_res) ) {
			$user_id = $user_row[0];
		} else {
			$err .= "User $author was not found...	skipping.\n";
			break;
		}
*/

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
		'$updates',
		'$adds')";

		if ( !db_query( $sql ) ) {
			$err .= 'Insertion in stats_cvs_group failed: '.$sql.' - '.db_error();
			break;
		}

		// building the user list
		$user_list = array_unique(array_merge(array_keys($usr_adds),array_keys($usr_updates)));
		foreach ($user_list as $user) {
			// trying to get user id from user name
			$user_res = db_query("SELECT user_id FROM users WHERE user_name='$user'");
			if($user_row = db_fetch_array($user_res)) {
				$user_id = $user_row[0];
			} else {
				$err .= "User $user was not found... skipping.\n";
				continue;
			}
			
			$sql = "INSERT INTO stats_cvs_user
                		(month,day,group_id,user_id,commits,adds) VALUES
		                ('$month_string',
				'$day',
				'$groups[0]',
				'$user_id',
				'" . ($usr_updates{$user}?$usr_updates{$user}:0)  . "',
				'" . ($usr_adds{$user}?$usr_adds{$user}:0)  . "')";

			debug($sql);

		        if ( !db_query( $sql )) {
				$err .= 'Insertion in stats_cvs_user failed: '.$sql.' - '.db_error();
				break 2;
			}
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
	global $pluginid;
	global $svnroot;
	global $svnlook_bin;

	$all_days = array();

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
		$currev = shell_exec($svnlook_bin." youngest ". $svnroot_group ) ;

		$rev = 0;
		while ($rev <= $currev) {
			$date = shell_exec($svnlook_bin." date -r$rev ". $svnroot_group );
			$time_parsed = strtotime($date);

			$day = gmmktime( 0, 0, 0, gmstrftime("%m", $time_parsed), gmstrftime("%d", $time_parsed), gmstrftime("%Y", $time_parsed));
			
			if(!in_array($day, $all_days)) {
				array_push($all_days, $day);
			}
			$rev++;
				
		}
	}

	return $all_days;
}

?>

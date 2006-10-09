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
// This variable should probably be moved to this plugin's config.php
$svnroot = $sys_chroot$svndir_prefix;
$ARGV = $GLOBALS['argv'];
$err = '';
$debug = 0;

// You should set this variable manually in the configuration file
if (!isset($svn_bin)) {
	$svn_bin = "svn"; // Assumes svn is in the PATH
}

// Set up some globals for use when parsing the XML log file
$last_user = '';
$last_time = '';
$last_tag = '';
$adds = array();
$deletes = array();
$updates = array();
$commits = array();
$usr_adds = array();
$usr_deletes = array();
$usr_updates = array();
$start_time = 0;
$end_time = 0;
$time_ok = false;
$date_key = '';
$user_list = array();

// Handle closing an element.
// We are only actually interested in LOGENTRY type things, as this is 
// where we update our commit total
function endElement($parser, $name) {
  debug ("endelement $name") ;
	global $time_ok, $last_tag, $commits, $date_key;
	if ($name == "LOGENTRY" && $time_ok) {
		$commits[$date_key]++;
	}
	$last_tag = "";
}

// Handle character data
// We only care about AUTHOR and DATE entries, so we can figure out who
// is doing things and when. We use last_tag to keep track of the
// last element we were in.
function charData($parser, $chars) {
  debug ("chardata $chars") ;
	global $last_tag, $last_user, $last_time, $start_time,
			$end_time, $time_ok, $date_key, $user_list;
	switch ($last_tag) {
		case "AUTHOR":
			$last_user = strtolower(trim($chars));
			// We can save time by looking up users and caching them
			if (!array_key_exists($last_user, $user_list)) {
				// trying to get user id from user name
				$user_res = db_query("SELECT user_id FROM users WHERE " .
									"user_name='$last_user'");
	            if ($user_row = db_fetch_array($user_res)) {
					$user_list[$last_user] = $user_row[0];
				} else {
					// We don't know about them, so give them the 
					// nonsensical -1 value
					$user_list[$last_user] = -1;
				}
			}
			debug("Got author $last_user");
			break;
		case "DATE":
			$chars = preg_replace('/T(\d\d:\d\d:\d\d)\.\d+Z?$/', ' ${1}', $chars);
			$last_time = strtotime($chars);
			// If we don't have a start end end time, we should assume
			// that the time is OK.
			// If we do have the start and end time, make sure this event
			// is within those times.
			debug ("start $start_time, end $end_time, last $last_time");
			if (!$start_time && !$end_time) {
				$time_ok = true;
			} elseif ($start_time <= $last_time && $last_time <= $end_time) {
				$time_ok = true;
			} else {
				$time_ok = false;
			}
			// We need to set up the date key that we use when generating
			// totals
			if ($time_ok) {
	 			$year	= gmstrftime("%Y", $last_time);
				$month	= gmstrftime("%m", $last_time);
				$day	= gmstrftime("%d", $last_time);
				$month_string = sprintf( "%04d%02d", $year, $month );
				$date_key = "${month_string}-$day";
			}
			debug("Got date $chars $last_time $date_key");
			break;
	}
}

function startElement($parser, $name, $attrs) {
  debug ("startelement $name");
    global $last_user, $last_time, $last_tag, $time_ok,
			$adds, $deletes, $updates, $commits, $date_key,
			$usr_adds, $usr_deletes, $usr_updates;
	$last_tag = $name;
	switch($name) {
		case "LOG":
			// Clear up at the start of a new log file
			$adds = array();
			$deletes = array();
			$updates = array();
			$commits = array();
			$usr_adds = array();
			$usr_deletes = array();
			$usr_updates = array();
			$date_key = '';
			break;
		case "LOGENTRY":
			// Make sure we clean up before doing a new log entry
			$last_user = "";
			$last_time = "";
			break;
		case "PATH":
			if ($time_ok && $date_key) {
				if ($attrs['ACTION'] == "M") {
					$updates[$date_key]++;
					if ($last_user) {
						$usr_updates[$date_key][$last_user]++;
					}
				} elseif ($attrs['ACTION'] == "A") {
					$adds[$date_key]++;
					if ($last_user) {
						$usr_adds[$date_key][$last_user]++;
					}
				} elseif ($attrs['ACTION'] == "D") {
					$deletes[$date_key]++;
					if ($last_user) {
						$usr_deletes[$date_key][$last_user]++;
					}
				}
			}
			break;
	}
}

function debug($message) {
	global $debug, $err;
	if ($debug) {
		$err .= $message."\n";
	}
	if ($debug > 1) {
		echo "$message\n";
		flush();
		ob_end_flush();
	}
}

db_begin();

$pluginid = get_plugin_id($pluginname);

if ($ARGV[1] && $ARGV[2] && $ARGV[3]) {
	//$ARGV[1] = Year
	//$ARGV[2] = Month
	//$ARGV[3] = Day
	
	$day_begin = gmmktime( 0, 0, 0, $ARGV[2], $ARGV[3], $ARGV[1] );
	//	$day_begin = timegm( 0, 0, 0, $ARGV[2], $ARGV[1] - 1, $ARGV[0] - 1900 );
	$day_end = $day_begin + 86400;
 
	$rollback = process_day($day_begin, $day_end);
} else if ($ARGV[1]=='all' && !$ARGV[2] && !$ARGV[3]) { 
	// Do ALL the days
	debug('Processing all days');
	$rollback = process_day();

} else {
	// Do yesterday
	$local_time = localtime();
	// Start at midnight last night.
	$day_end = gmmktime(0, 0, 0, $local_time[4] + 1, 
						$local_time[3], $local_time[5] );
	// go until midnight yesterday.
	$day_begin = $day_end - 86400;

	$rollback = process_day($day_begin, $day_end);
}

if ($rollback) {
	db_rollback();
} else {
	db_commit();
}

// lenp Not sure about this...
cron_entry(24,$err);

function process_day($day_begin=0, $day_end=0) {
	global $err;
	global $pluginid;
	global $svnroot;
	global $svn_bin;
	global $start_time;
	global $end_time;
	global $date_key;
	global $time_ok;
	global $last_time;
	global $user_list;
	global $usr_adds, $usr_deletes, $usr_updates, $adds, $deletes, $updates,
			$commits;

	$start_time = $day_begin;
	$end_time = $day_end;

	if ($day_begin && $day_end) {
	 	$year	= gmstrftime("%Y", $day_begin );
		$month	= gmstrftime("%m", $day_begin );
		$day	= gmstrftime("%d", $day_begin );
		$month_string = sprintf( "%04d%02d", $year, $month );
		debug('Checking with SVN for actions on day ' .
				$day.' month '.$month.' year '.$year);
		$date_key = "${month_string}-$day";
	}

	$rollback = false;
	
	// Lookup all the groups that use this plugin, use SCM and
	// are active
	$res = db_query("SELECT group_plugin.group_id, groups.unix_group_name
				FROM group_plugin, groups
				WHERE group_plugin.plugin_id = $pluginid
				AND groups.use_scm = 1
				AND groups.status = 'A'
				AND group_plugin.group_id = groups.group_id");
	
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		return 1;
	}

	while ($groups =& db_fetch_array($res)) {
		$logfile = tempnam("/tmp", "svnlog");
		debug('Working on group ' . $groups[1]);

		$svnroot_group = "$svnroot" . "/" . $groups[1];	
		if (!is_dir($svnroot_group)) {
			debug("Skipping repository $svnroot_group : doesn't exist");
		}

		// Now, examine the log file for the group

		$cmd = "$svn_bin log file://$svnroot_group --xml -v -q > $logfile";
		debug($cmd);
		exec($cmd, $cmd_out, $cmd_retval);
		$xml_parser = xml_parser_create();
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser, "charData");

		if (!$cmd_retval) {
			$xmlhandle = fopen($logfile, "r");
			while (!feof($xmlhandle) && 
					$data = fread($xmlhandle, 8192)) {
				if (!xml_parse($xml_parser, $data, feof($xmlhandle))) {
					debug("Unable to parse XML with error " .
							xml_error_string(xml_get_error_code($xml_parser)) .
							" on line " .
							xml_get_current_line_number($xml_parser));
					$rollback = true;
					break;
				}
				// See if we can drop out of parsing, since
				// the time of the last event is less than our start time
				// and, because the log is in newest first order,
				// we will never run into a valid event after this
				if (!$time_ok && $last_time && $last_time < $start_time) {
					break;
				}
			}
		} else {
			// Looks like we couldn't open svn :(
			// Fail the run
			if (is_file($logfile)) {
				debug("Removing log file $logfile");
				unlink($logfile);
			}
			debug("Unable to svn group $svnroot_group");
			$rollback = true;
			xml_parser_free($xml_parser);
			break;
		}
		xml_parser_free($xml_parser);
		if (is_file($logfile)) {
			debug("Removing log file $logfile");
			unlink($logfile);
		}

		// We have to loop through all the days we've looked at,
		// extracting the month and day from the date_key
		foreach (array_keys($commits) as $key) {
			// Cleaning stats_cvs_* table for the current day to
			// avoid conflicting index problem.
			list($m, $d) = split("-", $key);
			$del_grp_sql = "DELETE FROM stats_cvs_group
				WHERE month = '$m'
				AND day = '$d'
				AND group_id = '$groups[0]'";
			$del_grp_res = db_query($del_grp_sql);
			debug($del_grp_sql);
			if (!$del_grp_res) {
				$err .= 'Error cleaning stats_cvs_group for ' .
						"current day $d, month $m and current group id " .
						$groups[0] . ': ' . db_error();
				// Break out completely
				$rollback = true;
				debug("Unable to clean stats_cvs_group");
				break 2;
			}
	
			$del_usr_sql = "DELETE FROM stats_cvs_user
				WHERE month = '$m'
				AND day = '$d'
				AND group_id = '$groups[0]'";
			$del_usr_res = db_query($del_usr_sql);
			debug($del_usr_sql);
			if (!$del_usr_res) {
				$err .= 'Error cleaning stats_cvs_user for ' .
						"current day $d, month $m and current group id " .
						$groups[0] . ': ' . db_error();
				// Break out completely
				$rollback = true;
				debug("Unable to clean stats_cvs_user");
				break 2;
			}

			$ins_grp_sql = "INSERT INTO stats_cvs_group
				(month,day,group_id,checkouts,commits,adds)
				VALUES
				('$m',
				'$d',
				'$groups[0]',
				'0',
				'" . ($updates[$key] ? $updates[$key] : 0) . "',
				'" . ($adds[$key] ? $adds[$key] : 0) . "')";

			debug($ins_grp_sql);
			if (!db_query($ins_grp_sql)) {
				$err .= 'Insertion in stats_cvs_group failed: ' . 
						$ins_grp_sql . ' - ' . db_error();
				// Break out completely
				$rollback = true;
				debug("Unable to insert to stats_cvs_group");
				break 2;
			}

			// Now, loop through the users we've seen
			// We want to update any user who's got an add, delete
			// or update
			foreach (array_keys($user_list) as $user_name) {
				if ($user_list{$user_name} > 0 &&
					((array_key_exists($key, $usr_updates) &&
					  array_key_exists($user_name, $usr_updates[$key])) ||
					 (array_key_exists($key, $usr_deletes) &&
					  array_key_exists($user_name, $usr_deletes[$key])) ||
					 (array_key_exists($key, $usr_adds) &&
                      array_key_exists($user_name, $usr_adds[$key])))) {

					$user_id = $user_list{$user_name};
					$usr_sql = "INSERT INTO stats_cvs_user
						(month,day,group_id,user_id,commits,adds) VALUES
						('$m',
					'$d',
					'$groups[0]',
					'$user_id',
					'" . ($usr_updates{$key}{$user_name}?$usr_updates{$key}{$user_name}:0)  . "',
					'" . ($usr_adds{$key}{$user_name}?$usr_adds{$key}{$user_name}:0)  . "')";

					debug($usr_sql);

					if (!db_query($usr_sql)) {
						$err .= 'Insertion in stats_cvs_user failed: ' . 
								$usr_sql . ' - ' . db_error();
						$rollback = true;
						debug("Unable to insert into stats_cvs_user");
						break 3;
					}
				}
			}
		}
		debug("Finished group : $groups[1]");
	}
	return $rollback;
}

function get_plugin_id($pluginname){
	$res = db_query("SELECT plugin_id FROM plugins WHERE plugin_name = '" .
					$pluginname."'");	
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


?>

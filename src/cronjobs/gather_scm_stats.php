#! /usr/bin/php -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2009, Roland Mas
 * Copyright 2013, Christoph Niethammer
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

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCMPlugin.class.php' ;

session_set_admin () ;

setup_plugin_manager () ;


date_default_timezone_set(@date_default_timezone_get());
$endtime   = time();
$starttime = $endtime - 86400 ;

$shortopts = "v";       // enable verbose mode
$longopts = array(
	"all",              // consider all commits from registration date saved in db up to now
	"allepoch",         // consider all commits from start of the epoch (1970-01-01) up to now
	"startdate:",       // consider only commits later than given startdate (YYYY-MM-DD), overwritten by all/allepoch
	"enddate:",         // consider only commits before given enddate (YYYY-MM-DD), overwritten by all/allepoch
	"group_id:",        // update data only for group with given id
	"unix_group_name:"  // update data only for group with given unix name
);
$options = getopt($shortopts, $longopts);
$EXTRA_WHERE = "";
$verbose = false;

$qpa = db_construct_qpa(false, 'SELECT group_id, group_name, register_time FROM groups WHERE status=$1 AND use_scm=$2', array ('A', 1));

if ( isset($options['v']) ) {
	$verbose = true;
}
if ( isset($options['startdate']) ) {
	$starttime = strtotime($options['startdate']) ;
	($verbose) && print "Startdate: ".date("Y-m-d", $starttime)."\n";
}
if ( isset($options['enddate']) ) {
	$endtime = strtotime($options['enddate']) ;
	($verbose) && print "Enddate:   ".date("Y-m-d", $endtime)."\n";
}
if ( isset($options['group_id']) ) {
	($verbose) && print "group_id:      ".$options['group_id']."\n";
	$qpa = db_construct_qpa($qpa, ' AND group_id=$1', array($options['group_id']));
}
if ( isset($options['unix_group_name']) ) {
	($verbose) && print "unix_group_name: ".$options['unix_group_name']."\n";
	$qpa = db_construct_qpa($qpa, ' AND unix_group_name=$1', array($options['unix_group_name']));
}

$qpa = db_construct_qpa($qpa, ' ORDER BY group_id DESC');

$res = db_query_qpa($qpa);

if (!$res) {
	$this->setError('Unable to get list of projects using SCM: '.db_error());
	return false;
}


$output = '';
while ($data = db_fetch_array ($res)) {
	($verbose) && print "Processing GroupId ".$data['group_id']." (".$data['group_name'].")\n";
	$time = $starttime;
	$etime = $endtime;
	if ( isset($options['all']) ) {
		$time = date($data['register_time']);
		$etime = time();
	}
	if ( isset($options['allepoch']) ) {
		$time = 0;
		$etime = time();
	}
	$last_seen_day = '' ;
	while ($time < $etime) {
		$day = date ('Y-m-d', $time) ;
		if ($day != $last_seen_day) {
			$last_seen_day = $day ;
			($verbose) && print "processing $day\n" ;
			$hook_params = array ('group_id' => $data['group_id'],
						  'mode' => 'day',
						  'year' => date ('Y', $time),
						  'month' => date ('n', $time),
						  'day' => date ('j', $time)) ;
			plugin_hook ('scm_gather_stats', $hook_params) ;
		}
		$time = $time + 86400 ;
	}
}

if ($output) cron_entry(28, $output);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

#! /usr/bin/php -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2009, Roland Mas
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
			 
setup_plugin_manager () ;

$res = db_query_params ('SELECT group_id FROM groups WHERE status=$1 AND use_scm=1 ORDER BY group_id DESC',
			array ('A')); 
if (!$res) {
	$this->setError('Unable to get list of projects using SCM: '.db_error());
	return false;
}

$mode = 'day' ;
if (count ($argv) >= 2 && $argv[1] == '--all') {
	$mode = 'all' ;
}

while ($data = db_fetch_array ($res)) {
	if ($mode == 'day') {
		$time = time () - 86400 ;
		$hook_params = array ('group_id' => $data['group_id'],
				      'mode' => 'day',
				      'year' => date ('Y', $time),
				      'month' => date ('n', $time),
				      'day' => date ('j', $time)) ;
		plugin_hook ('scm_gather_stats', $hook_params) ;
	} elseif ($mode == 'all') {
		$last_seen_day = '' ;
		$time = 0 ;
		$now = time () ;
		while ($time < $now) {
			$day = date ('Y-m-d', $time) ;
			print "processing $day\n" ;
			if ($day != $last_seen_day) {
				$hook_params = array ('group_id' => $data['group_id'],
						      'mode' => 'day',
						      'year' => date ('Y', $time),
						      'month' => date ('n', $time),
						      'day' => date ('j', $time)) ;
				plugin_hook ('scm_gather_stats', $hook_params) ;
				$last_seen_day = $day ;
			}
			$time = $time + 80000 ;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

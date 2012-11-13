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

session_set_admin () ;

setup_plugin_manager () ;

$res = db_query_params ('SELECT group_id, register_time FROM groups WHERE status=$1 AND use_scm=1 ORDER BY group_id DESC',
			array ('A'));
if (!$res) {
	$this->setError('Unable to get list of projects using SCM: '.db_error());
	return false;
}

$mode = 'day' ;
$now = time();
if (count ($argv) >= 2 && $argv[1] == '--all') {
	$mode = 'all' ;
} elseif (count ($argv) == 2) {
	$now = $argv[1] ;
}

$output = '';
while ($data = db_fetch_array ($res)) {
	if ($mode == 'day') {
		$time = $now - 86400 ;
		$hook_params = array ('group_id' => $data['group_id'],
				      'mode' => 'day',
				      'year' => date ('Y', $time),
				      'month' => date ('n', $time),
				      'day' => date ('j', $time)) ;
		plugin_hook ('scm_gather_stats', $hook_params) ;
	} elseif ($mode == 'all') {
		$time = $data['register_time'];
		if (!$time) continue;
		while ($time < $now) {
			$hook_params = array ('group_id' => $data['group_id'],
						      'mode' => 'day',
						      'year' => date ('Y', $time),
						      'month' => date ('n', $time),
						      'day' => date ('j', $time)) ;
			plugin_hook ('scm_gather_stats', $hook_params) ;
			$time = $time + 86400 ;
		}
	}
}

if ($output) cron_entry(28, $output);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

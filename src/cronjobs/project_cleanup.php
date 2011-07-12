#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

db_begin();

// one hour ago for projects
$then=(time()-3600);
db_query_params ('DELETE FROM groups WHERE status=$1 and register_time < $2',
		 array ('I',
			$then));
$err .= db_error();

// one week ago for users
$then=(time()-604800);
if (USE_PFO_RBAC) {
	db_query_params ('DELETE FROM pfo_user_role WHERE EXISTS (SELECT user_id FROM users
WHERE status=$1 and add_date < $2 AND users.user_id=pfo_user_role.user_id)',
			 array ('P',
				$then));
	$err .= db_error();
	db_query_params ('DELETE FROM user_group WHERE EXISTS (SELECT user_id FROM users
WHERE status=$1 and add_date < $2 AND users.user_id=user_group.user_id)',
			 array ('P',
				$then));
	$err .= db_error();
} else {
	db_query_params ('DELETE FROM user_group WHERE EXISTS (SELECT user_id FROM users
WHERE status=$1 and add_date < $2 AND users.user_id=user_group.user_id)',
			 array ('P',
				$then));
	$err .= db_error();
}
$result = db_query_params ('SELECT user_id, email FROM users WHERE status=$1 and add_date < $2',
			   array ('P',
				  $then));
if (db_numrows($result)) {

  // Plugins subsystem
  require_once('common/include/Plugin.class.php') ;
  require_once('common/include/PluginManager.class.php') ;

  // SCM-specific plugins subsystem
  require_once('common/include/SCMPlugin.class.php') ;

  setup_plugin_manager () ;

  while ($row = db_fetch_array($result)) {
    $hook_params = array();
    $hook_params['user'] = &user_get_object($row['user_id']);
    $hook_params['user_id'] = $row['user_id'];
    plugin_hook ("user_delete", $hook_params);
  }
}

db_query_params ('DELETE FROM users WHERE status=$1 and add_date < $2',
		 array ('P',
			$then));
$err .= db_error();

#30 days ago for sessions
$then=(time()-(30*60*60*24));
db_query_params ('DELETE FROM user_session WHERE time < $1',
			array ($then));
$err .= db_error();

#one month ago for preferences
$then=(time()-604800*4);
db_query_params ('DELETE FROM user_preferences WHERE set_date < $1',
			array ($then));
$err .= db_error();

#3 weeks ago for jobs
$then=(time()-604800*3);
db_query_params ('UPDATE people_job SET status_id = 3 where post_date < $1',
			array ($then));
$err .= db_error();

#1 day ago for form keys
$then=(time()-(60*60*24));
db_query_params ('DELETE FROM form_keys WHERE creation_date < $1',
			array ($then));
$err .= db_error();

db_commit();
if (db_error()) {
	$err .= "Error: ".db_error();
}

cron_entry(7,$err);

?>

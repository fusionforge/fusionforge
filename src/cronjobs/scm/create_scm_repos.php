#! /usr/bin/php -f
<?php
/**
 * Create/update/delete SCM repositories
 *
 * Copyright 2009, Roland Mas
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

require dirname(__FILE__).'/../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';
require_once $gfcommon.'include/SysTasksQ.class.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCMPlugin.class.php' ;

session_set_admin ();

setup_plugin_manager();

$res = db_query_params('SELECT systask_id, group_id FROM systasks WHERE status=$1 AND systask_type=$2',
                       array('WIP', 'SCM_REPO'));
if (!$res) {
	$this->setError('Unable to get list of projects using SCM: '.db_error());
	return false;
}

$output = '';
while ($data = db_fetch_array ($res)) {
	$hook_params = array('group_id' => $data['group_id'], 'output' => '') ;
	plugin_hook_by_reference ('scm_create_repo', $hook_params) ;
	$output .= $hook_params['output'];
}

$hook_params = array ('output' => '') ;
plugin_hook_by_reference ('scm_update_repolist', $hook_params) ;
$output .= $hook_params['output'];

cron_entry(27, $output);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

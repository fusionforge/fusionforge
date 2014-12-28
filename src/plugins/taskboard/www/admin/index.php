<?php

/*
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


require_once "env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/taskboard/config.php' ;

global $gfplugins;
require_once $gfplugins.'taskboard/www/include/TaskBoardHtml.class.php';


$user = session_get_user(); // get the session user

if (!$user || !is_object($user) ) {
	exit_error(_('Invalid User'),'home');
} else if ( $user->isError() ) {
	exit_error($user->getErrorMessage(),'home');
} else if ( !$user->isActive()) {
	exit_error(_('Invalid User : Not active'),'home');
}


$group_id = getIntFromRequest('group_id');
$pluginname = 'taskboard';

if (!$group_id) {
	exit_error(_('Cannot Process your request : No ID specified'),'home');
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		exit_no_group();
	}
	if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the plugin active
		exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
	}


	session_require_perm ('project_admin', $group_id) ;
	$taskboard = new TaskBoardHtml( $group ) ;

	$allowedActions = array('trackers','columns','edit_column','down_column','delete_column','init');
	$action = getStringFromRequest('action');

	if( in_array($action, $allowedActions) ) {
		include( $gfplugins.'taskboard/www/admin/'.$action.'.php' );
	} else {
		include( $gfplugins.'taskboard/www/admin/ind.php' );
	}
}

site_project_footer(array());

?>

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

global $gfwww;

require_once $gfwww."env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/taskboard/config.php' ;

global $gfplugins;
require_once $gfplugins.'taskboard/www/include/TaskBoardHtml.class.php';

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) ) {
	echo  json_encode( array( 'message' => _('Invalid User') ) );
	exit();
} else if ( $user->isError() ) {
	echo  json_encode( array( 'message' => $user->getErrorMessage() ) );
	exit();
} else if ( !$user->isActive()) {
	echo  json_encode( array( 'message' => _('Invalid User : Not active') ) );
	exit();
}

$group_id = getIntFromPost( 'group_id');
$action = getStringFromPost( 'action');

if (!$group_id) {
	echo  json_encode( array( 'message' => _('Cannot Process your request : No ID specified') ) );
	exit();
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		echo  json_encode( array( 'message' => _('Group is not found') ) );
		exit();
	}

	$taskboard = new TaskBoardHtml( $group ) ;
	$allowedActions = array('load_taskboard','drop_card','update','add');
	
	if( in_array($action, $allowedActions) ) {
		include( $gfplugins.'taskboard/www/ajax_'.$action.'.php' );
	} else {
		echo  json_encode( array( 'message' => 'OK' ) );
	}
}

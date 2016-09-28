#! /usr/bin/php
<?php
/**
 * Copyright 2010 Roland Mas
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

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

$err='';

// Plugins subsystem
require_once 'common/include/Plugin.class.php';
require_once 'common/include/PluginManager.class.php';

setup_plugin_manager () ;
session_set_admin () ;

$res = db_query_params ('SELECT user_id FROM users WHERE status=$1 ORDER BY user_id',
			array ('A'));

while ($arr = db_fetch_array($res)) {
    $u = user_get_object($arr['user_id']);
	echo "Refreshing NSS for user ".$u->getUnixName()."\n" ;

    $SYS->sysCheckCreateUser($u->getID());
}

$res = db_query_params ('SELECT group_id FROM groups WHERE status=$1 ORDER BY group_id',
			array ('A'));

while ($arr = db_fetch_array($res)) {
    $g = group_get_object($arr['group_id']);
	echo "Refreshing NSS for group ".$g->getUnixName()."\n" ;

    $SYS->sysCheckCreateGroup($g->getID());
}

?>

#! /usr/bin/php5
<?php
/** SCM repositories maintenance task
 *
 * Copyright 2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */


require (dirname(__FILE__).'/../www/env.inc.php');
require ($gfwww.'include/squal_pre.php');
require ($gfcommon.'include/cron_utils.php');

$groupids = array () ;
foreach ($scm_list as $pname) {
	$plugin = &plugin_get_object ($pname) ;
	$gids = $plugin->getGroups () ;
	$groupids = array_merge ($groupids, $gids) ;
}

$groupids = array_reverse ($groupids) ; // To handle new repos first
foreach ($groupids as $group_id) {
	$params = array ('group_id' => $group_id) ;
	plugin_hook ('scm_createrepo', $params) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

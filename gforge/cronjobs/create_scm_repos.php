#! /usr/bin/php5 -f
<?php
/**
 * FusionForge source control management
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
        
require_once (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCM.class.php' ;
			 
setup_plugin_manager () ;

$res=db_query("SELECT group_id FROM groups WHERE status='A' AND use_scm=1"); 
if (!$res) {
	$this->setError('Unable to get hosted project count: '.db_error());
	return false;
}

while ($data = db_fetch_array ($res)) {
	$hook_params = array ('group_id' => $data['group_id']) ;
	plugin_hook ('scm_createrepo', $hook_params) ;
}
?>

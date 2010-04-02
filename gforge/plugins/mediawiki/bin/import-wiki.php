#! /usr/bin/php5 -f
<?php
/**
 * FusionForge/Mediawiki integration
 *
 * Copyright 2010, Roland Mas
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

$script = array_shift ($argv) ;
$fusionforgeproject = array_shift ($argv) ;
array_unshift ($argv, $script) ;

require (dirname(__FILE__)).'/../../../www/env.inc.php';
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCMPlugin.class.php' ;
                         
setup_plugin_manager () ;

$group = group_get_object_by_name($fusionforgeproject) ;
if (!$group || $group->isError()) {
	die ("Wrong group!\n") ;
}

if (!$group->usesPlugin('mediawiki')) {
	die ("Project doesn't use the Mediawiki plugin\n") ;
}

ob_end_flush() ;

define( "MEDIAWIKI", true );
require_once $gfwww.'plugins/mediawiki/LocalSettings.php' ;
chdir ($wikidata) ;
$script = array_shift ($argv) ;
array_unshift ($argv, '--conf', "$wikidata/LocalSettings.php") ;
array_unshift ($argv, $script) ;

require_once '/usr/share/mediawiki/maintenance/importDump.php' ;

?>

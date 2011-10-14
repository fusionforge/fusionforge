#! /usr/bin/php -f
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (count ($argv) < 3) {
        echo "Usage: .../mw-wrapper.php <project> <script> [ arguments... ]
For instance: .../mw-wrapper.php siteadmin importDump.php /tmp/wikidump.xml
              .../mw-wrapper.php siteadmin rebuildrecentchanges.php
" ;
        exit (1) ;
}

$wrapperscript = array_shift ($argv) ;
$fusionforgeproject = array_shift ($argv) ;
$mwscript = array_shift ($argv) ;

require_once dirname(__FILE__) . '/../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

// Plugins subsystem
require_once($gfcommon.'include/Plugin.class.php');
require_once($gfcommon.'include/PluginManager.class.php');

setup_plugin_manager () ;

$group = group_get_object_by_name($fusionforgeproject) ;
if (!$group || $group->isError()) {
	die ("Wrong group!\n") ;
}

if (!$group->usesPlugin('mediawiki')) {
	die ("Project doesn't use the Mediawiki plugin\n") ;
}


define( "MEDIAWIKI", true );
require_once $gfwww.'plugins/mediawiki/LocalSettings.php' ;

$src_path = forge_get_config('src_path', 'mediawiki');
$mwscript = $src_path . '/maintenance/'.$mwscript ;

array_unshift ($argv, $mwscript, '--conf', $fusionforge_basedir . '/plugins/mediawiki/www/LocalSettings.php') ;

while (@ob_end_flush());

require_once $mwscript ;

?>

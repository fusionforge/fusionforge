#! /usr/bin/php -f
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
 * along with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

class Actions {
	function help ($name='') {
		$methods = join('|', get_class_methods($this));
		echo "Usage: forge.php ($methods) [arguments...]\n" ;
		exit (1) ;
	}

	function pluginActivate ($name) {
		$pm = plugin_manager_get_object();
		$pm->activate($name);
		$pm->LoadPlugin($name);
		$plugin = $pm->GetPluginObject($name);
		$plugin->install();
	}
	
	function pluginDeactivate ($name) {
		$pm = plugin_manager_get_object();
		$pm->deactivate($name);
	}
}

if (count($argv) == 3) {
	$action = $argv[1];
	$name   = $argv[2];
} else {
	$action = 'help';
	$name   = '';
}
	
$ctl = new Actions();
if (!method_exists($ctl, $action)) {
	$action = 'help';
}

$ctl->$action($name);

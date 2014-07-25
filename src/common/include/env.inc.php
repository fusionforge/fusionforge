<?php
/**
 * Sets the default required environment for FusionForge
 *
 * Some of the settings made here can be overwrite in the
 * configuration file if needed.
 *
 */

/**
 *
 * Copyright 2013, Franck Villaume - TrivialDev
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

if (!getenv('SERVER_SOFTWARE')) {
	/* when running outside of the Web */

	/* enable maximum error reporting */
	error_reporting(-1);
	/* disable output buffering */
	$no_gz_buffer = true;
	/* allow it to eat all memory */
	ini_set("memory_limit", -1);
}

// Attempt to set up the include path, to fix problems with relative includes
$fusionforge_basedir = dirname(dirname(dirname( __FILE__ )));

// By default, the include_path is changed to include path needed by FusionForge.
// If this does not work, then set defines to real path directly.
//
// In case of failure, the following defines are set:
//	$gfconfig: Directory where are the configuration files (/etc/fusionforge).
//	$gfcommon: Directory common of fusionforge (for common php classes).
//	$gfwww: Directory www of fusionforge (publicly accessible files).
//	$gfplugins: Directory for plugins.
//

// Easyforge config, allow several instances of fusionforge based on server name.
// Require multiples VirtualHosts in your httpd configuration.
// FORGE_CONFIG_PATH var is set in httpd.conf file and is default method shipped with the Fusionforge code source.
if (getenv('FORGE_CONFIG_PATH') && file_exists(getenv('FORGE_CONFIG_PATH').'/config.ini')) {
	$gfconfig = getenv('FORGE_CONFIG_PATH').'/';
	$gfcgfile = 'config.ini';
} elseif (getenv('sys_localinc')) {
	$gfcgfile = getenv('sys_localinc');
	$gfconfig = dirname($gfcgfile).'/';
	$gfcgfile = basename($gfcgfile);
} elseif (isset($_SERVER['SERVER_NAME']) &&
	file_exists('/etc/fusionforge/'.$_SERVER['SERVER_NAME'].'/config.ini')) {
	$gfcgfile = 'config.ini';
	$gfconfig = '/etc/fusionforge/'.$_SERVER['SERVER_NAME'].'/';
} elseif (isset($_SERVER['SERVER_NAME']) &&
	file_exists($fusionforge_basedir.'/config/'.$_SERVER['SERVER_NAME'].'/config.ini')) {
	$gfcgfile = '/config.ini';
	$gfconfig = $fusionforge_basedir.'/config/'.$_SERVER['SERVER_NAME'].'/';
} elseif (file_exists($fusionforge_basedir.'/config/config.ini')) {
	$gfcgfile = 'config.ini';
	$gfconfig = $fusionforge_basedir.'/config/';
} elseif (is_dir('/etc/fusionforge')) {
	$gfcgfile = 'config.ini';
	$gfconfig = '/etc/fusionforge/';
} elseif (is_dir('/etc/gforge')) {
	$gfcgfile = 'config.ini';
	$gfconfig = '/etc/gforge/';
} else {
	$gfcgfile = 'config.ini';
	if (is_dir('/etc/fusionforge')) {
		$gfconfig = '/etc/fusionforge/';
	} else {
		$gfconfig = '';
	}
}

$include_path = join(PATH_SEPARATOR,
	array(
		$gfconfig.'/custom',
		$gfconfig,
		$fusionforge_basedir.'/common',
		$fusionforge_basedir.'/www',
		$fusionforge_basedir.'/plugins',
		$fusionforge_basedir,
		$fusionforge_basedir.'/www/include',
		$fusionforge_basedir.'/common/include',
		'.',
		'/usr/share/php',
		'/usr/share/pear'
	)
);

if( !ini_set('include_path', $include_path ) && !set_include_path( $include_path )) {
	$gfcommon = $fusionforge_basedir.'/common/';
	$gfwww = $fusionforge_basedir.'/www/';
	$gfplugins = $fusionforge_basedir.'/plugins/';
} else {
	$gfcommon = '';
	$gfwww = '';
	$gfplugins = '';
}

<?php
/**
 * FusionForge : Exit functions for cronjob
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Franck Villaume
 * http://fusionforge.org
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

/**
 * exit_errorlevel – Return code for exit_error() and friends
 *
 * A script can set this to explicitly return a different
 * status code than the historic default of 0 to the caller.
 */
$exit_errorlevel = 0;

/**
 * exit_error() - Exit with error
 *
 * @param		string	Error text
 * @param		string	Error toptab
 */
function exit_error($title,$toptab='') {
	global $exit_errorlevel;

	print 'Error: ' . $title . "\n";
	exit($exit_errorlevel);
}

/**
 * exit_permission_denied() - Return a 'Permission Denied' error
 * @param   $reason_descr   string
 * @param   $toptab         string  toptab needed for navigation
 */
function exit_permission_denied($reason_descr='',$toptab='') {
	exit_error('PERMISSION DENIED');
}

/**
 * exit_not_logged_in() - Return a 'Not Logged in' error
 */
function exit_not_logged_in() {
	exit_error('NOT LOGGED IN');
}

/**
 * exit_no_group() - Return a 'Choose A Project/Group' error
 */
function exit_no_group() {
	exit_error('CHOOSE A PROJECT/GROUP');
}

/**
 * exit_missing_param() - Return a 'Missing Required Parameters' error
 * @param   string  $url			URL : usually $_SERVER['HTTP_REFERER']
 * @param   array   $missing_params	array of missing parameters
 * @param   string  $toptab 		needed for navigation
 */
function exit_missing_param($url='',$missing_params=array(),$toptab='') {
	exit_error('MISSING REQUIRED PARAMETERS');
}

/**
 * exit_disabled() - Return a 'Disabled Feature' error
 * @param   string  $toptab needed for navigation
 */
function exit_disabled($toptab='') {
	exit_error('DISABLED FEATURE');
}

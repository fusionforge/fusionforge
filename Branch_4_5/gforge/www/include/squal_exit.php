<?php
/**
 * squal_exit.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * exit_error() - Exit with error
 *
 * @param		string	Error title
 * @param		string	Error text
 */
function exit_error($title,$text) {
	print 'ERROR - '.$text;
	exit;
}

/**
 * exit_permission_denied() - Return a 'Permission Denied' error
 */
function exit_permission_denied() {
	exit_error('','PERMISSION DENIED');
}

/**
 * exit_not_logged_in() - Return a 'Not Logged in' error
 */
function exit_not_logged_in() {
	exit_error('','NOT LOGGED IN');
}

/**
 * exit_no_group() - Return a 'Choose A Project/Group' error
 */
function exit_no_group() {
	exit_error('','CHOOSE A PROJECT/GROUP');
}

/**
 * exit_missing_param() - Return a 'Missing Required Parameters' error
 */
function exit_missing_param() {
	exit_error('','MISSING REQUIRED PARAMETERS');
}

/**
 * exit_disabled() - Return a 'Disabled Feature' error
 */
function exit_disabled() {
       exit_error('','DISABLED FEATURE');
}

?>

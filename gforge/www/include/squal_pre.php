<?php
/**
 * squal_pre.php
 *
 * Drastically simplified version of pre.php
 * 
 * Sets up database connection and session
 * 
 * NOTE:
 *		You cannot call HTML-related functions
 * 
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

if (!$no_gz_buffer) {
    ob_start("ob_gzhandler");
}

require_once('/etc/gforge/local.inc');
require('common/include/constants.php');
require_once('common/include/database.php');
require_once('common/include/session.php');
require_once('common/include/Error.class');
require_once('common/include/User.class');
require_once('common/include/Permission.class');
require_once('common/include/utils.php');
require_once('common/include/Group.class');

//plain text version of exit_error();
require_once('squal_exit.php');

//needed for logging / logo
require_once('browser.php');

$sys_datefmt = "m/d/y H:i";

// #### Connect to db

db_connect();

if (!$conn) {
	exit_error("Could Not Connect to Database",db_error());
}

//require_once('logger.php');

// #### set session

session_set();

?>

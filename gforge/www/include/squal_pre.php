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
 */

if (!isset($no_gz_buffer) || !$no_gz_buffer) {
    ob_start("ob_gzhandler");
}

require $gfcgfile;
require $gfcommon.'include/config.php';
require $gfcommon.'include/config-vars.php';

forge_read_config_file ($gfconfig.'/config.ini') ;

require $gfcommon.'include/constants.php';
require_once $gfcommon.'include/database-pgsql.php';
require_once $gfcommon.'include/session.php';
require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'include/UserManager.class.php';
require_once $gfcommon.'include/Permission.class.php';
require_once $gfcommon.'include/utils.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'include/ProjectManager.class.php';
require_once $gfcommon.'include/escapingUtils.php';
require_once $gfcommon.'include/gettext.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

//plain text version of exit_error();
require_once $gfwww.'include/squal_exit.php';

//needed for logging / logo
//require_once('browser.php');

//system library
require_once $gfcommon.'include/System.class.php';
if (!forge_get_config('account_manager_type')) {
        $sys_account_manager_type='UNIX';
}
require_once $gfcommon.'include/system/'.forge_get_config('account_manager_type').'.class.php';
$amt = forge_get_config('account_manager_type') ;
$SYS=new $amt();


// #### Connect to db

db_connect();

if (!$gfconn) {
	exit_error("Could Not Connect to Database",db_error());
}

//require_once('logger.php');

// #### set session

session_set();

?>

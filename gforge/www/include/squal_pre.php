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

if (!isset($no_gz_buffer) || !$no_gz_buffer) {
    ob_start("ob_gzhandler");
}

require_once('local.inc');
require('common/include/constants.php');
require_once('common/include/database-'.$sys_database_type.'.php');
require_once('common/include/session.php');
require_once('common/include/Error.class.php');
require_once('common/include/User.class.php');
require_once('common/include/Permission.class.php');
require_once('common/include/utils.php');
require_once('common/include/Group.class.php');
require_once('common/include/escapingUtils.php');
require_once('www/include/BaseLanguage.class.php');

// Plugins subsystem
require_once('common/include/Plugin.class.php') ;
require_once('common/include/PluginManager.class.php') ;

//plain text version of exit_error();
require_once('squal_exit.php');

//needed for logging / logo
//require_once('browser.php');

//system library
require_once('common/include/System.class.php');
if (!$sys_account_manager_type) {
        $sys_account_manager_type='UNIX';
}
require_once('common/include/system/'.$sys_account_manager_type.'.class.php');
$SYS=new $sys_account_manager_type();


// #### Connect to db

db_connect();

if (!$conn) {
	exit_error("Could Not Connect to Database",db_error());
}

//require_once('logger.php');

// #### set session

session_set();

?>

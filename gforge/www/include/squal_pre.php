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

forge_define_config_item ('forge_name', 'core', $GLOBALS['sys_name']) ;
forge_define_config_item ('user_registration_restricted', 'core', $GLOBALS['sys_user_reg_restricted']) ;
forge_define_config_item ('web_host', 'core', $GLOBALS['sys_default_domain']) ;
forge_define_config_item ('scm_tarballs_path', 'core', $GLOBALS['sys_scm_tarballs_path']) ;
forge_define_config_item ('scm_snapshots_path', 'core', $GLOBALS['sys_scm_snapshots_path']) ;
// forge_define_config_item ('', 'core', $GLOBALS['sys_']) ;

/* Long-term:
 require_once $gfcommon.'include/FusionForge.class.php';
 $forge = new FusionForge() ;
 forge_define_config_item ('forge_name', 'core', $forge->software_name) ;
 forge_define_config_item ('user_registration_restricted', 'core', false) ;
*/
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
if (!$sys_account_manager_type) {
        $sys_account_manager_type='UNIX';
}
require_once $gfcommon.'include/system/'.$sys_account_manager_type.'.class.php';
$SYS=new $sys_account_manager_type();


// #### Connect to db

db_connect();

if (!$gfconn) {
	exit_error("Could Not Connect to Database",db_error());
}

//require_once('logger.php');

// #### set session

session_set();

?>

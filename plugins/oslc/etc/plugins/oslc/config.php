<?php

// here you can put all your configuration variables

$world = 'Earth';

// Define the backend tracker type : by defaut : fusionforge
defined('TRACKER_TYPE')
    || define('TRACKER_TYPE', (getenv('TRACKER_TYPE') ? getenv('TRACKER_TYPE') : 'fusionforge'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Define path separator
defined('PATH_SEPARATOR')
    || define('PATH_SEPARATOR', ((strpos(PHP_OS,'Win') !== false) ? ';' : ':'));

switch (TRACKER_TYPE) {
	case 'mantis':
		// Initialize the Mantis environment necessary to plug to its internal API

		// this is supposed to be placed into mantis_top_level_dir/www/oslc-zend/
		defined('MANTIS_DIR')
    		|| define('MANTIS_DIR', (getenv('MANTIS_DIR') ? getenv('MANTIS_DIR') : dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR));

		$t_mantis_dir = MANTIS_DIR;

		// TODO : explain the following line :
		$g_bypass_headers = true;
		require($t_mantis_dir.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'constant_inc.php' );
		require_once( $t_mantis_dir . 'core.php' );
		require_once( $t_mantis_dir . 'core/summary_api.php' );

		break;
	case 'demo':
		break;
	case 'fusionforge':
		define('APPLICATION_PATH', '/usr/share/gforge/plugins/oslc/include/oslc-zend/application');
		/*require(APPLICATION_PATH.'/../../../../www/env.inc.php');
	    require_once $gfwww.'include/pre.php';*/
		break;
	default:
		throw new Exception('Unsupported TRACKER_TYPE : '. TRACKER_TYPE .' !');
		break;
}
/*
define(AUTH_TYPE, 'oauth');
// Define the backend tracker type : by defaut : mantis
defined('AUTH_TYPE')
    || define('AUTH_TYPE', (getenv('AUTH_TYPE') ? getenv('AUTH_TYPE') : 'basic'));

switch (AUTH_TYPE) {
	case 'basic':
		break;
	case 'oauth':
		switch (TRACKER_TYPE) {
			case 'mantis':
				// Initialize the Mantis environment necessary to plug to its internal API

				// this is supposed to be placed into mantis_top_level_dir/www/oslc-zend/
				// TODO : render this customizable in .htaccess much like TRACKER_TYPE above
				$t_mantis_dir = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;

				// TODO : explain the following line :
				$g_bypass_headers = true;
				require_once( $t_mantis_dir . 'plugins/OauthAuthz/oauth/DbOAuthDataStore.inc.php' );

				break;
			default:
				throw new Exception('Unsupported oauth AUTH_TYPE for TRACKER_TYPE: '. TRACKER_TYPE .' !');
				break;
		}
		break;
	default:
		throw new Exception('Unsupported AUTH_TYPE : '. AUTH_TYPE .' !');
		break;
}
*/
?>

<?php
/*
 * DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
 * DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
 * 
 * Running the test suite will drop your current database, this is
 * to be used only on test environment.
 * 
 * DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
 * DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER DANGER 
 */

// Host where selenium-rc is running
define ('SELENIUM_RC_HOST', 'localhost');

// URL to access the application
define ('URL', 'http://test.local/');

// Base URL where FusionForge is installed
define ('BASE', '');

// Database connection parameters.
define('DB_TYPE', 'pgsql');         // Values: mysql, pgsql
define('DB_NAME', 'fforge');
define('DB_USER', 'gforge');
define('DB_PASSWORD', '@@FFDB_PASS@@');

define ('WSDL_URL', URL.'soap/index.php?wsdl');

// this should be an existing user of the forge together with its password
// (the password should be different from 'xxxxxx')
define ('EXISTING_USER', 'admin');
define ('PASSWD_OF_EXISTING_USER', 'myadmin');

// Enter true when file is configured.
define('CONFIGURED', false);
?>

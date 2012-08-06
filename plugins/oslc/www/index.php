<?php

/*
 * oslc plugin
 *
 * Sabri LABBENE <sabri.labbene@gmail.com>
 *
 */

require_once '../../../www/env.inc.php';
require_once $gfwww.'include/pre.php';
require_once $gfconfig.'plugins/oslc/config.php';

// Run OSLC Zend application
/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV,APPLICATION_PATH . '/configs/application.ini');

// The next stop is in application/Bootstrap.php
$application->bootstrap()->run();


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

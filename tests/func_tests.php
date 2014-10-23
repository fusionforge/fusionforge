<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (!@include_once 'PHPUnit/Autoload.php') {
	include_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
}

require_once 'func/Testing/SeleniumRemoteSuite.php';

class AllTests
{
	public static function main()
	{
        global $testsuite_already_run;
        if (!isset ($testsuite_already_run)) {
            PHPUnit_TextUI_TestRunner::run(self::suite());
            $testsuite_already_run = TRUE;
        }
	}

	public static function suite()
	{
		$suite = new SeleniumRemoteSuite('PHPUnit');

		// Selenium tests
		if (!defined('DB_INIT_CMD')) { define('PROJECTA','true'); }
		if (getenv('TESTGLOB') != FALSE)
		  $files = glob(getenv('TESTGLOB'));
		else
		  $files = glob('func/*/*Test.php');
		natsort($files);
		$suite->addTestFiles($files);

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    if (!isset ($testsuite_already_run)) {
        AllTests::main();
        $testsuite_already_run = TRUE;
    }
}

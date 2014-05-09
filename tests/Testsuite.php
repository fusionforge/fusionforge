<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (!@include_once 'PHPUnit/Autoload.php') {
	include_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
}

require_once 'func/Testing/SeleniumRemoteSuite.php';

class Testsuite
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new SeleniumRemoteSuite('PHPUnit');

		// Selenium tests
		if (!defined('DB_INIT_CMD')) { define('PROJECTA','true'); }
		$files = glob("func/*/*Test.php");
		natsort($files);
		$suite->addTestFiles($files);

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}

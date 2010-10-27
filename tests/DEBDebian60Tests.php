<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'func/Testing/DEBSeleniumRemoteSuite.php';

class DEBDebian60Tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new DEBSeleniumRemoteSuite('PHPUnit');

		// Selenium tests
		$suite->addTestFiles(glob("func/Site/*Test.php"));
		if (!defined('DB_INIT_CMD')) { define('PROJECTA','true'); }
		$suite->addTestFiles(glob("func/Trackers/*Test.php"));
		$suite->addTestFiles(glob("func/Tasks/*Test.php"));
		$suite->addTestFiles(glob("func/Forums/*Test.php"));
		$suite->addTestFiles(glob("func/News/*Test.php"));
		$suite->addTestFiles(glob("func/PluginsBlocks/*Test.php"));
		
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
?>

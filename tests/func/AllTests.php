<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Unit tests
//require_once 'ACL/AllTests.php';

// Selenium based tests
require_once 'Site/AllTests.php';
//require_once 'Trackers/AllTests.php';
require_once 'Tasks/AllTests.php';
require_once 'Forums/AllTests.php';
//require_once 'PluginsWiki/AllTests.php';
//require_once 'PluginsWebSvn/AllTests.php';
require_once 'News/AllTests.php';
//require_once 'scm/AllTests.php';
//require_once 'docs/AllTests.php';
// ...

class AllTests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		// Unit tests
//		$suite->addTest(ACL_AllTests::suite());

		// Integration tests (Selenium).
		$suite->addTest(Site_AllTests::suite());
//		$suite->addTest(Trackers_AllTests::suite());
		$suite->addTest(Tasks_AllTests::suite());
		$suite->addTest(Forums_AllTests::suite());
		$suite->addTest(News_AllTests::suite());
//		$suite->addTest(PluginsWiki_AllTests::suite());
//		$suite->addTest(PluginsWebSvn_AllTests::suite());
//		$suite->addTest(Scm_AllTests::suite());
//		$suite->addTest(Docs_AllTests::suite());

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
?>

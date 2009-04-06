<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Unit tests
require_once 'unit/utils/AllTests.php';
//require_once 'ACL/AllTests.php';

// Code tests
require_once 'code/syntax/AllTests.php';

// Build tests
require_once 'build/packages/AllTests.php';

// Remote tests
//require_once 'remote/tarball/AllTests.php';

// Selenium based tests
//require_once 'func/Site/AllTests.php';
//require_once 'func/Trackers/AllTests.php';
//require_once 'func/Tasks/AllTests.php';
//require_once 'func/Forums/AllTests.php';
//require_once 'func/PluginsWiki/AllTests.php';
//require_once 'func/PluginsWebSvn/AllTests.php';
//require_once 'func/News/AllTests.php';
//require_once 'func/scm/AllTests.php';
//require_once 'func/docs/AllTests.php';


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
		$suite->addTest(Utils_AllTests::suite());
//		$suite->addTest(ACL_AllTests::suite());

		// Code tests
		$suite->addTest(Syntax_AllTests::suite());

		// Building packages tests
		$suite->addTest(Packages_AllTests::suite());
		
		// Remote tests
//		$suite->addTest(Remote_AllTests::suite());
		
		// Integration tests (Selenium).
//		$suite->addTest(Site_AllTests::suite());
//		$suite->addTest(Trackers_AllTests::suite());
//		$suite->addTest(Tasks_AllTests::suite());
//		$suite->addTest(Forums_AllTests::suite());
//		$suite->addTest(News_AllTests::suite());
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

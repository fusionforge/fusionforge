<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (@include_once '/usr/local/share/php/vendor/autoload.php') {
	$phpunitversion = 6;
	class PHPUnit_Framework_TestSuite extends PHPUnit\Framework\TestSuite {}
	class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase {}
} else {
	$phpunitversion = 4;
	if (!@include_once 'PHPUnit/Autoload.php') {
		include_once 'PHPUnit/Framework.php';
		require_once 'PHPUnit/TextUI/TestRunner.php';
	}
}

class AllTests
{
	public static function main()
	{
        PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		// Selenium tests
		if (getenv('TESTGLOB') != FALSE)
			$files = glob(dirname(__FILE__).'/'.getenv('TESTGLOB'));
		else
			$files = glob(dirname(__FILE__).'/func/*/*Test.php');
		natsort($files);
		$suite->addTestFiles($files);

		return $suite;
	}
}

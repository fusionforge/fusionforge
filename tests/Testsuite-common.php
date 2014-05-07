<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (!@include_once 'PHPUnit/Autoload.php') {
	include_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
}

require_once 'func/Testing/SeleniumRemoteSuite.php';

class TestsuiteCommon
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}
}

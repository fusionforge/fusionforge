<?php

require_once 'TestsuiteCommon.php';

class TestsuiteSrcCOS extends TestsuiteCommon
{
	public static function suite()
	{
		$suite = new SeleniumRemoteSuite('PHPUnit');

		// Selenium tests
		$suite->addTestFiles(glob("func/Site/*Test.php"));
//		$suite->addTestFiles(glob("func/SSH/*Test.php"));
		$suite->addTestFiles(glob("func/Trackers/*Test.php"));
		$suite->addTestFiles(glob("func/Tasks/*Test.php"));
		$suite->addTestFiles(glob("func/Docs/*Test.php"));
		$suite->addTestFiles(glob("func/Forums/*Test.php"));
		$suite->addTestFiles(glob("func/News/*Test.php"));
		$suite->addTestFiles(glob("func/PluginsBlocks/*Test.php"));
//		$suite->addTestFiles(glob("func/PluginsMediawiki/*Test.php"));
//		$suite->addTestFiles(glob("func/PluginsMoinMoin/*Test.php"));
		$suite->addTestFiles(glob("func/PluginsOnlineHelp/*Test.php"));
//		$suite->addTestFiles(glob("func/SSH/*Test.php"));
//		$suite->addTestFiles(glob("func/PluginsScmBzr/*Test.php"));
		$suite->addTestFiles(glob("func/PluginsScmGit/gitWUITest.php"));
		$suite->addTestFiles(glob("func/PluginsScmSvn/svnWUITest.php"));
		$suite->addTestFiles(glob("func/RBAC/*Test.php"));
		$suite->addTestFiles(glob("func/Surveys/*Test.php"));
		$suite->addTestFiles(glob("func/Search/*Test.php"));

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}

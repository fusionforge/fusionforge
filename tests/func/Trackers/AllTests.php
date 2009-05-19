<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Trackers_AllTests::main');
}
 
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
require_once dirname(__FILE__).'/trackers.php';
require_once dirname(__FILE__).'/workflow.php';
require_once dirname(__FILE__).'/relation.php';
// ...
 
class Trackers_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('CreateTracker');
        $suite->addTestSuite('CreateTrackerWorkflow');
        $suite->addTestSuite('CreateTrackerRelation');
        // ...
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'Trackers_AllTests::main') {
    Framework_AllTests::main();
}
?>
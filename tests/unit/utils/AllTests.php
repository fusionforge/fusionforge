<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/UtilsTests.php';
// ...
 
class Utils_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Utils_Tests');
        // ...
 
        return $suite;
    }
}
?>

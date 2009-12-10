<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/UtilsTests.php';
require_once dirname(__FILE__).'/DbUtilsTests.php';
require_once dirname(__FILE__).'/TextSanitizerTests.php';
 
class Utils_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Utils_Tests');
        $suite->addTestSuite('TextSanitizerTests');
        $suite->addTestSuite('Database_Utils_Tests');
 
        return $suite;
    }
}
?>

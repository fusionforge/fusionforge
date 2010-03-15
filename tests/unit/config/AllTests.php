<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/ConfigTests.php';
 
class Config_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Config_Tests');
 
        return $suite;
    }
}
?>

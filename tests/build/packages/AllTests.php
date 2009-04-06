<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/BuildTests.php';

class Packages_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Packages_Tests');
 
        return $suite;
    }
}
?>

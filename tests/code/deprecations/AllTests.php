<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/DeprecationsTests.php';

class Deprecations_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Deprecations_Tests');
        // ...
 
        return $suite;
    }
}
?>

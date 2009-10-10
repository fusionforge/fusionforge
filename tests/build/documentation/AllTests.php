<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/DocumentationTests.php';

class Documentation_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Documentation_Tests');
 
        return $suite;
    }
}
?>

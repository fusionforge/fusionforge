<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/SyntaxTests.php';

class Syntax_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Syntax_Tests');
        // ...
 
        return $suite;
    }
}
?>

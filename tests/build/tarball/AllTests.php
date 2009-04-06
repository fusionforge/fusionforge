<?php
require_once 'PHPUnit/Framework.php';
 
require_once dirname(__FILE__).'/TarballTests.php';

class Tarball_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Tarball_Tests');
        // ...
 
        return $suite;
    }
}
?>

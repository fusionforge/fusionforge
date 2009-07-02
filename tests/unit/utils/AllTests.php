<?php

$sys_path_to_htmlpurifier = '/usr/share/htmlpurifier';

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/UtilsTests.php';
require_once dirname(__FILE__).'/TextSanitizerTests.php';
 
class Utils_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestSuite('Utils_Tests');
        $suite->addTestSuite('TextSanitizerTests');
 
        return $suite;
    }
}
?>

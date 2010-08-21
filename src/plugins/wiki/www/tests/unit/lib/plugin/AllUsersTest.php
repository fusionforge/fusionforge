<?php // $Id: AllUsersTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/AllUsers.php';
require_once 'PHPUnit.php';

class AllUsersTest extends phpwiki_TestCase {

    /**
     * Test that we can instantiate and run AllUsers plugin without error.
     */
    function testAllUsers() {
        global $request;

        $lp = new WikiPlugin_AllUsers();
        $this->assertEquals("AllUsers", $lp->getName());
        $basepage = "";
        $args = "";
        $result = $lp->run($request->getDbh(), $args, $request, $basepage);
        $this->assertType('object',$result,'isa PageList');
    }
}


?>

<?php // $Id: WantedPagesTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/WantedPages.php';
require_once 'PHPUnit.php';

class WantedPagesTest extends phpwiki_TestCase {
    /**
     * Test that we can instantiate and run WantedPages plugin without error.
     */
    function testWantedPages() {
        global $request;

        $lp = new WikiPlugin_WantedPages();
        $this->assertEquals("WantedPages", $lp->getName());

        $basepage = "";
        $args = "";
        $result = $lp->run($request->getDbh(), $args, $request, $basepage);
        $this->assertType('object', $result, 'isa PageList');

        $args = "HomePage";
        $result = $lp->run($request->getDbh(), $args, $request, $basepage);
        $this->assertType('object', $result, 'isa PageList');
    }
}


?>
